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
        var text = card.expansion_name + ' #' + card.position + '. ';
        return text;
    }

    /**
     * @memberOf format
     */
    format.info = function info(card)
    {
        var text = '<span class="card-type">' + card.type_name + '. </span>';
        console.log('TODO warriors and warzones');
        switch(card.type_code) {
            case 'warrior':
                text += Translator.trans('card.info.shoot') + ': ' + card.shoot + '. ';
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
