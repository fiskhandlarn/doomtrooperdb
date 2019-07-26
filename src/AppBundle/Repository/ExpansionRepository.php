<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ExpansionRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('y')
            ->select('y')
            ->orderBy('y.position', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByCode($code)
    {
        $qb = $this->createQueryBuilder('y')
            ->select('y')
            ->andWhere('y.code = ?1');

        $qb->setParameter(1, $code);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
