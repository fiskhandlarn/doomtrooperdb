/* global app, _ */

(function ui_deckimport(ui, $)
 {
   ui.on_content_change = function on_content_change(event)
   {
     var text = $(content).val(),
         slots = {};

     text.match(/[^\r\n]+/g).forEach(function (token) {
       var qty = 1, name = token.trim(), card, expansionName;
       if (name.match(/^(\d+)x? ([^(]+) \(([^)]+)\)/)) {
         qty = parseInt(RegExp.$1, 10);
         name = RegExp.$2.trim();
         expansionName = RegExp.$3.trim();
       } else if(name.match(/^(\d+)x? (.*)/)) {
         qty = parseInt(RegExp.$1, 10);
         name = RegExp.$2.trim();
       }
       if (expansionName) {
         card = app.data.cards.findOne({name: name, expansion_name: expansionName});
         if (!card) {
           card = app.data.cards.findOne({name: name, expansion_code: expansionName});
         }
       } else {
         card = app.data.cards.findOne({name: name});
       }
       if(card) {
         slots[card.code] = qty;
       } else {
         console.log('rejecting string [' + name + ']');
       }
     });

     app.deck.init({
       slots: slots
     });
     app.deck.display('#deck');
     $('input[name=content').val(app.deck.get_json());
   };

   /**
    * called when the DOM is loaded
    * @memberOf ui
    */
   ui.on_dom_loaded = function on_dom_loaded()
   {
     $('#content').change(ui.on_content_change);
   };

   /**
    * called when the app data is loaded
    * @memberOf ui
    */
   ui.on_data_loaded = function on_data_loaded()
   {
   };

   /**
    * called when both the DOM and the data app have finished loading
    * @memberOf ui
    */
   ui.on_all_loaded = function on_all_loaded()
   {
   };


 })(app.ui, jQuery);
