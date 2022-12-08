<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}')]
class HomeController extends AbstractController
{
    protected EntityManagerInterface $em;

    public function __construct (
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }

    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('/');
        }
        $produits = $this->em->getRepository(Produit::class)->findAll();

        return $this->render('home/index.html.twig', [
            'produits' => $produits,
            'controller_name' => 'HomeController',
        ]);
    }
}
