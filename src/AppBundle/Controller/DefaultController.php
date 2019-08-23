<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Model\DecklistManager;
use AppBundle\Entity\Decklist;

class DefaultController extends Controller
{
    use LocaleAwareTemplating;

    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        /**
         * @var $decklist_manager DecklistManager
         */
        $decklist_manager = $this->get('decklist_manager');
        $decklist_manager->setLimit(10);

        $typeNames = [];
        foreach ($this->getDoctrine()->getRepository('AppBundle:Type')->findAll() as $type) {
            $typeNames[$type->getCode()] = $type->getName();
        }

        $decklists = [];
        $factions = $this->getDoctrine()
            ->getRepository('AppBundle:Faction')
            ->findBy(['code' => 'ASC']);

        $paginator = $decklist_manager->findDecklistsByPopularity();

        foreach ($paginator as $decklist) {
            if ($decklist) {
                $array['decklist'] = $decklist;

                $countByType = $decklist->getSlots()->getCountByType();
                $counts = [];
                foreach ($countByType as $code => $qty) {
                    $typeName = $typeNames[$code];
                    $counts[] = $qty . " " . $typeName . "s";
                }
                $array['count_by_type'] = join(' &bull; ', $counts);

                $decklists[] = $array;
            }
        }

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');

        return $this->render('AppBundle:Default:index.html.twig', [
            'pagetitle' =>  "$game_name Deckbuilder",
            'pagedescription' => "Build your deck for $game_name by $publisher_name."
                . " Browse the cards and the thousand of decklists submitted by the community."
                . " Publish your own decks and get feedback.",
            'decklists' => $decklists
        ], $response);
    }

    public function aboutAction(Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render($this->getLocaleSpecificViewPath('about', $request->getLocale()), array(
                "pagetitle" => "About",
                "website_name" => $this->container->getParameter('website_name'),
                "game_name" => $this->container->getParameter('game_name'),
        ), $response);
    }

    public function apiIntroAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render('AppBundle:Default:apiIntro.html.twig', array(
                "pagetitle" => "API",
                "game_name" => $this->container->getParameter('game_name'),
                "publisher_name" => $this->container->getParameter('publisher_name'),
        ), $response);
    }
}
