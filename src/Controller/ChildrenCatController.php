<?php

namespace App\Controller;

use App\Entity\CncatCat;
use App\Entity\CncatMain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ChildrenCatController
 * @package App\Controller
 */
class ChildrenCatController extends AbstractController
{
    /**
     * @Route("/{lev1}/{lev2}/{lev3}", name="children_cat")
     * @param $lev1
     * @param $lev2
     * @param $lev3
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index($lev1, $lev2  = null, $lev3 = null)
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
            ->getRepository(CncatMain::class)
            ->findAnybyCatId($id);

        /** @var \App\Repository\CncatCatRepository $cncat */
        $cncat = $this->getDoctrine()
            ->getRepository(CncatCat::class);

        $children = $cncat->findBy(['parent' => $id]);
        $current = $cncat->findOneBy(['cid' => $id]);

        if ($current instanceof CncatCat) {
            $parents = $cncat->findParents($current->getParent());
        } else {
            $parents = null;
        }

        return $this->render('children_cat/index.html.twig', [
            'title' => $current->getName(),
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
