<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/{id}', name: 'app_categorie')]
    public function index(Categorie $categorie = null): Response
    {
        if (null === $categorie) {
            return $this->redirectToRoute('app_home');
        }

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
