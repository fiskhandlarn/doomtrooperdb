/* global _, app, Translator */

(function app_deck(deck, $)
 {

   var date_creation,
       date_update,
       description_md,
       id,
       name,
       tags,
       unsaved,
       user_id,
       problem_labels = _.reduce(
         ['too_few_cards', 'too_many_copies'],
         function (problems, key) {
           problems[key] = Translator.trans('decks.problems.' + key);
           return problems;
         },
         {}),
       header_tpl = _.template('<h5><span class="icon icon-<%= code %>"></span> <%= name %> (<%= quantity %>)</h5>'),
       card_line_tpl = _.template('<span class="icon icon-<%= card.type_code %> fg-<%= card.faction_code %>"></span> <a href="<%= card.url %>" class="card card-tip" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="<%= card.code %>"><%= card.label %></a>'),
       /*
        * Templates for the different deck layouts, see deck.get_layout_data
        */
       layouts = {
         types: _.template('<div class="deck-content"><div class="row"><div class="meta col-sm-12 col-print-12"><%= meta %></div></div><div class="row"><div class="type col-sm-6 col-print-6"><%= alliance %></div><div class="type col-sm-6 col-print-6"><%= art %></div><div class="type col-sm-6 col-print-6"><%= beast %></div><div class="type col-sm-6 col-print-6"><%= equipment %></div><div class="type col-sm-6 col-print-6"><%= fortification %></div><div class="type col-sm-6 col-print-6"><%= ki %></div><div class="type col-sm-6 col-print-6"><%= mission %></div><div class="type col-sm-6 col-print-6"><%= relic %></div><div class="type col-sm-6 col-print-6"><%= special %></div><div class="type col-sm-6 col-print-6"><%= symmetry %></div><div class="type col-sm-6 col-print-6"><%= warrior %></div><div class="type col-sm-6 col-print-6"><%= warzone %></div></div></div>'),
         cards: _.template('<div class="deck-content"><div class="row"><div class="meta col-sm-12 col-print-12"><%= meta %></div></div><div class="row"><div class="cards col-sm-12 col-print-12"><%= cards %></div></div></div>'),
       },
       layout_data = {};

   // TODO remove codes/keys?
   var factions = {
     'bauhaus': 'bauhaus',
     'brotherhood': 'brotherhood',
     'capitol': 'capitol',
     'crescentia': 'crescentia',
     'cybertronic': 'cybertronic',
     'general': 'general',
     'imperial': 'imperial',
     'legion': 'legion',
     'lutheran': 'lutheran',
     'mishima': 'mishima',
     'rasputin': 'rasputin',
     'templars': 'templars'
   };

   /**
    * Creates a new line-item for a given card to a given DOM element.
    * @param {Object} card The card object
    * @param {jQuery} $section The section element
    * @return {jQuery} The given section, with the line item appended.
    * @see get_layout_data_one_section()
    */
   var append_card_line_to_section = function append_card_line_to_section(card, $section) {
     var $elem = $('<div>');
     $elem.append($(card_line_tpl({card: card})));
     $elem.prepend(card.indeck + 'x ');
     $elem.appendTo($section);
     return $section;
   };

   /**
    * @memberOf deck
    * @param {object} data
    */
   deck.init = function init(data)
   {
     date_creation = data.date_creation;
     date_update = data.date_update;
     description_md = data.description_md;
     id = data.id;
     name = data.name;
     tags = data.tags;
     unsaved = data.unsaved;
     user_id = data.user_id;

     if(app.data.isLoaded) {
       deck.set_slots(data.slots);
     } else {
       //console.log("deck.set_slots put on hold until data.app");
       $(document).on('data.app', function () {
         deck.set_slots(data.slots);
       });
     }
   };

   /**
    * Sets the slots of the deck
    *
    * @memberOf deck
    * @param {object} slots
    */
   deck.set_slots = function set_slots(slots)
   {
     app.data.cards.update({}, {
       indeck: 0
     });
     for(var code in slots) {
       if(slots.hasOwnProperty(code)) {
         app.data.cards.updateById(code, {indeck: slots[code]});
       }
     }
   };

   /**
    * @memberOf deck
    * @returns string
    */
   deck.get_id = function get_id()
   {
     return id;
   };

   /**
    * @memberOf deck
    * @returns string
    */
   deck.get_name = function get_name()
   {
     return name;
   };

   /**
    * @memberOf deck
    * @returns string
    */
   deck.get_description_md = function get_description_md()
   {
     return description_md;
   };

   /**
    * @memberOf deck
    * @param {object} sort
    * @param {object} query
    * @param {object} group
    */
   deck.get_cards = function get_cards(sort, query, group)
   {
     sort = sort || {};
     sort['code'] = 1;

     query = query || {};
     query.indeck = {
       '$gt': 0
     };

     var options = {
       '$orderBy': sort
     };
     if (group){
       options.$groupBy = group;
     }
     return app.data.cards.find(query, options);
   };

   /**
    * @memberOf deck
    * @param {object} sort
    */
   deck.get_draw_deck = function get_draw_deck(sort)
   {
     return deck.get_cards(sort);
   };

   /**
    * @memberOf deck
    * @param {object} sort
    */
   deck.get_draw_deck_size = function get_draw_deck_size(sort)
   {
     var draw_deck = deck.get_draw_deck();
     return deck.get_nb_cards(draw_deck);
   };

   deck.get_nb_cards = function get_nb_cards(cards)
   {
     if(!cards)
       cards = deck.get_cards();
     var quantities = _.pluck(cards, 'indeck');
     return _.reduce(quantities, function (memo, num) {
       return memo + num;
     }, 0);
   };

   /**
    * @memberOf deck
    * @param {Object} sort
    * @return {Array}
    */
   deck.get_included_expansions = function get_included_expansions(sort)
   {
     var cards = deck.get_cards();
     var nb_expansions = {};
     sort = sort || { 'available': 1 };
     cards.forEach(function (card) {
       nb_expansions[card.expansion_code] = Math.max(nb_expansions[card.expansion_code] || 0, card.indeck / card.quantity);
     });
     var expansion_codes = _.uniq(_.pluck(cards, 'expansion_code'));
     var expansions = app.data.expansions.find({
       'code': {
         '$in': expansion_codes
       }
     }, {
       '$orderBy': sort
     });
     expansions.forEach(function (expansion) {
       expansion.quantity = nb_expansions[expansion.code] || 0;
     });
     return expansions;
   };

   deck.change_sort = function(sort_type) {
     if (localStorage) {
       localStorage.setItem('ui.deck.sort', sort_type);
     }
     deck.sort_type = sort_type;
     if ($("#deck")) {
       deck.display('#deck');
     }

     if ($("#deck-content")) {
       deck.display('#deck-content');
     }

     if ($("#decklist")) {
       deck.display('#decklist');
     }
   };

   /**
    * @memberOf deck
    * @param {object} container
    * @param {object} options
    */
   deck.display = function display(container, options)
   {
     options = _.extend({sort: 'type', cols: 2}, options);

     var deck_content = deck.get_layout_data(options);

     $(container)
       .removeClass('deck-loading')
       .empty();

     $(container).append(deck_content);
   };

   deck.get_layout_data = function get_layout_data(options)
   {
     var data = {
       images: '',
       meta: '',
       alliance: '',
       art: '',
       beast: '',
       equipment: '',
       fortification: '',
       ki: '',
       mission: '',
       relic: '',
       special: '',
       symmetry: '',
       warrior: '',
       warzone: '',
       cards: ''
     };

     var problem = deck.get_problem();

     var expansions = _.map(deck.get_included_expansions({ 'position': 1 }), function (expansion) {
       return expansion.name;
     }).join(', ');
     deck.update_layout_section(data, 'meta', $('<div>' + Translator.trans('decks.edit.meta.expansions', {"expansions": expansions}) + '</div>'));

     if(problem) {
       deck.update_layout_section(data, 'meta', $('<div class="text-danger small"><span class="fas fa-exclamation-triangle"></span> ' + problem_labels[problem] + '</div>'));
     }

     var layout_template;

     switch (deck.sort_type) {
       case "name":
         deck.update_layout_section(data, "cards", $('<br>'));
         deck.update_layout_section(data, "cards", deck.get_layout_section({'name': 1},  null, null, "number"));
         layout_template = 'cards';
         break;
       case "set":
         deck.update_layout_section(data, "cards", deck.get_layout_section_for_cards_sorted_by_set(true));
         layout_template = 'cards';
         break;
       case "setnumber":
         deck.update_layout_section(data, "cards", deck.get_layout_section_for_cards_sorted_by_set(false));
         layout_template = 'cards';
         break;
       case "cardnumber":
         deck.update_layout_section(data, "cards", $('<br>'));
         deck.update_layout_section(data, "cards", deck.get_layout_section({'code': 1},  null, null, "number"));
         layout_template = 'cards';
         break;
       case "type":
       default:
         deck.update_layout_section(data, 'art', deck.get_layout_data_one_section('type_code', 'art', 'type_name'));
         deck.update_layout_section(data, 'beast', deck.get_layout_data_one_section('type_code', 'beast', 'type_name'));
         deck.update_layout_section(data, 'equipment', deck.get_layout_data_one_section('type_code', 'equipment', 'type_name'));
         deck.update_layout_section(data, 'fortification', deck.get_layout_data_one_section('type_code', 'fortification', 'type_name'));
         deck.update_layout_section(data, 'ki', deck.get_layout_data_one_section('type_code', 'ki', 'type_name'));
         deck.update_layout_section(data, 'mission', deck.get_layout_data_one_section('type_code', 'mission', 'type_name'));
         deck.update_layout_section(data, 'relic', deck.get_layout_data_one_section('type_code', 'relic', 'type_name'));
         deck.update_layout_section(data, 'special', deck.get_layout_data_one_section('type_code', 'special', 'type_name'));
         deck.update_layout_section(data, 'symmetry', deck.get_layout_data_one_section('type_code', 'symmetry', 'type_name'));
         deck.update_layout_section(data, 'warrior', deck.get_layout_data_one_section('type_code', 'warrior', 'type_name'));
         deck.update_layout_section(data, 'warzone', deck.get_layout_data_one_section('type_code', 'warzone', 'type_name'));
         deck.update_layout_section(data, 'alliance', deck.get_layout_data_one_section('type_code', 'alliance', 'type_name'));
         layout_template = 'types';
         break;
     }

     if (options && options.layout) {
       layout_template = options.layout;
     }

     return layouts[layout_template](data);
   };

   deck.update_layout_section = function update_layout_section(data, section, element)
   {
     data[section] = data[section] + element[0].outerHTML;
   };

   deck.get_layout_section = function(sort, group, query, context){
     var cards;
     var section = $('<div>');
     cards = deck.get_cards(sort, query, group);
     if(cards.length) {
       deck.create_card_group(cards, context).appendTo(section);

     } else if (cards.constructor !== Array){
       $.each(cards, function (index, group_cards) {
         if (group_cards.constructor === Array){
           $(header_tpl({code: index, name: index === "undefined" ? "Null" : index, quantity: group_cards.reduce(function(a,b){ return a + b.indeck}, 0) })).appendTo(section);
           deck.create_card_group(group_cards, context).appendTo(section);
         }
       });
     }
     return section;
   };

   /**
    * @param {boolean} sortByName set to TRUE for sorting by name within sets, or FALSE to sort by card number.
    */
   deck.get_layout_section_for_cards_sorted_by_set = function(sortByName) {
     sortByName = !!sortByName;

     var section = $('<div>');
     var context = sortByName ? "" : "number";
     var sort = sortByName ? {"name": 1} : {"code": 1};
     var expansions = deck.get_included_expansions({"position": 1});
     var cards = deck.get_cards(sort, {}, {"expansion_name": 1});

     expansions.forEach(function(expansion){
       $(header_tpl({code: expansion.code, name: expansion.name, quantity: cards[expansion.name].reduce(function(a,b){ return a + b.indeck}, 0) })).appendTo(section);
       deck.create_card_group(cards[expansion.name], context).appendTo(section);
     });
     return section;
   };

   deck.create_card_group = function(cards, context){
     var section = $('<div>');
     cards.forEach(function (card) {
       var $div = $('<div>');

       $div.append($(card_line_tpl({card:card})));
       $div.prepend(card.indeck+'x ');
       if (context && context === "number"){
         $div.append(" | "+card.expansion_name);
       }

       $div.appendTo(section);
     });
     return section;
   };

   deck.get_layout_data_one_section = function get_layout_data_one_section(sortKey, sortValue, displayLabel)
   {
     var $section = $('<div>');
     var query = {};
     query[sortKey] = sortValue;
     var cards = deck.get_cards({name: 1}, query);
     if(cards.length) {
       $(header_tpl({code: sortValue, name: cards[0][displayLabel], quantity: deck.get_nb_cards(cards)})).appendTo($section);
       cards.forEach(function(card) {
         $section = append_card_line_to_section(card, $section);
       });
     }
     return $section;
   };

   /**
    * @memberOf deck
    * @return boolean true if at least one other card quantity was updated
    */
   deck.set_card_copies = function set_card_copies(card_code, nb_copies)
   {
     var card = app.data.cards.findById(card_code);
     if(!card)
       return false;

     var updated_other_card = false;
     app.data.cards.updateById(card_code, {
       indeck: nb_copies
     });
     app.deck_history && app.deck_history.notify_change();

     return updated_other_card;
   };

   /**
    * @memberOf deck
    */
   deck.get_content = function get_content()
   {
     var cards = deck.get_cards();
     var content = {};
     cards.forEach(function (card) {
       content[card.code] = card.indeck;
     });
     return content;
   };

   /**
    * @memberOf deck
    */
   deck.get_json = function get_json()
   {
     return JSON.stringify(deck.get_content());
   };

   /**
    * @memberOf deck
    */
   deck.get_export = function get_export(format)
   {

   };

   /**
    * @memberOf deck
    */
   deck.get_copies_and_deck_limit = function get_copies_and_deck_limit()
   {
     var copies_and_deck_limit = {};
     deck.get_draw_deck().forEach(function (card) {
       var value = copies_and_deck_limit[card.name];
       if(!value) {
         copies_and_deck_limit[card.name] = {
           nb_copies: card.indeck,
           deck_limit: card.deck_limit
         };
       } else {
         value.nb_copies += card.indeck;
         value.deck_limit = Math.min(card.deck_limit, value.deck_limit);
       }
     });
     return copies_and_deck_limit;
   };

   /**
    * @memberOf deck
    */
   deck.get_problem = function get_problem()
   {
     var expectedMinCardCount = 60;

     // TODO validate side board

     // at least 60 others cards
     if(deck.get_draw_deck_size() < expectedMinCardCount) {
       return 'too_few_cards';
     }

     // too many copies of one card
     if(!_.isUndefined(_.findKey(deck.get_copies_and_deck_limit(), function (value) {
       return value.nb_copies > value.deck_limit;
     }))) {
       return 'too_many_copies';
     }
   };

   /**
    * Returns all list of all faction codes.
    * @memberOf deck
    * @returns {Array}
    */
   deck.get_all_faction_codes = function get_all_faction_codes()
   {
     return _.values(factions);
   };

   /**
    * @memberOf deck
    * @param {string} type_code
    * @param {object} sort
    */
   deck.get_cards_of_type = function get_cards_of_type(type_code, sort)
   {
     return deck.get_cards(sort, {
       type_code: type_code
     });
   };

 })(app.deck = {}, jQuery);
