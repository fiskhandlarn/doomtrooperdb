<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Card;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, t, f, y')
            ->join('c.type', 't')
            ->join('c.faction', 'f')
            ->join('p.expansion', 'y')
            ->orderBy('c.code', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByType($type)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.type', 't')
            ->andWhere('t.code = ?1')
            ->orderBy('c.code', 'ASC');

        $qb->setParameter(1, $type);

        return $qb->getQuery()->getResult();
    }

    public function findByCode($code)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->andWhere('c.code = ?1');

        $qb->setParameter(1, $code);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByCodes($codes)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, t, f, y')
            ->join('c.type', 't')
            ->join('c.faction', 'f')
            ->join('p.expansion', 'y')
            ->andWhere('c.code in (?1)')
            ->orderBy('c.code', 'ASC');

        $qb->setParameter(1, $codes);

        return $qb->getQuery()->getResult();
    }

    public function findByRelativePosition(Card $card, int $position)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.expansion', 'y')
            ->andWhere('y.code = ?1')
            ->andWhere('c.position = ?2');

        $qb->setParameter(1, $card->getExpansion()->getCode());
        $qb->setParameter(2, $card->getPosition()+$position);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findPreviousCard($card)
    {
        return $this->findByRelativePosition($card, -1);
    }

    public function findNextCard($card)
    {
        return $this->findByRelativePosition($card, 1);
    }

    public function findTraits()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.traits')
            ->andWhere("c.traits != ''");
        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieves all agendas eligible for deck building.
     * @param array $excludedAgendas a list of codes of agendas to exclude.
     * @return array
     */
    public function getAgendasForNewDeckWizard($excludedAgendas = array()): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, y')
            ->join('c.expansion', 'y')
            ->join('c.type', 't')
            ->where('t.code = :type')
            ->orderBy('c.name', 'ASC');

        $qb->setParameter(':type', 'agenda');

        if (! empty($excludedAgendas)) {
            $qb->andWhere($qb->expr()->notIn('c.code', ':codes'));
            $qb->setParameter(':codes', $excludedAgendas, Connection::PARAM_STR_ARRAY);
        }

        return $qb->getQuery()->getResult();
    }
}
