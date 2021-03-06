<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class LinkRepository
 * @package App\Repository
 */
class LinkRepository extends ServiceEntityRepository
{
    /**
     * LinkRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * @param $cat_id
     * @return Link|null
     */
    public function findAnybyCatId($cat_id)
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.cat1 = :cat_id')
            ->setParameter('cat_id', $cat_id)
            ->orderBy('c.moderVote', 'DESC')
            ->getQuery();

        return $qb->execute();
    }

}