<?php

namespace AppBundle\Services;

use AppBundle\Controller\SearchController;
use AppBundle\Entity\Card;
use AppBundle\Entity\Expansion;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/*
 *
 */

class CardsData
{
    /** @var \Symfony\Component\Asset\Packages $packages */
    private $packages;

    public function __construct(
        Registry $doctrine,
        RequestStack $request_stack,
        Router $router,
        \Symfony\Component\Asset\Packages $packages,
        TranslatorInterface $translator,
        $rootDir
    ) {
        $this->doctrine = $doctrine;
        $this->request_stack = $request_stack;
        $this->router = $router;
        $this->packages = $packages;
        $this->translator = $translator;
        $this->rootDir = $rootDir;
    }

    public function splitInParagraphs($text)
    {
        if (empty($text)) {
            return '';
        }
        return implode(array_map(function ($l) {
            return "<p>$l</p>";
        }, preg_split('/[\r\n]+/', $text)));
    }

    public function allsetsdata()
    {
        /** @var Expansion[] $list_expansions */
        $list_expansions = $this->doctrine->getRepository('AppBundle:Expansion')->findAll();
        $lines = [];

        foreach ($list_expansions as $expansion) {
            if ($expansion->getSize() === 1) {
                $label = $expansion->getName();
            } else {
                $label = $expansion->getPosition() . '. ' . $expansion->getName();
            }
            $lines[] = array(
                "code" => $expansion->getCode(),
                "label" => $label,
                "available" => true,
                "url" => $this->router->generate(
                    'cards_list',
                    array('expansion_code' => $expansion->getCode()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            );
        }
        return $lines;
    }

    public function allsetsdatathreaded()
    {
        $list_expansions = $this->doctrine->getRepository('AppBundle:Expansion')->findBy(
            [],
            array("position" => "ASC")
        );
        $expansions = [];

        /* @var $expansion \AppBundle\Entity\Expansion */
        foreach ($list_expansions as $expansion) {
            $label = $expansion->getName();

            $expansions[] = [
                "code" => $expansion->getCode(),
                "label" => $label,
                "available" => true,
                "url" => $this->router->generate(
                    'cards_list',
                    array('expansion_code' => $expansion->getCode()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ];
        }

        return $expansions;
    }

    public function getSearchRows($conditions, $sortorder, $forceempty = false)
    {
        $i = 0;

        // construction de la requete sql
        $repo = $this->doctrine->getRepository('AppBundle:Card');
        $qb = $repo->createQueryBuilder('c')
            ->select('c', 'y', 't', 'f')
            ->leftJoin('c.expansion', 'y')
            ->leftJoin('c.type', 't')
            ->leftJoin('c.faction', 'f');
        $qb2 = null;
        $qb3 = null;

        foreach ($conditions as $condition) {
            $searchCode = array_shift($condition);
            $searchName = SearchController::$searchKeys[$searchCode];
            $searchType = SearchController::$searchTypes[$searchCode];
            $operator = array_shift($condition);

            switch ($searchType) {
                case 'code':
                    switch ($searchCode) {
                        case 'e':
                            $or = [];
                            foreach ($condition as $arg) {
                                switch ($operator) {
                                    case ':':
                                        $or[] = "(y.code = ?$i)";
                                        break;
                                    case '!':
                                        $or[] = "(y.code != ?$i)";
                                        break;
                                    case '<':
                                        if (!isset($qb2)) {
                                            $qb2 = $this->doctrine
                                                ->getRepository('AppBundle:Expansion')
                                                ->createQueryBuilder('y2');
                                            $or[] = $qb->expr()->lt(
                                                'y.dateRelease',
                                                '('
                                                . $qb2->select('y2.dateRelease')
                                                ->where("y2.code = ?$i")
                                                ->getDql()
                                                . ')'
                                            );
                                        }
                                        break;
                                    case '>':
                                        if (!isset($qb3)) {
                                            $qb3 = $this->doctrine
                                                ->getRepository('AppBundle:Expansion')
                                                ->createQueryBuilder('y3');
                                            $or[] = $qb->expr()->gt(
                                                'y.dateRelease',
                                                '('
                                                . $qb3->select('y3.dateRelease')
                                                ->where("y3.code = ?$i")
                                                ->getDql()
                                                . ')'
                                            );
                                        }
                                        break;
                                }
                                $qb->setParameter($i++, $arg);
                            }
                            $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                            break;
                        default: // type and faction
                            $or = [];
                            foreach ($condition as $arg) {
                                switch ($operator) {
                                    case ':':
                                        $or[] = "($searchCode.code = ?$i)";
                                        break;
                                    case '!':
                                        $or[] = "($searchCode.code != ?$i)";
                                        break;
                                }
                                $qb->setParameter($i++, $arg);
                            }
                            $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                            break;
                    }
                    break;
                case 'string':
                    if ($searchCode === '') { // name or index
                        $or = [];
                        foreach ($condition as $arg) {
                            $code = preg_match('/^\d\d\d\d\d$/u', $arg);
                            $acronym = preg_match('/^[A-Z]{2,}$/', $arg);
                            if ($code) {
                                $or[] = "(c.code = ?$i)";
                                $qb->setParameter($i++, $arg);
                            } elseif ($acronym) {
                                $like = implode('% ', str_split($arg));
                                $or[] = "(REPLACE(c.name, '-', ' ') like ?$i)";
                                $qb->setParameter($i++, "$like%");
                            } else {
                                $or[] = "(c.name like ?$i)";
                                $qb->setParameter($i++, "%$arg%");
                            }
                        }
                        $qb->andWhere(implode(" or ", $or));
                    } else if ($searchCode === 'x' ||
                               $searchCode === 'l' ||
                               $searchCode === 'i') {
                        if ($searchCode === 'x') { // text
                            $property = 'text';
                        }

                        if ($searchCode === 'l') { // flavor
                            $property = 'flavor';
                        }

                        if ($searchCode === 'i') { // illustrator
                            $property = 'illustrator';
                        }

                        $or = [];
                        foreach ($condition as $arg) {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.$property like ?$i)";
                                    break;
                                case '!':
                                    $or[] = "(c.$property is null or c.$property not like ?$i)";
                                    break;
                            }
                            $qb->setParameter($i++, "%$arg%");
                        }
                        $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                    } else if ($searchCode === 's' ||
                               $searchCode === 'g' ||
                               $searchCode === 'a' ||
                               $searchCode === 'v') {
                        if ($searchCode === 's') { // shoot
                            $property = 'shoot';
                        }

                        if ($searchCode === 'g') { // fight
                            $property = 'fight';
                        }

                        if ($searchCode === 'a') { // armor
                            $property = 'armor';
                        }

                        if ($searchCode === 'v') { // value
                            $property = 'value';
                        }

                        $or = [];
                        foreach ($condition as $arg) {
                            switch ($operator) {
                                case ':':
                                    $or[] = "(c.$property = ?$i)";
                                    break;
                                case '!':
                                    $or[] = "(c.$property != ?$i)";
                                    break;
                            }
                            $qb->setParameter($i++, $arg);
                        }
                        $qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
                    }
                    break;
            }
        }

        if (!$i && !$forceempty) {
            return;
        }
        switch ($sortorder) {
            case 'set':
                $qb->orderBy('y.position');
                break;
            case 'faction':
                $qb->orderBy('c.faction')->addOrderBy('c.type');
                break;
            case 'type':
                $qb->orderBy('c.type')->addOrderBy('c.faction');
                break;
        }
        $qb->addOrderBy('c.name');
        $qb->addOrderBy('c.code');
        $rows = $qb->getQuery()->getResult();

        return $rows;
    }

    /**
     * @param Card        $card
     * @param bool        $api
     * @param string|null $version
     * @return array
     */
    public function getCardInfo(Card $card, bool $api, string $version = null)
    {
        $cardinfo = [];

        $metadata = $this->doctrine->getManager()->getClassMetadata('AppBundle:Card');
        $fieldNames = $metadata->getFieldNames();
        $associationMappings = $metadata->getAssociationMappings();

        foreach ($associationMappings as $fieldName => $associationMapping) {
            if ($associationMapping['isOwningSide']) {
                $getter = str_replace(
                    ' ',
                    '',
                    ucwords(str_replace('_', ' ', "get_$fieldName"))
                );
                $associationEntity = $card->$getter();
                if (!$associationEntity) {
                    continue;
                }

                $cardinfo[$fieldName . '_code'] = $associationEntity->getCode();
                $cardinfo[$fieldName . '_name'] = $associationEntity->getName();
            }
        }

        foreach ($fieldNames as $fieldName) {
            $getter = str_replace(
                ' ',
                '',
                ucwords(str_replace('_', ' ', "get_$fieldName"))
            );
            $value = $card->$getter();
            switch ($metadata->getTypeOfField($fieldName)) {
                case 'datetime':
                case 'date':
                    continue 2;
                    break;
                case 'boolean':
                    $value = (boolean) $value;
                    break;
            }
            $fieldName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $fieldName)), '_');
            $cardinfo[$fieldName] = $value;
        }

