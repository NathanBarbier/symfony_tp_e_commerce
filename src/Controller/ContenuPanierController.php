<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/contenuPanier')]
class ContenuPanierController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected TranslatorInterface $translator;

    public function __construct (
        EntityManagerInterface $em,
        TranslatorInterface $translator,
    ) {
        $this->em = $em;
        $this->translator = $translator;
    }

    #[Route('/create/{produit}', name: 'create_contenu_panier')]
    public function create(Produit $produit): Response
    {
        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        /**
         * on vérifie si l'utilisateur est connecté
         *  on regarde si il a un panier en cours, si non on en crée un si oui on le met à jour
         * si il est connecté on lui permet la création d'un contenuPanier
         * si le produit est déjà lié a un contenuPanier par rapport a un panier déjà existant on le met à jour
         * sinon on crèe un nouveau contenuPanier
         */
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();

            if ($panier === null)  {
                $panier = $this->createPanier($user);
            }

            $contenuPanier = $this->em->getRepository(ContenuPanier::class)->findOneBy([
                "panier" => $panier,
                "produit" => $produit
            ]);

            if (null !== $contenuPanier) {
                $contenuPanier->setQuantite($contenuPanier->getQuantite() + 1);
                $contenuPanier->setDate(new \DateTimeImmutable());
            }

            $contenuPanier = new ContenuPanier();
            $contenuPanier->setQuantite(1);
            $contenuPanier->setDate(new \DateTimeImmutable());
            $contenuPanier->setPanier($panier);
        }

        return $this->render('contenu_panier/index.html.twig', [
            'controller_name' => 'ContenuPanierController',
        ]);
    }

    protected function createPanier($user): Panier {
        $panier = new Panier();
        $panier->setEtat(false);
        $panier->setUtilisateur($user);

        $this->em->persist($panier);
        return $panier;
    }
}
