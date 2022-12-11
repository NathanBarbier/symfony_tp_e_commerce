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

    #[Route('/create/{id}', name: 'create_contenu_panier')]
    public function create(Produit $produit = null): Response
    {
        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        /**
         * on vérifie si l'utilisateur est connecté
         * si le produit a stock a 0 on flash une erreur
         * on regarde si il a un panier en cours, si non on en crée un si oui on le met à jour
         * si le contenuPanier est déjà lié a un produit dans le panier on le met à jour
         * sinon on crèe un nouveau contenuPanier
         */
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            if ($produit->getStock() === 0) {
                $this->addFlash('danger', $this->translator->trans('produit.out_of_stock'));
                return $this->redirectToRoute('app_home');
            }

            /** @var Panier $panier */
            $panier = $user->getActivePanier();

            if ($panier === null)  {
                $panier = $this->createPanier($user);
            }

            $contenuPanier = $this->em->getRepository(ContenuPanier::class)->findOneBy([
                "Panier" => $panier,
                "produit" => $produit
            ]);

            if (null !== $contenuPanier) {
                $contenuPanier->setQuantite($contenuPanier->getQuantite() + 1);
                $contenuPanier->setDate(new \DateTimeImmutable());
                $contenuPanier->setProduit($produit);
            } else {
                $contenuPanier = new ContenuPanier();
                $contenuPanier->setQuantite(1);
                $contenuPanier->setDate(new \DateTimeImmutable());
                $contenuPanier->setPanier($panier);
                $contenuPanier->setProduit($produit);
            }

            $produit->setStock($produit->getStock() - 1);

            $this->em->persist($contenuPanier);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_home');
    }

    protected function createPanier($user): Panier {
        $panier = new Panier();
        $panier->setEtat(false);
        $panier->setUtilisateur($user);

        $this->em->persist($panier);
        return $panier;
    }
}
