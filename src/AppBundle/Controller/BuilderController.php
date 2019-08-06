<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\Faction;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Deckslot;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Deckchange;

/**
 * Class BuilderController
 * @package AppBundle\Controller
 */
class BuilderController extends Controller
{

    public function buildformAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));


        $em = $this->getDoctrine()->getManager();

        $factions = $em->getRepository('AppBundle:Faction')->findAllAndOrderByName();

        return $this->render(
            'AppBundle:Builder:initbuild.html.twig',
            [
                'pagetitle' => $this->get('translator')->trans('decks.form.new'),
                'factions' => $factions,
            ],
            $response
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function initbuildAction(Request $request)
    {
        $translator = $this->get('translator');

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $faction_code = $request->request->get('faction');

        if (!$faction_code) {
            $this->get('session')->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

            return $this->redirect($this->generateUrl('deck_buildform'));
        }

        $faction = $em->getRepository('AppBundle:Faction')->findByCode($faction_code);
        if (!$faction) {
            $this->get('session')->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

            return $this->redirect($this->generateUrl('deck_buildform'));
        }
        $tags = [$faction_code];

        $agenda = null;
        $name = $translator->trans(
            "decks.build.newname.noagenda",
            array(
                "%faction%" => $faction->getName(),
            )
        );
        $expansion = $em->getRepository('AppBundle:Expansion')->findOneBy(array("code" => "unl"));


        $deck = new Deck();
        $deck->setDescriptionMd("");
        $deck->setFaction($faction);
        $deck->setLastExpansion($expansion);
        $deck->setName($name);
        $deck->setProblem('too_few_cards');
        $deck->setTags(join(' ', array_unique($tags)));
        $deck->setUser($this->getUser());

        $em->persist($deck);
        $em->flush();

        return $this->redirect($this->get('router')->generate('deck_edit', ['deck_id' => $deck->getId()]));
    }

    public function importAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $factions = $this->getDoctrine()->getRepository('AppBundle:Faction')->findAll();

        return $this->render(
            'AppBundle:Builder:directimport.html.twig',
            array(
                'pagetitle' => "Import a deck",
                'factions' => array_map(
                    function (Faction $faction) {
                        return ['code' => $faction->getCode(), 'name' => $faction->getName()];
                    },
                    $factions
                ),
            ),
            $response
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function fileimportAction(Request $request)
    {
        $filetype = filter_var($request->get('type'), FILTER_SANITIZE_STRING);
        $uploadedFile = $request->files->get('upfile');
        if (!isset($uploadedFile)) {
            throw new BadRequestHttpException("No file");
        }

        $origname = $uploadedFile->getClientOriginalName();
        $origext = $uploadedFile->getClientOriginalExtension();
        $filename = $uploadedFile->getPathname();
        $name = str_replace(".$origext", '', $origname);

        if (function_exists("finfo_open")) {
            // return mime type ala mimetype extension
            $finfo = finfo_open(FILEINFO_MIME);

            // check to see if the mime-type starts with 'text'
            $is_text = substr(finfo_file($finfo, $filename), 0, 4) == 'text'
                || substr(finfo_file($finfo, $filename), 0, 15) == "application/xml";
            if (!$is_text) {
                throw new BadRequestHttpException("Unsupported file format");
            }
        }

        $service = $this->get('deck_import_service');
        if ($filetype == "octgn" || ($filetype == "auto" && $origext == "o8d")) {
            $data = $service->parseOctgnImport(file_get_contents($filename));
        } else {
            $data = $service->parseTextImport(file_get_contents($filename));
        }

        if (empty($data['faction'])) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                [
                    'error' => "Unable to recognize the Faction of the deck.",
                ]
            );
        }

        $this->get('deck_manager')->save(
            $this->getUser(),
            new Deck(),
            null,
            $name,
            $data['faction'],
            $data['description'],
            null,
            $data['content'],
            null
        );

        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('decks_list'));
    }

    public function downloadAction(Request $request, $deck_id)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $deck Deck */
        $deck = $em->getRepository('AppBundle:Deck')->find($deck_id);

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $format = $request->query->get('format', 'text');

        switch ($format) {
            case 'octgn':
                return $this->downloadInOctgnFormat($deck);
                break;
            case 'text_expansion':
                return $this->downloadInTextFormatSortedByExpansion($deck);
                break;
            case 'text':
            default:
                return $this->downloadInDefaultTextFormat($deck);
        }
    }

    protected function downloadInOctgnFormat(Deck $deck)
    {
        $content = $this->renderView(
            'AppBundle:Export:octgn.xml.twig',
            [
                "deck" => $deck->getTextExport(),
            ]
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/octgn');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.o8d'
            )
        );

        $response->setContent($content);

        return $response;
    }

    protected function downloadInDefaultTextFormat(Deck $deck)
    {
        $content = $this->renderView(
            'AppBundle:Export:default.txt.twig',
            [
                "deck" => $deck->getTextExport(),
            ]
        );
        $content = str_replace("\n", "\r\n", $content);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    protected function downloadInTextFormatSortedByExpansion(Deck $deck)
    {
        $content = $this->renderView(
            'AppBundle:Export:sortedbyexpansion.txt.twig',
            [
                "deck" => $deck->getExpansionOrderExport(),
            ]
        );
        $content = str_replace("\n", "\r\n", $content);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    public function cloneAction($deck_id)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $deck Deck */
        $deck = $em->getRepository('AppBundle:Deck')->find($deck_id);

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $content = [];
        foreach ($deck->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }

        return $this->forward(
            'AppBundle:Builder:save',
            array(
                'name' => $deck->getName().' (clone)',
                'faction_code' => $deck->getFaction()->getCode(),
                'content' => json_encode($content),
                'deck_id' => $deck->getParent() ? $deck->getParent()->getId() : null,
            )
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveAction(Request $request)
    {

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (count($user->getDecks()) > $user->getMaxNbDecks()) {
            return new Response(
                'You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.'
            );
        }

        $id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Deck $deck */
        $deck = null;
        $source_deck = null;
        if ($id) {
            $deck = $em->getRepository('AppBundle:Deck')->find($id);
            if (!$deck || $user->getId() != $deck->getUser()->getId()) {
                throw new UnauthorizedHttpException("You don't have access to this deck.");
            }
            $source_deck = $deck;
        }

        $faction_code = filter_var($request->get('faction_code'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        if (!$faction_code) {
            return new Response('Cannot import deck without faction');
        }
        $faction = $em->getRepository('AppBundle:Faction')->findOneBy(['code' => $faction_code]);
        if (!$faction) {
            return new Response('Cannot import deck with unknown faction '.$faction_code);
        }

        $cancel_edits = (boolean)filter_var($request->get('cancel_edits'), FILTER_SANITIZE_NUMBER_INT);
        if ($cancel_edits) {
            if ($deck) {
                $this->get('deck_manager')->revert($deck);
            }

            return $this->redirect($this->generateUrl('decks_list'));
        }

        $is_copy = (boolean)filter_var($request->get('copy'), FILTER_SANITIZE_NUMBER_INT);
        if ($is_copy || !$id) {
            $deck = new Deck();
        }

        $content = (array)json_decode($request->get('content'));
        if (!count($content)) {
            return new Response('Cannot import empty deck');
        }

        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
        $description = trim($request->get('description'));
        $tags = filter_var($request->get('tags'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        $this->get('deck_manager')->save(
            $this->getUser(),
            $deck,
            $decklist_id,
            $name,
            $faction,
            $description,
            $tags,
            $content,
            $source_deck ? $source_deck : null
        );
        $em->flush();

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $deck_id = filter_var($request->get('deck_id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Deck $deck */
        $deck = $em->getRepository('AppBundle:Deck')->find($deck_id);
        if (!$deck) {
            return $this->redirect($this->generateUrl('decks_list'));
        }
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw new UnauthorizedHttpException("You don't have access to this deck.");
        }

        foreach ($deck->getChildren() as $decklist) {
            /** @var Decklist $decklist */
            $decklist->setParent(null);
        }
        $em->remove($deck);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Deck deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteListAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $list_id = explode('-', $request->get('ids'));

        foreach ($list_id as $id) {
            /* @var $deck Deck */
            $deck = $em->getRepository('AppBundle:Deck')->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }

            foreach ($deck->getChildren() as $decklist) {
                $decklist->setParent(null);
            }
            $em->remove($deck);
        }
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Decks deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    public function editAction($deck_id)
    {
        /** @var Deck $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deck')->find($deck_id);

        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.',
                )
            );
        }

        return $this->render(
            'AppBundle:Builder:deckedit.html.twig',
            array(
                'pagetitle' => "Deckbuilder",
                'deck' => $deck,
            )
        );
    }

    /**
     * @param $deck_id
     * @return Response
     */
    public function viewAction($deck_id)
    {
        /** @var Deck $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository('AppBundle:Deck')->find($deck_id);

        if (!$deck) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => "This deck doesn't exist.",
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $tournaments = $this->getDoctrine()->getManager()->getRepository('AppBundle:Tournament')->findAll();

        return $this->render(
            'AppBundle:Builder:deckview.html.twig',
            array(
                'pagetitle' => "Deckbuilder",
                'deck' => $deck,
                'deck_id' => $deck_id,
                'is_owner' => $is_owner,
                'tournaments' => $tournaments,
            )
        );
    }

    /**
     * @param int $deck1_id
     * @param int $deck2_id
     * @return Response
     */
    public function compareAction($deck1_id, $deck2_id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        /* @var Deck $deck1 */
        $deck1 = $entityManager->getRepository('AppBundle:Deck')->find($deck1_id);

        /* @var Deck $deck2 */
        $deck2 = $entityManager->getRepository('AppBundle:Deck')->find($deck2_id);

        if (!$deck1 || !$deck2) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'This deck cannot be found.',
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck1->getUser()->getId();
        if (!$deck1->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck2->getUser()->getId();
        if (!$deck2->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'AppBundle:Default:error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $drawIntersection = $this->get('diff')->getSlotsDiff(
            [
                $deck1->getSlots()->getDrawDeck(),
                $deck2->getSlots()->getDrawDeck(),
            ]
        );

        return $this->render(
            'AppBundle:Compare:deck_compare.html.twig',
            [
                'deck1' => $deck1,
                'deck2' => $deck2,
                'draw_deck' => $drawIntersection,
            ]
        );
    }

    public function listAction()
    {
        /* @var User $user */
        $user = $this->getUser();

        $decks = $this->get('deck_manager')->getByUser($user);

        /* @todo refactor this out, use DQL not raw SQL [ST 2019/04/04] */
        $tournaments = $this->getDoctrine()->getConnection()->executeQuery(
            "SELECT t.id, t.description FROM tournament t ORDER BY t.description desc"
        )->fetchAll();

        if (count($decks)) {
            $tags = [];
            foreach ($decks as $deck) {
                /** @var Deck $deck */
                $tags[] = $deck->getTags();
            }
            $tags = array_unique($tags);

            return $this->render(
                'AppBundle:Builder:decks.html.twig',
                array(
                    'pagetitle' => $this->get("translator")->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'decks' => $decks,
                    'tags' => $tags,
                    'nbmax' => $user->getMaxNbDecks(),
                    'nbdecks' => count($decks),
                    'cannotcreate' => $user->getMaxNbDecks() <= count($decks),
                    'tournaments' => $tournaments,
                )
            );
        } else {
            return $this->render(
                'AppBundle:Builder:no-decks.html.twig',
                array(
                    'pagetitle' => $this->get("translator")->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'nbmax' => $user->getMaxNbDecks(),
                    'tournaments' => $tournaments,
                )
            );
        }
    }

    public function copyAction($decklist_id)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $decklist Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);

        $content = [];
        foreach ($decklist->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }

        return $this->forward(
            'AppBundle:Builder:save',
            array(
                'name' => $decklist->getName(),
                'faction_code' => $decklist->getFaction()->getCode(),
                'content' => json_encode($content),
                'decklist_id' => $decklist_id,
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function autosaveAction(Request $request)
    {
        $user = $this->getUser();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $deck_id = $request->get('deck_id');

        /** @var Deck $deck */
        $deck = $em->getRepository('AppBundle:Deck')->find($deck_id);
        if (!$deck) {
            throw new BadRequestHttpException("Cannot find deck ".$deck_id);
        }
        if ($user->getId() != $deck->getUser()->getId()) {
            throw new UnauthorizedHttpException("You don't have access to this deck.");
        }

        $diff = (array)json_decode($request->get('diff'));
        if (count($diff) != 2) {
            $this->get('logger')->error("cannot use diff", $diff);
            throw new BadRequestHttpException("Wrong content ".json_encode($diff));
        }

        if (count($diff[0]) || count($diff[1])) {
            $change = new Deckchange();
            $change->setDeck($deck);
            $change->setVariation(json_encode($diff));
            $change->setIsSaved(false);
            $em->persist($change);
            $em->flush();
        }

        return new Response($change->getDatecreation()->format('c'));
    }
}
