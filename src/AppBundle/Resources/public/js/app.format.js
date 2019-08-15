(function app_format(format, $)
 {

   /**
    * @memberOf format
    */
   format.name = function name(card)
   {
     return card.name;
   }

   format.faction = function faction(card)
   {
     return '<span class="fg-' + card.faction_code + ' icon-' + card.faction_code + '"></span> ' + card.faction_name + '. ';
   }

   /**
    * @memberOf format
    */
   format.expansion = function expansion(card)
   {
     var text = card.expansion_name;
     return text;
   }

   /**
    * @memberOf format
    */
   format.info = function info(card)
   {
     var text = '<span class="card-type">' + card.type_name + '</span>';
     switch(card.type_code) {
         case 'warrior':
         case 'warzone':
           text += ' ';
           text += Translator.trans('card.info.fight') + ': ' + card.fight + '. ';
           text += Translator.trans('card.info.shoot') + ': ' + card.shoot + '. ';
           text += Translator.trans('card.info.armor') + ': ' + card.armor + '. ';
           text += Translator.trans('card.info.value') + ': ' + card.value + '. ';
           break;
     }
     return text;
   };

   /**
    * @memberOf format
    */
   format.text = function text(card)
   {
     var text = card.text || '';
     text = text.replace(/\[(\w+)\]/g, '<span class="icon-$1"></span>')
     text = text.split("\n").join('</p><p>');
     if(card.designer) {
       text = text + '<p class="card-designer">' + card.designer + '</p>';
     }
     return '<p>' + text + '</p>';
   };

 })(app.format = {}, jQuery);
