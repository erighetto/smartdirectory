<?php

namespace App\Controller;

use App\Entity\CncatCat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $categories = $this->getDoctrine()
            ->getRepository(CncatCat::class)
            ->findAllOrdered();

        return $this->render('home/index.html.twig', [
            'title' => 'Home',
            'categories' => $categories
        ]);
    }
}
