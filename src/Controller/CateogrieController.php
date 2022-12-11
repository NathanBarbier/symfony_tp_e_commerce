<?php

namespace App\Controller;

use App\Entity\Produit as EntityProduit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}/categorie')]
class CateogrieController extends AbstractController
{
    #[Route('/', name: 'app_cateogrie')]
    public function index(EntityProduit $produit = null): Response
    {
        /** Si l'utilisateur est connecté on récupère son panier */
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();
        }

        return $this->render('cateogrie/index.html.twig', [
            'panier' => $panier,
            'produit' => $produit,
        ]);
    }
}
