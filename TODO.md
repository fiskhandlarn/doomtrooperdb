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
* rename 'core' to 'unl'
  * `core: "Core / Deluxe"`
  * `usecore: "{1} Use 1 Core Set | ]1, Inf] Use %core% Core Sets"`
  * set -> expansion ? (`set: Ordenar por capítulo`, "capítulo" is used for "packs"; `sets: label: Packs`)
* update tests/
* factions as array
* fix API 500 error
* DeckValidationHelper.php
* TODO's in source code
* JS
* CSS
    * selection.json
* fonts
* about.*.html.twig
* footer.*.html.twig
* faq, tournamentregulations, rulesreference
* `<span class="fas fa-gift donator" title="DoomtrooperDB Gracious Donator"></span>`
* `src/AppBundle/Services/CardsData.php`
* info@doomtrooperdb.com
* `google_analytics_tracking_code`
* sideboard
* default.txt.twig
* card-props.html.twig
* images from https://raw.githubusercontent.com/ instead?
* stylelint
* document npm run *
* languages.png
* CardsData::addAbbrTags()
* remove
  * influence
  * trait
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
  * run `php bin/console app:import:trans ../doomtrooperdb-json-data` if you want to import the translations
