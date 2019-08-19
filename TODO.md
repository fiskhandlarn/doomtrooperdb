* Change localhost to variable
* update to symfony 4: https://symfony.com/doc/current/setup/upgrade_major.html
* change default values in Incenteev\ParameterHandler\ScriptHandler::buildParameters
* thronesdb issues
  * https://github.com/ThronesDB/thronesdb/labels/bug
  * https://github.com/ThronesDB/thronesdb/labels/code%20quality
* factions as array
* DeckValidationHelper.php
  * validate side board
* TODO's in source code
* JS
* CSS
  * selection.json
  * tighten card page text and image
  * nicer looking bullet list in "Smart filter syntax"? (at least remove margin-left)
* fonts
  * convert svg to other format(s?)
* `google_analytics_tracking_code`
* sidebar
* images from https://raw.githubusercontent.com/ instead?
* stylelint
* INSERT decklist 1 to pass ApiControllerTest::testGetDecklist?
* fix mapping and remove `--skip-mapping` from `doctrine:schema:validate`
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
* add graphs for FSAV(?)
* add FSAV
  * columns in global search
  * columns in deckbuilder list?
  * icons on cards
  * values/icons on all cards that have those properties
* allow all factions in deckbuilder (`DeckValidationHelper::canIncludeCard`?)
  * Deck.orm.yml
* remove "last expansion"
* remove public/app_dev.php
* remove patchwork/jsqueeze
* https in js
* add deck name to `build_markdown` and `build_dtcards`
* remove items in Options js dropdown
* deck settings for tournament/classic, restricted/banned
* don't sort factions in deckbuilder
* `app_deck_charts.charts.faction_colors`
* global var for `icons = 'baratheon greyjoy alliance lannister martell art thenightswatch beast stark targaryen tyrell equipment fortification ki mission relic special symmetry general'.split(' ');`
* replace `src/AppBundle/Resources/public/images/factions`
* remove link for comment on decklists https://github.com/ThronesDB/thronesdb/issues/352
* remove available for cards and expansions
* add icons for expansions? `src/AppBundle/Resources/public/images/factions`
  * cards
  * search
  * decklist search
* add to card info and card searches (inline in builder and on global search)
  * flavor
    * fix `app.smart_filter.js`'s `a: [add_string_sf, 'flavor', Translator.trans('decks.smartfilter.filters.flavor')],`(search for `a:airborne` returns all cards, http://localhost/find?q=a%3AAirborne&sort=set&view=list yields MARTIAN BANSHEE)
  * clarification_text
* http://localhost/tag/bauhaus ?
* add tags to deck?
* move "Member since" to messages.*.yml
* "Quantity in expansion"? http://localhost/deck/edit/14
* remove on/off

* deployer

## Restore
* about credits
* Rules
* F.A.Q.
* Tournament Regulations
* Donators
* translations of cards
  * https://github.com/ThronesDB/thronesdb/commit/1319dd5989175589ff3689e9891bba477fd5a573#diff-fd9da3c383c79d8ec8de1c215b6bbd41
  * run `php bin/console app:import:trans ../doomtrooperdb-json-data` if you want to import the translations
* language menu
* decklist-faction-image (from used factions in deck)
