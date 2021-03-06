/* global Translator, app */

(function ui_deck(ui, $)
 {
   var dom_loaded = new $.Deferred(),
       data_loaded = new $.Deferred();

   function build_plaintext(deck) {
     var lines = [];
     var included_expansions = deck.get_included_expansions({ 'position': 1 });

     var sortOrder = { "name": 1 };
     var sections = get_sections(deck, sortOrder);

     lines.push(deck.get_name());
     lines.push("");
     if (included_expansions.length > 1) {
       lines.push("Expansions: From " + included_expansions[0].name + ' to ' + included_expansions[included_expansions.length - 1].name);
     } else {
       lines.push("Expansions: From " + included_expansions[0].name);
     }
     Object.getOwnPropertyNames(sections).forEach(function(section) {
       lines.push("");
       lines.push(section + " (" + deck.get_nb_cards(sections[section]) + "):");
       sections[section].forEach(function(card) {
         lines.push(card.indeck + "x " + card.name + " (" + card.expansion_code + ")");
       });
     });
     return lines;
   }

   function build_markdown(deck) {
     var lines = [];
     var included_expansions = deck.get_included_expansions({ 'position': 1 });

     var sortOrder = { "name": 1 };
     var sections = get_sections(deck, sortOrder);

     var print_card_line = function(card, show_quantity) {
       var out = "";
       show_quantity = !!show_quantity;

       if (show_quantity) {
         out = out + card.indeck + 'x ';
       }
       out  = out + '[' + card.name + ' \\('+ card.expansion_code +'\\)](https://localhost/card/' + card.code + ')';
       return out;
     };

     lines.push("# " + deck.get_name());
     lines.push("");

     lines.push("## Expansions");
     if (included_expansions.length > 1) {
       lines.push("From " + included_expansions[0].name + ' to ' + included_expansions[included_expansions.length - 1].name);
     } else {
       lines.push("From " + included_expansions[0].name);
     }
     lines.push("");

     lines.push("## Cards");
     Object.getOwnPropertyNames(sections).forEach(function(section) {
       lines.push("");
       lines.push("### " + section + " (" + deck.get_nb_cards(sections[section]) + "):");
       lines.push("");
       sections[section].forEach(function(card) {
         lines.push("- " + print_card_line(card, true));
       });
     });

     return lines;
   }

   function build_dtcards(deck) {
     var lines = [];
     var included_expansions = deck.get_included_expansions({ 'position': 1 });

     var sortOrder = { "name": 1 };
     var sections = get_sections(deck, sortOrder);

     var print_card_line = function(card, show_quantity) {
       var out = "";
       show_quantity = !!show_quantity;

       if (show_quantity) {
         out = out + card.indeck + 'x ';
       }
       out  = out + '[dt]' + card.name + ' ('+ card.expansion_code + ')[/dt]';
       return out;
     };

     lines.push(deck.get_name());
     lines.push("");
     if (included_expansions.length > 1) {
       lines.push("Expansions: From " + included_expansions[0].name + ' to ' + included_expansions[included_expansions.length - 1].name);
     } else {
       lines.push("Expansions: From " + included_expansions[0].name);
     }

     Object.getOwnPropertyNames(sections).forEach(function(section) {
       lines.push("");
       lines.push(section + " (" + deck.get_nb_cards(sections[section]) + "):");
       lines.push("");
       sections[section].forEach(function(card) {
         lines.push(print_card_line(card, true));
       });
     });

     return lines;
   }

   var get_sections = function get_sections(deck, sortOrder) {
     return {
       'Alliances': deck.get_cards_of_type('alliance', sortOrder),
       'Art': deck.get_cards_of_type('art', sortOrder),
       'Beasts': deck.get_cards_of_type('beast', sortOrder),
       'Dark Symmetry': deck.get_cards_of_type('symmetry', sortOrder),
       'Equipment': deck.get_cards_of_type('equipment', sortOrder),
       'Fortifications': deck.get_cards_of_type('fortification', sortOrder),
       'Ki Powers': deck.get_cards_of_type('ki', sortOrder),
       'Missions': deck.get_cards_of_type('mission', sortOrder),
       'Relics': deck.get_cards_of_type('relic', sortOrder),
       'Specials': deck.get_cards_of_type('special', sortOrder),
       'Warriors': deck.get_cards_of_type('warrior', sortOrder),
       'Warzones': deck.get_cards_of_type('warzone', sortOrder),
     };
   };

   /**
    * called when the DOM is loaded
    * @memberOf ui
    */
   ui.on_dom_loaded = function on_dom_loaded()
   {};

   /**
    * called when the app data is loaded
    * @memberOf ui
    */
   ui.on_data_loaded = function on_data_loaded()
   {};

   /**
    * called when both the DOM and the app data have finished loading
    * @memberOf ui
    */
   ui.on_all_loaded = function on_all_loaded()
   {};

   ui.insert_alert_message = function ui_insert_alert_message(type, message)
   {
     var alert = $('<div class="alert" role="alert"></div>').addClass('alert-' + type).append(message);
     $('#wrapper>div.container').first().prepend(alert);
   };

   ui.export_plaintext = function export_plaintext(deck) {
     $('#export-deck').html(build_plaintext(deck).join("\n"));
     $('#exportModal').modal('show');
   };

   ui.export_markdown = function export_markdown(deck) {
     $('#export-deck').html(build_markdown(deck).join("\n"));
     $('#exportModal').modal('show');
   };

   ui.export_dtcards = function export_dtcards(deck) {
     $('#export-deck').html(build_dtcards(deck).join("\n"));
     $('#exportModal').modal('show');
   };

   $(document).ready(function () {
     $('[data-toggle="tooltip"]').tooltip();
     $('time').each(function (index, element) {
       var datetime = moment($(element).attr('datetime'));
       $(element).html(datetime.fromNow());
       $(element).attr('title', datetime.format('LLLL'));
     });
     if(typeof ui.on_dom_loaded === 'function')
       ui.on_dom_loaded();
     dom_loaded.resolve();
   });
   $(document).on('data.app', function () {
     if(typeof ui.on_data_loaded === 'function')
       ui.on_data_loaded();
     data_loaded.resolve();
   });
   $(document).on('start.app', function (){
     if(typeof ui.on_all_loaded === 'function')
       ui.on_all_loaded();
   });
   $.when(dom_loaded, data_loaded).done(function () {
     setTimeout(function () {
       $(document).trigger('start.app');
     }, 0);
   });

 })(app.ui = {}, jQuery);
