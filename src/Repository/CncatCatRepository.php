<?php

namespace App\Repository;

use App\Entity\CncatCat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CncatCatRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CncatCat::class);
    }

    /**
     * @return CncatCat[]
     */
    public function findAllOrdered(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.parent = 0')
            ->orderBy('c.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

}