<?php

namespace App\Provider;

use App\Entity\CncatCat;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Fw\LastBundle\Router\RouteProvider;
use Symfony\Component\HttpFoundation\Request;

class SmartDirectoryRouteProvider implements RouteProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var SlugifyInterface
     */
    protected $slugifyManager;

    /**
     * SmartDirectoryRouteProvider constructor.
     * @param ManagerRegistry $registry
     * @param SlugifyInterface $slugify
     */
    public function __construct(ManagerRegistry $registry, SlugifyInterface $slugify)
    {
        $this->slugifyManager = $slugify;
        $this->doctrine = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        $urls = [];
        $urls[] = Request::create('/');

        /** @var \App\Repository\CncatCatRepository $cncat */
        $cncat = $this->doctrine->getRepository(CncatCat::class);

        /** @var \App\Entity\CncatCat[] $parents */
        $parents = $cncat->findBy(array('parent' => 0), ['name' => 'asc']);

        foreach ($parents as $parent) {
            $urls[] = Request::create('/' . $this->getSlug($parent->getName(), $parent->getCid()));
            /** @var \App\Entity\CncatCat[] $children */
            $children = $cncat->findBy(array('parent' => $parent->getCid()), ['name' => 'asc']);
            foreach ($children as $child) {
                $urls[] = Request::create('/' . $this->getSlug($parent->getName(), $parent->getCid()) . '/' .
                    $this->getSlug($child->getName(), $child->getCid()));
                /** @var \App\Entity\CncatCat[] $grandchildren */
                $grandchildren = $cncat->findBy(array('parent' => $child->getCid()), ['name' => 'asc']);
                foreach ($grandchildren as $grandchild) {
                    $urls[] = Request::create('/' . $this->getSlug($parent->getName(), $parent->getCid()) . '/' .
                        $this->getSlug($child->getName(), $child->getCid()) .'/'.
                        $this->getSlug($grandchild->getName(), $grandchild->getCid())
                    );
                }
            }
        }

        return $urls;
    }

    /**
     * @param $name
     * @param $id
     * @return string
     */
    protected function getSlug($name, $id)
    {
        return $this->slugifyManager->slugify($name . ' ' . $id);
    }
}