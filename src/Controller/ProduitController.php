<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class ProduitController extends AbstractController
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

    #[Route('/produit/{id}', name: 'app_produit')]
    public function index(Produit $produit = null): Response
    {
        $form = "";

        /** @var Panier $panier */
        $panier = $this->em->getRepository(Panier::class)->findActivePanier($this->getUser());

        if ($this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(ProduitType::class, $produit);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->persist($produit);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('produit.update.confirm'));
            }
        }

        return $this->render('produit/index.html.twig', [
            'panier' => $panier,
            'produit' => $produit,
            'form' => $form,
        ]);
    }
}
