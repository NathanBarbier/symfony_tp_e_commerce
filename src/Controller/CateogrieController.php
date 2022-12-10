<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CateogrieController extends AbstractController
{
    #[Route('/cateogrie', name: 'app_cateogrie')]
    public function index(): Response
    {
        return $this->render('cateogrie/index.html.twig', [
            'controller_name' => 'CateogrieController',
        ]);
    }
}