        $cardinfo['url'] = $this->router->generate(
            'cards_zoom',
            array('card_code' => $card->getCode()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cardinfo['label'] = $card->getName();

        if ($api) {
            unset($cardinfo['id']);
        } else {
            $cardinfo['text'] = $this->splitInParagraphs($cardinfo['text']);
        }

        return $cardinfo;
    }

    public function syntax($query)
    {
        // renvoie une liste de conditions (array)
        // chaque condition est un tableau à n>1 éléments
        // le premier est le type de condition (0 ou 1 caractère)
        // les suivants sont les arguments, en OR

        $query = preg_replace('/\s+/u', ' ', trim($query));

        $list = [];
        $cond = null;
        // l'automate a 3 états :
        // 1:recherche de type
        // 2:recherche d'argument principal
        // 3:recherche d'argument supplémentaire
        // 4:erreur de parsing, on recherche la prochaine condition
        // s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
        $etat = 1;
        while ($query != "") {
            if ($etat == 1) {
                if (isset($cond) && $etat != 4 && count($cond) > 2) {
                    $list[] = $cond;
                }
                // on commence par rechercher un type de condition
                $match = [];
                if (preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
                    $cond = array(mb_strtolower($match[1]), $match[2]);
                    $query = $match[3];
                } else {
                    $cond = array("", ":");
                }
                $etat = 2;
            } else {
                if (preg_match('/^"([^"]*)"(.*)/u', $query, $match)
                    // jeton "texte libre entre guillements"
                    || preg_match('/^([\p{L}\p{N}\-\&]+)(.*)/u', $query, $match)
                    // jeton "texte autorisé sans guillements"
                ) {
                    if (($etat == 2 && count($cond) == 2) || $etat == 3) {
                        $cond[] = $match[1];
                        $query = $match[2];
                        $etat = 2;
                    } else {
                        // erreur
                        $query = $match[2];
                        $etat = 4;
                    }
                } elseif (preg_match('/^\|(.*)/u', $query, $match)) { // jeton "|"
                    if (($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && count($cond) > 2) || $etat == 3)) {
                        $query = $match[1];
                        $etat = 3;
                    } else {
                        // erreur
                        $query = $match[1];
                        $etat = 4;
                    }
                } elseif (preg_match('/^ (.*)/u', $query, $match)) { // jeton " "
                    $query = $match[1];
                    $etat = 1;
                } else {
                    // erreur
                    $query = substr($query, 1);
                    $etat = 4;
                }
            }
        }
        if (isset($cond) && $etat != 4 && count($cond) > 2) {
            $list[] = $cond;
        }
        return $list;
    }

    public function validateConditions($conditions)
    {
        // suppression des conditions invalides
        $numeric = array('<', '>');

        foreach ($conditions as $i => $l) {
            $searchCode = $l[0];
            $searchOp = $l[1];

            if (in_array($searchOp, $numeric) && SearchController::$searchTypes[$searchCode] !== 'integer') {
                // operator is numeric but searched property is not
                unset($conditions[$i]);
            }
        }

        return array_values($conditions);
    }

    public function buildQueryFromConditions($conditions)
    {
        // reconstruction de la bonne chaine de recherche pour affichage
        return implode(" ", array_map(
            function ($l) {
                return ($l[0] ? $l[0] . $l[1] : "")
                    . implode("|", array_map(
                        function ($s) {
                            return preg_match("/^[\p{L}\p{N}\-\&]+$/u", $s) ? $s : "\"$s\"";
                        },
                        array_slice($l, 2)
                    ));
            },
            $conditions
        ));
    }

    public function getReviews($card)
    {
        $reviews = $this->doctrine->getRepository('AppBundle:Review')->findBy(
            array('card' => $card),
            array('nbVotes' => 'DESC')
        );

        $response = $reviews;

        return $response;
    }
}
