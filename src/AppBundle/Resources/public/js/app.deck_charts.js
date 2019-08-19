(function app_deck_charts(deck_charts, $)
 {
   var charts = [],
       faction_colors = {
         bauhaus:'#e3d852',
         brotherhood:'#cfcfcf',
         capitol:'#1d7a99',
         crescentia:'#509f16',
         cybertronic:'#c00106',
         general:'#a99560',
         imperial:'#e89521',
         legion:'#1c1c1c',
         lutheran: '#ff0000',
         mishima:'#7a7a7a',
         rasputin: '#00ff00',
         templars: '#0000ff',
       };

   deck_charts.chart_faction = function chart_faction()
   {
     var factions = {};
     var draw_deck = app.deck.get_draw_deck();
     draw_deck.forEach(function (card) {
       if(!factions[card.faction_code])
         factions[card.faction_code] = {code: card.faction_code, name: card.faction_name, count: 0};
       factions[card.faction_code].count += card.indeck;
     });

     var data = [];
     _.each(_.values(factions), function (faction) {
       data.push({
         name: faction.name,
         label: '<span class="icon icon-' + faction.code + '"></span>',
         color: faction_colors[faction.code],
         y: faction.count
       });
     });

     $("#deck-chart-faction").highcharts({
       chart: {
         type: 'column'
       },
       title: {
         text: Translator.trans("decks.charts.faction.title")
       },
       subtitle: {
         text: Translator.trans("decks.charts.faction.subtitle")
       },
       xAxis: {
         categories: _.pluck(data, 'label'),
         labels: {
           useHTML: true
         },
         title: {
           text: null
         }
       },
       yAxis: {
         min: 0,
         allowDecimals: false,
         tickInterval: 3,
         title: null,
         labels: {
           overflow: 'justify'
         }
       },
       series: [{
         type: "column",
         animation: false,
         name: Translator.trans("decks.charts.faction.label"),
         showInLegend: false,
         data: data
       }],
       plotOptions: {
         column: {
           borderWidth: 0,
           groupPadding: 0,
           shadow: false
         }
       }
     });
   };

   deck_charts.setup = function setup(options)
   {
     deck_charts.chart_faction();
   };

   $(document).on('shown.bs.tab', 'a[data-toggle=tab]', function (e) {
     deck_charts.setup();
   });

 })(app.deck_charts = {}, jQuery);
