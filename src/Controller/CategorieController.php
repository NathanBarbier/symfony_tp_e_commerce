<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\Produit as EntityProduit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/{id}', name: 'app_categorie')]
    public function index(Categorie $categorie,): Response
    {
        $panier = new Panier();

        /** Si l'utilisateur est connecté on récupère son panier */
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();
        }

        return $this->render('categorie/categorie.html.twig', [
            'panier' => $panier,
            'categorie' => $categorie,
            'utilisateur' => $user,
        ]);
    }
}
