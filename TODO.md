* Change localhost to variable
* factions as array
* DeckValidationHelper.php
  * validate side board
* TODO's in source code
* JS
* CSS
  * selection.json
  * tighten card page text and image
  * nicer looking bullet list in "Smart filter syntax"? (at least remove margin-left)
  * colors
    * footer
* `google_analytics_tracking_code`
* sidebar
  * compare/diff `src/AppBundle/Resources/views/Compare/deck_compare.html.twig` b639334eb3f02d223022feebf0426b34571783df
* images from https://raw.githubusercontent.com/ instead?
* stylelint
* INSERT decklist 1 to pass ApiControllerTest::testGetDecklist?
* smaller throbber in deckbuilder
* add throbber in `/decklist/view/` and `/deck/publish/`
* don't output div wrapping type if cards of type is nonexistant in `.deck-content`
* CSS -> SCSS
  * different col spans in `.deck-content` in `deck/publish`
  * collapse col heights in `.deck-content`
  * bootstrap 4
* replace all icons via https://icomoon.io/app/
* add FSAV
  * columns in global search
  * columns in deckbuilder list?
  * icons on cards
  * values/icons on all cards that have those properties
* https in js
  * replace https://localhost
* deck settings for tournament/classic, restricted/banned
* replace `src/AppBundle/Resources/public/images/factions`
* add to card info and card searches (inline in builder and on global search)
  * flavor
    * fix `app.smart_filter.js`'s `a: [add_string_sf, 'flavor', Translator.trans('decks.smartfilter.filters.flavor')],`(search for `a:airborne` returns all cards, http://localhost/find?q=a%3AAirborne&sort=set&view=list yields MARTIAN BANSHEE)
  * clarification_text
* test import
* do we even need `selection.json`?
* new test for /factions/
* 3 columns in deckbuilder list
  * fix pushed "5" on the row beneath
  * vertical align center (for long card names)
* add cache buster for js and css
* "sort by expansion, then number" shouldn't show expansion next to each card
* remove eot and otf (and woff?)
* replace `decklists_by_faction` on index
* test emails: `src/AppBundle/Resources/views/Email/`
* mark current page as `.active` in navbar
* expand profile/user dropdown menu on xs
* fix for logo vs hamburger in navbar on small/tight screens
* credits += Leon Ilian
* real background pattern
* different front page art?
* prevent mouse default event for user menu in navbar

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

## Prio 2
* deployer
* fix mapping and remove `--skip-mapping` from `doctrine:schema:validate`
* certain searches in deckbuilder returns all cards (e.g. `g:-1`)
* list all expansions instead of from/to in
  * `src/AppBundle/Resources/views/Export/default.txt.twig`
  * `src/AppBundle/Resources/public/js/app.ui.js` x3
* move "Member since" to messages.*.yml
* add graphs for FSAV(?)
* add support for digital doomtrooper (3rd edition)
* global var for `icons = 'baratheon greyjoy alliance lannister martell art thenightswatch beast stark targaryen tyrell equipment fortification ki mission relic special symmetry general'.split(' ');`
* get types from api instead of hardcoded in `ui.build_type_selector`, `SlotCollectionDecorator`
* separate tags with commas instead
* update to symfony 4: https://symfony.com/doc/current/setup/upgrade_major.html
* thronesdb issues
  * https://github.com/ThronesDB/thronesdb/labels/bug
  * https://github.com/ThronesDB/thronesdb/labels/code%20quality
* add icons for expansions? `src/AppBundle/Resources/public/images/factions`
  * cards
  * search
  * decklist search
