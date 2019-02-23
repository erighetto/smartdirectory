<?php

namespace App\Repository;

use App\Entity\CncatCat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class CncatCatRepository
 * @package App\Repository
 */
class CncatCatRepository extends ServiceEntityRepository
{
    /**
     * CncatCatRepository constructor.
     * @param RegistryInterface $registry
     */
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

    /**
     * @param $id
     * @return array
     */
    public function findParents($id)
    {
        $tree = [];
        $this->buildTree($tree, $id);
        return array_reverse($tree);
    }

    /**
     * Populates a tree array given a item tree id.
     *
     * @param $tree
     * @param $id
     */
    protected function buildTree(&$tree, $id)
    {
        $object = $this->findOneBy(['cid' => $id]);

        if (!$object instanceof CncatCat) {
            return;
        }

        $tree[$object->getCid()] = $object;

        if ($object->getParent() == 0) {
            return;
        }

        $this->buildTree($tree, $object->getParent());
    }


}