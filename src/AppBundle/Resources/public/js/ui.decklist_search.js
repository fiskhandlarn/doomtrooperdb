/* global app */

(function ui_decklist_search(ui, $)
{


    ui.handle_checkbox_change = function handle_checkbox_change()
    {
        $('#expansions-on').text($('#allowed_expansions').find('input[type="checkbox"]:checked').size());
        $('#expansions-off').text($('#allowed_expansions').find('input[type="checkbox"]:not(:checked)').size());
    };

    ui.send_like = function send_like(event)
    {
        event.preventDefault();
        var that = $(this);
        if($(that).hasClass('processing'))
            return;
        $(that).addClass('processing');
        $.post(Routing.generate('decklist_like'), {
            id: $(that).closest('.social').data('decklist-id')
        }, function (data, textStatus, jqXHR)
        {
            $(that).find('.num').text(data);
            $(that).removeClass('processing');
        });
    };

    ui.send_favorite = function send_favorite(event)
    {
        event.preventDefault();
        var that = $(this);
        if($(that).hasClass('processing'))
            return;
        $(that).addClass('processing');
        $.post(Routing.generate('decklist_favorite'), {
            id: $(that).closest('.social').data('decklist-id')
        }, function (data, textStatus, jqXHR)
        {
            that.find('.num').text(data);
            var title = that.data('original-tooltip');
            that.data('original-tooltip',
                    title === "Add to favorites" ? "Remove from favorites"
                    : "Add to favorites");
            that.attr('title', that.data('original-tooltip'));
            $(that).removeClass('processing');
        });
    };

    /**
     * sets up event handlers ; dataloaded not fired yet
     * @memberOf ui
     */
    ui.setup_event_handlers = function setup_event_handlers()
    {
        $('.social .social-icon-like').on('click', ui.send_like);
        $('.social .social-icon-favorite').on('click', ui.send_favorite);
    };

    /**
     * @memberOf ui
     */
    ui.setup_typeahead = function setup_typeahead()
    {
        function findMatches(q, cb)
        {
            if(q.match(/^\w:/))
                return;
            var regexp = new RegExp(q, 'i');
            cb(app.data.cards.find({name: regexp}));
        }

        $('#card').typeahead({
            hint: true,
            highlight: true,
            minLength: 2
        }, {
            name: 'cardnames',
            displayKey: 'label',
            source: findMatches
        });


        $('#card').on('typeahead:selected typeahead:autocompleted', function (event, data)
        {
            var card = app.data.cards.find({
                label: data.label
            })[0];
            var line = $('<p class="fg-' + card.faction_code + '" style="padding: 3px 5px;border-radius: 3px;border: 1px solid silver"><button type="button" class="close" aria-hidden="true">&times;</button><input type="hidden" name="cards[]" value="' + card.code + '">' +
                    card.label + '</p>');
            line.on({
                click: function (event)
                {
                    line.remove();
                }
            });
            line.insertBefore($('#card'));
            $(event.target).typeahead('val', '');
        });

    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        ui.setup_event_handlers();
        ui.setup_typeahead();
        $('#allowed_expansions').on('change', ui.handle_checkbox_change);

        $('#select_all').on('click', function (event)
        {
            $('#allowed_expansions').find('input[type="checkbox"]:not(:checked)').prop('checked', true);
            ui.handle_checkbox_change();
            return false;
        });

        $('#select_none').on('click', function (event)
        {
            $('#allowed_expansions').find('input[type="checkbox"]:checked').prop('checked', false);
            ui.handle_checkbox_change();
            return false;
        });
    };

    /**
     * called when the app data is loaded
     * @memberOf ui
     */
    ui.on_data_loaded = function on_data_loaded()
    {
        function findMatches(q, cb)
        {
            if(q.match(/^\w:/))
                return;
            var matches = app.data.cards({name: {likenocase: q}}).map(function (record)
            {
                return {value: record.name};
            });
            cb(matches);
        }

        $('#card').typeahead({
            hint: true,
            highlight: true,
            minLength: 3
        }, {
            name: 'cardnames',
            displayKey: 'value',
            source: findMatches
        });
    };

    /**
     * called when both the DOM and the data app have finished loading
     * @memberOf ui
     */
    ui.on_all_loaded = function on_all_loaded()
    {

    };

})(app.ui, jQuery);
