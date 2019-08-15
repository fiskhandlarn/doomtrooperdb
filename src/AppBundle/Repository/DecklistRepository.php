<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Decklist;
use Doctrine\ORM\EntityRepository;

class DecklistRepository extends EntityRepository
{
    public function findDuplicate(Decklist $decklist)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->andWhere('d.signature = ?1');

        $qb->setParameter(1, $decklist->getSignature());
        $qb->orderBy('d.dateCreation', 'ASC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    //findBy([ 'parent' => $decklist->getParent() ], [ 'version' => 'DESC' ]);
    public function findVersions(Decklist $decklist)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d, ds, c')
            ->join('d.slots', 'ds')
            ->join('ds.card', 'c')
            ->andWhere('d.parent = ?1');

        $qb->setParameter(1, $decklist->getParent());
        $qb->orderBy('d.version', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
