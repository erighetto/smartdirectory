<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CategoryRepository
 * @package App\Repository
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[]
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
    private function buildTree(&$tree, $id)
    {
        $object = $this->findOneBy(['cid' => $id]);

        if (!$object instanceof Category) {
            return;
        }

        $tree[$object->getCid()] = $object;

        if ($object->getParent() == 0) {
            return;
        }

        $this->buildTree($tree, $object->getParent());
    }

}