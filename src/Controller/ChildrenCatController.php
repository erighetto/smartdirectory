<?php

namespace App\Controller;

use App\Entity\CncatCat;
use App\Entity\CncatMain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ChildrenCatController extends AbstractController
{
    /**
     * @Route("/{lev1}/{lev2}/{lev3}", name="children_cat", defaults={"lev2"=null, "lev3"=null})
     * @param $lev1
     * @param $lev2
     * @param $lev3
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index($lev1, $lev2, $lev3)
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

        $children = $cncat->findBy(array('parent' => $id));
        $actual = $cncat->findOneBy(array('cid' => $id));

        if ($actual instanceof CncatCat) {
            $parent = $cncat->findOneBy(array('cid' => $actual->getParent()));
        } else {
            $parent = null;
        }


        return $this->render('children_cat/index.html.twig', [
            'title' => $actual->getName(),
            'links' => $links,
            'children' => $children,
            'parent' => $parent,
            'actual' => $actual,
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
