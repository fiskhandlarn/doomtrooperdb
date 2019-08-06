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
* replace pack
* remove
  * influence
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
