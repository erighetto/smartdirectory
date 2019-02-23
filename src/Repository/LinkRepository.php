<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class LinkRepository
 * @package App\Repository
 */
class LinkRepository extends ServiceEntityRepository
{
    /**
     * LinkRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * @return Link|null
     * @throws \Doctrine\ORM\NonUniqueResultException
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
{

}