<?php

namespace AppBundle\Repository;

/**
 * WorkshopRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WorkshopRepository extends \Doctrine\ORM\EntityRepository
{
    public function calendar()
    {
        return $this->createQueryBuilder('w')
            ->getQuery()
            ->getResult()
        ;
    }
}
