(function app_data(data, $) {

  data.isLoaded = false;

  var database_changed = false;
  var locale_changed = false;

  var fdb = new ForerunnerDB();
  var database = fdb.db('doomtrooperdb');

  data.db = database;
  var masters = {
    expansions: database.collection('master_expansion', {primaryKey: 'code'}),
    factions: database.collection('master_faction', {primaryKey: 'code'}),
    cards: database.collection('master_card', {primaryKey: 'code'})
  };

  var dfd;

  function onCollectionUpdate(updated) {
    database_changed = true;
  }

  function onCollectionInsert(inserted, failed) {
    database_changed = true;
  }

  /**
   * loads the database from local
   * sets up a Promise on all data loading/updating
   * @memberOf data
   */
  function load() {
    masters.expansions.load(function (err) {
      if (err) {
        console.log('error when loading expansions', err);
      }
      masters.factions.load(function (err) {
        if (err) {
          console.log('error when loading factions', err);
        }
        masters.cards.load(function (err) {
          if (err) {
            console.log('error when loading cards', err);
          }

          /*
           * data has been fetched from local store
           */

          /*
           * we set up insert and update listeners now
           * if we did it before, .load() would have called onInsert
           */
          masters.expansions.on("insert", onCollectionInsert).on("update", onCollectionUpdate);
          masters.cards.on("insert", onCollectionInsert).on("update", onCollectionUpdate);

          /*
           * if database is not empty, use it for now
           */
          if (masters.expansions.count() > 0 && masters.cards.count() > 0) {
            release();
          }

          /*
           * then we ask the server if new data is available
           */
          query();
        });
      });
    });
  }

  /**
   * release the data for consumption by other modules
   * @memberOf data
   */
  function release() {
    data.expansions = database.collection('expansion', {primaryKey: 'code', changeTimestamp: false});
    data.expansions.setData(masters.expansions.find());

    data.factions = database.collection('faction', {primaryKey: 'code', changeTimestamp: false});
    data.factions.setData(masters.factions.find());

    data.cards = database.collection('card', {primaryKey: 'code', changeTimestamp: false});
    data.cards.setData(masters.cards.find());

    data.isLoaded = true;

    $(document).trigger('data.app');
  }

  /**
   * queries the server to update data
   * @memberOf data
   */
  function query() {
    dfd = {
      expansions: new $.Deferred(),
      factions: new $.Deferred(),
      cards: new $.Deferred()
    };
    $.when(dfd.expansions, dfd.cards).done(update_done).fail(update_fail);

    $.ajax({
      url: Routing.generate('api_expansions'),
      success: parse_expansions,
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('error when requesting expansions', errorThrown);
        dfd.expansions.reject(false);
      }
    });

    $.ajax({
      url: Routing.generate('api_factions'),
      success: parse_factions,
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('error when requesting factions', errorThrown);
        dfd.factions.reject(false);
      }
    });

    $.ajax({
      url: Routing.generate('api_cards', {'v': '3.0'}),
      success: parse_cards,
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('error when requesting cards', errorThrown);
        dfd.cards.reject(false);
      }
    });
  }

  /**
   * called if all operations (load+update) succeed (resolve)
   * deferred returns true if data has been updated
   * @memberOf data
   */
  function update_done() {
    if (database_changed && !locale_changed) {
      /*
       * we display a message informing the user that they can reload their page to use the updated data
       * except if we are on the front page, because data is not essential on the front page
       */
      if ($('.site-title').size() === 0) {
        var message = "A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.";
        app.ui.insert_alert_message('warning', message);
      }
    }

    // if it is a force update, we haven't release the data yet
    if (!data.isLoaded) {
      release();
    }
  }

  /**
   * called if an operation (load+update) fails (reject)
   * deferred returns true if data has been loaded
   * @memberOf data
   */
  function update_fail(expansions_loaded, factions_loaded, cards_loaded) {
    if (expansions_loaded === false || factions_loaded === false || cards_loaded === false) {
      var message = Translator.trans('data_load_fail');
      app.ui.insert_alert_message('danger', message);
    } else {
      /*
       * since data hasn't been persisted, we will have to do the query next time as well
       * -- not much we can do about it
       * but since data has been loaded, we call the promise
       */
      release();
    }
  }

  /**
   * updates the database if necessary, from fetched data
   * @memberOf data
   */
  function update_collection(data, collection, locale, deferred) {
    // we update the database and Forerunner will tell us if the data is actually different
    data.forEach(function (row) {
      if(collection.findById(row.code)) {
        collection.update({code: row.code}, row);
      } else {
        collection.insert(row);
      }
    });

    // we update the locale
    if (locale !== collection.metaData().locale) {
      locale_changed = true;
    }
    collection.metaData().locale = locale;

    collection.save(function (err) {
      if (err) {
        console.log('error when saving ' + collection.name(), err);
        deferred.reject(true)
      } else {
        deferred.resolve();
      }
    });
  }

  /**
   * handles the response to the ajax query for expansions data
   * @memberOf data
   */
  function parse_expansions(response, textStatus, jqXHR) {
    var locale = jqXHR.getResponseHeader('Content-Language');
    update_collection(response, masters.expansions, locale, dfd.expansions);
  }

  /**
   * handles the response to the ajax query for factions data
   * @memberOf data
   */
  function parse_factions(response, textStatus, jqXHR) {
    var locale = jqXHR.getResponseHeader('Content-Language');
    update_collection(response, masters.factions, locale, dfd.factions);
  }

  /**
   * handles the response to the ajax query for the cards data
   * @memberOf data
   */
  function parse_cards(response, textStatus, jqXHR) {
    var locale = jqXHR.getResponseHeader('Content-Language');
    update_collection(response, masters.cards, locale, dfd.cards);
  }

  $(function () {
    load();
  });

})(app.data = {}, jQuery);
