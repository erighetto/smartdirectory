<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Link;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SmartDirectoryController
 * @package App\Controller
 */
class SmartDirectoryController extends AbstractController
{
    /**
     * @Route("/{lev1}/{lev2}/{lev3}", name="smart_directory")
     *
     * @param $lev1
     * @param $lev2
     * @param $lev3
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($lev1, $lev2 = null, $lev3 = null)
    {
        if (!empty($lev1)) {
            $id = $this->getId($lev1);
        }

        if (!empty($lev2)) {
            $id = $this->getId($lev2);
        }

        if (!empty($lev3)) {
            $id = $this->getId($lev3);
        }

        $links = $this->getDoctrine()
            ->getRepository(Link::class)
            ->findAnybyCatId($id);

        /** @var \App\Repository\CategoryRepository $cncat */
        $cncat = $this->getDoctrine()
            ->getRepository(Category::class);

        $children = $cncat->findBy(['parent' => $id]);
        $current = $cncat->findOneBy(['cid' => $id]);

        if ($current instanceof Category) {
            $parents = $cncat->findParents($current->getParent());
            $title = $current->getName();
        } else {
            $parents = null;
            $title = null;
        }

        return $this->render('smart_directory/index.html.twig', [
            'title' => $title,
            'links' => $links,
            'parents' => $parents,
            'current' => $current,
            'children' => $children,
        ]);
    }

    /**
     * @param $string
     * @return mixed
     */
    private function getId($string)
    {
        $chunks = explode("-", $string);
        $id = $chunks[count($chunks) - 1];
        return $id;
    }
}
