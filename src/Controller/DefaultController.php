<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAllOrdered();

        return $this->render('default/index.html.twig', [
            'title' => 'Home',
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/error", name="error")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function error()
    {
        return $this->render('default/error.html.twig',
        [
            'title' => 'Error'
        ]);
    }
}
