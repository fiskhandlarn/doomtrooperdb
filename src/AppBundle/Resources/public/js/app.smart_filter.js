(function app_smart_filter(smart_filter, $)
 {

   var SmartFilterQuery = [];

   var configuration = {
     a: [add_string_sf, 'armor', Translator.trans('decks.smartfilter.filters.armor')],
     e: [add_string_sf, 'expansion_code', Translator.trans('decks.smartfilter.filters.expansion_code')],
     f: [add_string_sf, 'faction_code', Translator.trans('decks.smartfilter.filters.faction_code')],
     g: [add_string_sf, 'fight', Translator.trans('decks.smartfilter.filters.fight')],
     i: [add_string_sf, 'illustrator', Translator.trans('decks.smartfilter.filters.illustrator')],
     l: [add_string_sf, 'flavor', Translator.trans('decks.smartfilter.filters.flavor')],
     s: [add_string_sf, 'shoot', Translator.trans('decks.smartfilter.filters.shoot')],
     t: [add_string_sf, 'type_code', Translator.trans('decks.smartfilter.filters.type_code')],
     v: [add_string_sf, 'value', Translator.trans('decks.smartfilter.filters.value')],
     x: [add_string_sf, 'text', Translator.trans('decks.smartfilter.filters.text')],
   };

   /**
    * called when the list is refreshed
    * @memberOf smart_filter
    */
   smart_filter.get_query = function get_query(query)
   {
     return _.extend(query, SmartFilterQuery);
   };

   /**
    * called when the filter input is modified
    * @memberOf smart_filter
    */
   smart_filter.update = function update(value)
   {
     var conditions = filterSyntax(value);
     SmartFilterQuery = {};

     for(var i = 0; i < conditions.length; i++) {
       var condition = conditions[i];
       var type = condition.shift();
       var operator = condition.shift();
       var values = condition;

       var tools = configuration[type];
       if(tools) {
         tools[0].call(this, tools[1], operator, values);
       }
     }
   };

   smart_filter.get_help = function get_help()
   {
     var items = _.map(configuration, function (value, key)
                       {
                         return '<li><tt>' + key + '</tt> &ndash; ' + value[2] + '</li>';
                       });
     return '<ul>' + items.join('') + '</ul><p>' + Translator.trans('decks.smartfilter.example') + '</p>';

   }

   function add_string_sf(key, operator, values)
   {
     for(var j = 0; j < values.length; j++) {
       values[j] = new RegExp(values[j], 'i');
     }
     switch(operator) {
         case ":":
           SmartFilterQuery[key] = {
             '$in': values
           };
           break;
         case "!":
           SmartFilterQuery[key] = {
             '$nin': values
           };
           break;
     }
   }

   function filterSyntax(query)
   {
     // return a list of conditions (array)
     // each condition is an array with n> 1 elements
     // the first is the condition type (0 or 1 character)
     // the following are the arguments, in OR

     query = query.replace(/^\s*(.*?)\s*$/, "$1").replace('/\s+/', ' ');

     var list = [];
     var cond = null;
     // the automaton has 3 states:
     // 1: type search
     // 2: main argument search
     // 3: additional argument search
     // 4: parsing error, search for the next condition
     // if he falls on an argument while he is searching for a type, then the
     // type is empty
     var etat = 1;
     while(query != "") {
       if(etat == 1) {
         if(cond !== null && etat !== 4 && cond.length > 2) {
           list.push(cond);
         }
         // we start by looking for a type of condition
         if(query.match(/^(\w)([:<>!])(.*)/)) { // jeton "condition:"
           cond = [RegExp.$1.toLowerCase(), RegExp.$2];
           query = RegExp.$3;
         } else {
           cond = ["", ":"];
         }
         etat = 2;
       } else {
         if(query.match(/^"([^"]*)"(.*)/) // token "free text in quotation marks"
            || query.match(/^([^\s]+)(.*)/) // token "text allowed without quotation marks"
           ) {
           if((etat === 2 && cond.length === 2) || etat === 3) {
             cond.push(RegExp.$1);
             query = RegExp.$2;
             etat = 2;
           } else {
             // error
             query = RegExp.$2;
             etat = 4;
           }
         } else if(query.match(/^\|(.*)/)) { // jeton "|"
           if((cond[1] === ':' || cond[1] === '!')
              && ((etat === 2 && cond.length > 2) || etat === 3)) {
             query = RegExp.$1;
             etat = 3;
           } else {
             // error
             query = RegExp.$1;
             etat = 4;
           }
         } else if(query.match(/^ (.*)/)) { // jeton " "
           query = RegExp.$1;
           etat = 1;
         } else {
           // error
           query = query.substr(1);
           etat = 4;
         }
       }
     }
     if(cond !== null && etat !== 4 && cond.length > 2) {
       list.push(cond);
     }
     return list;
   }

   $(function ()
     {
       $('.smart-filter-help').tooltip({
         container: 'body',
         delay: 1000,
         html: true,
         placement: 'bottom',
         title: smart_filter.get_help(),
         trigger: 'hover'
       });
     })

 })(app.smart_filter = {}, jQuery);
