<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    public static $searchKeys = [
        ''  => 'code',
        'a' => 'armor',
        'c' => 'clarification_text',
        'e' => 'expansion',
        'f' => 'faction',
        'g' => 'fight',
        'i' => 'illustrator',
        'l' => 'flavor',
        's' => 'shoot',
        't' => 'type',
        'v' => 'value',
        'x' => 'text',
    ];

    public static $searchTypes = [
        ''  => 'string',
        'a' => 'string',
        'c' => 'string',
        'e' => 'code',
        'f' => 'code',
        'g' => 'string',
        'i' => 'string',
        'l' => 'string',
        's' => 'string',
        't' => 'code',
        'v' => 'string',
        'x' => 'string',
    ];

    public function formAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $dbh = $this->getDoctrine()->getConnection();

        $expansions = $this->get('cards_data')->allsetsdata();

        $expansions = $this->getDoctrine()->getRepository('AppBundle:Expansion')->findAll();
        $types = $this->getDoctrine()->getRepository('AppBundle:Type')->findAll();
        $factions = $this->getDoctrine()->getRepository('AppBundle:Faction')->findAllAndOrderByName();


        $list_illustrators = $dbh->executeQuery(
            "SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator"
        )->fetchAll();
        $illustrators = array_map(function ($card) {
            return $card["illustrator"];
        }, $list_illustrators);

        return $this->render('AppBundle:Search:searchform.html.twig', array(
            "pagetitle" => $this->get("translator")->trans('search.title'),
            "pagedescription" => "Find all the cards of the game, easily searchable.",
            "expansions" => $expansions,
            "types" => $types,
            "factions" => $factions,
            "illustrators" => $illustrators,
        ), $response);
    }

    public function zoomAction($card_code, Request $request)
    {
        $card = $this->getDoctrine()->getRepository('AppBundle:Card')->findByCode($card_code);
        if (!$card) {
            throw $this->createNotFoundException('Sorry, this card is not in the database (yet?)');
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');

        $meta = $card->getName()
            . ", a "
            . $card->getFaction()->getName()
            . " "
            . $card->getType()->getName()
            . " card for $game_name from the set "
            . $card->getExpansion()->getName()
            . " published by $publisher_name.";

        return $this->forward(
            'AppBundle:Search:display',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $card->getCode(),
                'view' => 'card',
                'sort' => 'set',
                'pagetitle' => $card->getName(),
                'meta' => $meta
            )
        );
    }

    public function listAction($expansion_code, $view, $sort, $page, Request $request)
    {
        $expansion = $this->getDoctrine()->getRepository('AppBundle:Expansion')->findByCode($expansion_code);
        if (!$expansion) {
            throw $this->createNotFoundException('This expansion does not exist');
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');

        $meta = $expansion->getName().", a set of cards for $game_name"
            ." by $publisher_name.";

        $key = array_search('expansion', SearchController::$searchKeys);

        return $this->forward(
            'AppBundle:Search:display',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $key.':'.$expansion_code,
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                'pagetitle' => $expansion->getName(),
                'meta' => $meta,
            )
        );
    }

    public function listFactionsAction($faction_code, $view, $sort, $page, Request $request)
    {
        $faction = $this->getDoctrine()->getRepository('AppBundle:Faction')->findByCode($faction_code);
        if (!$faction) {
            throw $this->createNotFoundException('This faction does not exist');
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');

        $meta = $faction->getName().", a set of cards for $game_name"
            ." by $publisher_name.";

        $key = array_search('faction', SearchController::$searchKeys);

        return $this->forward(
            'AppBundle:Search:display',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $key.':'.$faction_code,
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                'pagetitle' => $faction->getName(),
                'meta' => $meta,
            )
        );
    }

    /**
     * Processes the action of the card search form
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function processAction(Request $request)
    {
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';

        $operators = array(":","!","<",">");
        $factions = $this->getDoctrine()->getRepository('AppBundle:Faction')->findAll();

        $params = [];
        if ($request->query->get('q') != "") {
            $params[] = $request->query->get('q');
        }
        foreach (SearchController::$searchKeys as $key => $searchName) {
            $val = $request->query->get($key);
            if (isset($val) && $val != "") {
                if (is_array($val)) {
                    if ($searchName == "faction" && count($val) == count($factions)) {
                        continue;
                    }
                    $params[] = $key.":".implode("|", array_map(function ($s) {
                        return strstr($s, " ") !== false ? "\"$s\"" : $s;
                    }, $val));
                } else {
                    if ($searchName == "date_release") {
                        $op = "";
                    } else {
                        if (!preg_match('/^[\p{L}\p{N}\_\-\&]+$/u', $val, $match)) {
                            $val = "\"$val\"";
                        }
                        $op = $request->query->get($key."o");
                        if (!in_array($op, $operators)) {
                            $op = ":";
                        }
                    }
                    $params[] = "$key$op$val";
                }
            }
        }
        $find = array('q' => implode(" ", $params));
        if ($sort != "name") {
            $find['sort'] = $sort;
        }
        if ($view != "list") {
            $find['view'] = $view;
        }
        return $this->redirect($this->generateUrl('cards_find').'?'.http_build_query($find));
    }

    /**
     * Processes the action of the single card search input
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function findAction(Request $request)
    {
        $q = $request->query->get('q');
        $page = $request->query->get('page') ?: 1;
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';

        // we may be able to redirect to a better url if the search is on a single set
        $conditions = $this->get('cards_data')->syntax($q);
        if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
            if ($conditions[0][0] == array_search('expansion', SearchController::$searchKeys)) {
                $url = $this->get('router')->generate(
                    'cards_list',
                    array(
                        'expansion_code' => $conditions[0][2],
                        'view' => $view,
                        'sort' => $sort,
                        'page' => $page
                    )
                );
                return $this->redirect($url);
            }
            if ($conditions[0][0] == array_search('expansion', SearchController::$searchKeys)) {
                $expansion_position = $conditions[0][2];
                $expansion = $this->getDoctrine()
                    ->getRepository('AppBundle:Expansion')
                    ->findOneBy(array('position' => $expansion_position));
                if ($expansion) {
                    $url = $this->get('router')->generate(
                        'cards_expansion',
                        array(
                            'expansion_code' => $expansion->getCode(),
                            'view' => $view,
                            'sort' => $sort,
                            'page' => $page
                        )
                    );
                    return $this->redirect($url);
                }
            }
        }

        return $this->forward(
            'AppBundle:Search:display',
            array(
                'q' => $q,
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                '_route' => $request->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                '_get_params' => $request->query->all()
            )
        );
    }

    public function displayAction(Request $request, $q, $sort, $view = "card", $page = 1, $pagetitle = "", $meta = "")
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $cards = [];
        $first = 0;
        $last = 0;
        $pagination = '';

        $pagesizes = array(
            'list' => 240,
            'spoiler' => 240,
            'card' => 20,
            'scan' => 20,
            'short' => 1000,
        );
        $includeReviews = false;

        if (!array_key_exists($view, $pagesizes)) {
            $view = 'list';
        }

        $conditions = $this->get('cards_data')->syntax($q);
        $conditions = $this->get('cards_data')->validateConditions($conditions);

        // reconstruction de la bonne chaine de recherche pour affichage
        $q = $this->get('cards_data')->buildQueryFromConditions($conditions);
        if ($q && $rows = $this->get('cards_data')->getSearchRows($conditions, $sort)) {
            if (count($rows) == 1) {
                $includeReviews = true;
            }

            if ($pagetitle == "") {
                if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
                    if ($conditions[0][0] == "e") {
                        $expansion = $this->getDoctrine()
                            ->getRepository('AppBundle:Expansion')
                            ->findOneBy(array("code" => $conditions[0][2]));
                        if ($expansion) {
                            $pagetitle = $expansion->getName();
                        }
                    }
                    if ($conditions[0][0] == "c") {
                        $expansion = $this->getDoctrine()
                            ->getRepository('AppBundle:Expansion')
                            ->findOneBy(array("code" => $conditions[0][2]));
                        if ($expansion) {
                            $pagetitle = $expansion->getName();
                        }
                    }
                }
            }


            // calcul de la pagination
            $nb_per_page = $pagesizes[$view];
            $first = $nb_per_page * ($page - 1);
            if ($first > count($rows)) {
                $page = 1;
                $first = 0;
            }
            $last = $first + $nb_per_page;

            // data à passer à la view
            for ($rowindex = $first; $rowindex < $last && $rowindex < count($rows); $rowindex++) {
                $card = $rows[$rowindex];
                $expansion = $card->getExpansion();
                $cardinfo = $this->get('cards_data')->getCardInfo($card, false, null);
                if ($includeReviews) {
                    $cardinfo['reviews'] = $this->get('cards_data')->getReviews($card);
                }
                $cards[] = $cardinfo;
            }

            $first += 1;

            // si on a des cartes on affiche une bande de navigation/pagination
            if (count($rows)) {
                if (count($rows) == 1) {
                    $pagination = $this->setnavigation($card, $q, $view, $sort);
                } else {
                    $pagination = $this->pagination($nb_per_page, count($rows), $first, $q, $view, $sort);
                }
            }

            // si on est en vue "short" on casse la liste par tri
            if (count($cards) && $view == "short") {
                $sortfields = array(
                    'set' => 'expansion_name',
                    'name' => 'name',
                    'faction' => 'faction_name',
                    'type' => 'type_name',
                );

                $brokenlist = [];
                for ($i=0; $i<count($cards); $i++) {
                    $val = $cards[$i][$sortfields[$sort]];
                    if ($sort == "name") {
                        $val = substr($val, 0, 1);
                    }
                    if (!isset($brokenlist[$val])) {
                        $brokenlist[$val] = [];
                    }
                    array_push($brokenlist[$val], $cards[$i]);
                }
                $cards = $brokenlist;
            }
        }

        $searchbar = $this->renderView('AppBundle:Search:searchbar.html.twig', array(
            "q" => $q,
            "view" => $view,
            "sort" => $sort,
        ));

        if (empty($pagetitle)) {
            $pagetitle = $q;
        }

        // attention si $s="short", $cards est un tableau à 2 niveaux au lieu de 1 seul
        return $this->render('AppBundle:Search:display-'.$view.'.html.twig', array(
            "view" => $view,
            "sort" => $sort,
            "cards" => $cards,
            "first"=> $first,
            "last" => $last,
            "searchbar" => $searchbar,
            "pagination" => $pagination,
            "pagetitle" => $pagetitle,
            "metadescription" => $meta,
            "includeReviews" => $includeReviews,
        ), $response);
    }

    public function setnavigation($card, $q, $view, $sort)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Card');
        $prev = $repo->findPreviousCard($card);
        $next = $repo->findNextCard($card);
        return $this->renderView('AppBundle:Search:setnavigation.html.twig', array(
            "prevtitle" => $prev ? $prev->getName() : "",
            "prevhref" => $prev ? $this->get('router')
            ->generate(
                'cards_zoom',
                array('card_code' => $prev->getCode())
            ) : "",
            "nexttitle" => $next ? $next->getName() : "",
            "nexthref" => $next ? $this->get('router')
            ->generate(
                'cards_zoom',
                array('card_code' => $next->getCode())
            ) : "",
            "settitle" => $card->getExpansion()->getName(),
            "sethref" => $this->get('router')
            ->generate(
                'cards_list',
                array('expansion_code' => $card->getExpansion()->getCode())
            ),
        ));
    }

    public function paginationItem($q, $v, $s, $ps, $pi, $total)
    {
        return $this->renderView('AppBundle:Search:paginationitem.html.twig', array(
            "href" => $q == null ? "" : $this->get('router')
            ->generate('cards_find', array('q' => $q, 'view' => $v, 'sort' => $s, 'page' => $pi)),
            "ps" => $ps,
            "pi" => $pi,
            "s" => $ps*($pi-1)+1,
            "e" => min($ps*$pi, $total),
        ));
    }

    public function pagination($pagesize, $total, $current, $q, $view, $sort)
    {
        if ($total < $pagesize) {
            $pagesize = $total;
        }

        $pagecount = ceil($total / $pagesize);
        $pageindex = ceil($current / $pagesize); #1-based

        $first = "";
        if ($pageindex > 2) {
            $first = $this->paginationItem($q, $view, $sort, $pagesize, 1, $total);
        }

        $prev = "";
        if ($pageindex > 1) {
            $prev = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex - 1, $total);
        }

        $current = $this->paginationItem(null, $view, $sort, $pagesize, $pageindex, $total);

        $next = "";
        if ($pageindex < $pagecount) {
            $next = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex + 1, $total);
        }

        $last = "";
        if ($pageindex < $pagecount - 1) {
            $last = $this->paginationItem($q, $view, $sort, $pagesize, $pagecount, $total);
        }

        return $this->renderView('AppBundle:Search:pagination.html.twig', array(
            "first" => $first,
            "prev" => $prev,
            "current" => $current,
            "next" => $next,
            "last" => $last,
            "count" => $total,
            "ellipsisbefore" => $pageindex > 3,
            "ellipsisafter" => $pageindex < $pagecount - 2,
        ));
    }
}
