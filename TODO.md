* Change localhost to variable
* update to symfony 4: https://symfony.com/doc/current/setup/upgrade_major.html
* change default values in Incenteev\ParameterHandler\ScriptHandler::buildParameters
* thronesdb issues
  * https://github.com/ThronesDB/thronesdb/labels/bug
  * https://github.com/ThronesDB/thronesdb/labels/code%20quality
* remove Reviews
* restore Reviews
* remove Rules
* restore Rules
* factions as array
* DeckValidationHelper.php
  * validate side board
* TODO's in source code
* JS
* CSS
  * selection.json
* fonts
  * convert svg to other format(s?)
* about.*.html.twig
* footer.*.html.twig
* faq, tournamentregulations, rulesreference
* `<span class="fas fa-gift donator" title="DoomtrooperDB Gracious Donator"></span>`
  * remove /donators
* info@doomtrooperdb.com
* `google_analytics_tracking_code`
* sidebar
* default.txt.twig
* card-props.html.twig
* images from https://raw.githubusercontent.com/ instead?
* stylelint
* languages.png
* CardsData::addAbbrTags()
* `macros.html.twig`
* SAFV-order in CardsData:
```
            case 'cost':
                $qb->orderBy('c.type')->addOrderBy('c.cost')->addOrderBy('c.income');
                break;
            case 'strength':
                $qb->orderBy('c.type')->addOrderBy('c.strength')->addOrderBy('c.initiative');
                break;
```
* INSERT decklist 1 to pass ApiControllerTest::testGetDecklist?
* ```
        a: [add_string_sf, 'flavor', Translator.trans('decks.smartfilter.filters.flavor')],
        b: [add_integer_sf, 'claim', Translator.trans('decks.smartfilter.filters.claim')],
        e: [add_string_sf, 'expansion_code', Translator.trans('decks.smartfilter.filters.expansion_code')],
        f: [add_string_sf, 'faction_code', Translator.trans('decks.smartfilter.filters.faction_code')],
        g: [add_boolean_sf, 'is_intrigue', Translator.trans('decks.smartfilter.filters.is_intrigue')],
        h: [add_integer_sf, 'reserve', Translator.trans('decks.smartfilter.filters.reserve')],
        i: [add_string_sf, 'illustrator', Translator.trans('decks.smartfilter.filters.illustrator')],
        d: [add_string_sf, 'designer', Translator.trans('decks.smartfilter.filters.designer')],
        k: [add_string_sf, 'traits', Translator.trans('decks.smartfilter.filters.traits')],
        l: [add_boolean_sf, 'is_loyal', Translator.trans('decks.smartfilter.filters.is_loyal')],
        m: [add_boolean_sf, 'is_military', Translator.trans('decks.smartfilter.filters.is_military')],
        n: [add_integer_sf, 'income', Translator.trans('decks.smartfilter.filters.income')],
        o: [add_integer_sf, 'cost', Translator.trans('decks.smartfilter.filters.cost')],
        p: [add_boolean_sf, 'is_power', Translator.trans('decks.smartfilter.filters.is_power')],
        s: [add_integer_sf, 'strength', Translator.trans('decks.smartfilter.filters.strength')],
        t: [add_string_sf, 'type_code', Translator.trans('decks.smartfilter.filters.type_code')],
        u: [add_boolean_sf, 'is_unique', Translator.trans('decks.smartfilter.filters.is_unique')],
        v: [add_integer_sf, 'initiative', Translator.trans('decks.smartfilter.filters.initiative')],
        x: [add_string_sf, 'text', Translator.trans('decks.smartfilter.filters.text')],
        y: [add_integer_sf, 'quantity', Translator.trans('decks.smartfilter.filters.quantity')]
```
* SocialController::searchForm
* restore translations of cards
  * https://github.com/ThronesDB/thronesdb/commit/1319dd5989175589ff3689e9891bba477fd5a573#diff-fd9da3c383c79d8ec8de1c215b6bbd41
  * run `php bin/console app:import:trans ../doomtrooperdb-json-data` if you want to import the translations
* remove unused js
  * app.deck_browser
  * app.deck_gallery
  * levenshtein
  * string_score.min
* combine all internal and external js into app.js
* fix mapping and remove `--skip-mapping` from `doctrine:schema:validate`
* disable language menu
* restore language menu
* ```        switch (deck.sort_type) {
            case "type":
            default:
                console.log('TODO!');
```
* add more fields
  * `src/AppBundle/Controller/SearchController.php`
    * update `$searchType`
  * `src/AppBundle/Resources/public/js/app.smart_filter.js`
  * messages.en.yml: `smartfilter.filters`
  * `src/AppBundle/Services/CardsData.php`
  * `src/AppBundle/Resources/views/Search/searchform.html.twig`
  * `src/AppBundle/Resources/views/Export/default.txt.twig`
  * `src/AppBundle/Services/CardsData.php`
* more fields in `src/AppBundle/Resources/public/js/app.ui.js` x3 (`var sections`)
* change keywords
* icons
  * icons.css
  * messages.*.yml
  * replace all icons
  * add icons for
    * lutheran
    * rasputin
    * templars
    * warrior
    * warzone
* `src/AppBundle/Resources/views/Search/card-props.html.twig`
  * additional fields
    * warrior
    * warzone
  * remove GoT terms
* add graphs for SAFV(?)
* allow all factions in deckbuilder (`DeckValidationHelper::canIncludeCard`?)
  * Deck.orm.yml
* remove "last expansion"
* move Makefile contents to README.md?
* remove public/app_dev.php
* remove patchwork/jsqueeze
* http://localhost/card/05008
* https in js
* add deck name to `build_markdown` and `build_dtcards`
* remove items in Options js dropdown
* deck settings for tournament/classic, restricted/banned
* don't sort factions in deckbuilder
* `app_deck_charts.charts.faction_colors`
* global var for `icons = 'baratheon greyjoy alliance lannister martell art thenightswatch beast stark targaryen tyrell equipment fortification ki mission relic special symmetry general'.split(' ');`
* restore `SocialController::searchForm`
