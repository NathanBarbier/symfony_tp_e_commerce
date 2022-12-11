<?php

namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/commande')]
class CommandeController extends AbstractController
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

    // On passe la commande/panier en "acheté"
    #[Route('/paiement/{id}', name: 'app_paiement')]
    public function paiement(Panier $panier = null)
    {   
        if (null === $panier) {
            $this->addFlash('warning', $this->translator->trans('commande.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        foreach ($panier->getContenuPaniers() as $contenuPanier) {
            $produit = $contenuPanier->getProduit();

            if ($produit->getStock() < $contenuPanier->getQuantite()) {
                $this->addFlash('warning', $this->translator->trans('produit.panier.out_of_stock', ['%produit%' => $produit->getNom()]));

                return $this->redirectToRoute('app_home');
            }

            $produit->setStock($produit->getStock() - $contenuPanier->getQuantite());
        }

        $panier->setDateAchat(new \DateTime());
        $panier->setEtat(1);
        $this->em->persist($panier);
        $this->em->flush();


        $this->addFlash('success', $this->translator->trans('commande.success'));
        return $this->redirectToRoute('app_home');
    }
}
