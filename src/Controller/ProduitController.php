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

#[Route('{_locale}/produit')]
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

    /**
     * Page d'affichage d'un produit
     */
    #[Route('/show/{id}', name: 'app_produit')]
    public function index(Produit $produit = null): Response
    {
        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        $form = "";

        /** @var Panier $panier */
        $panier = $this->em->getRepository(Panier::class)->findActivePanier($this->getUser());

        /** si le user est admin on crèe le form de modification. */
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

    /**
     * On supprime le produit.
     */
    #[Route('/delete/{id}', name: 'app_produit')]
    public function delete(Produit $produit = null) {

        /** redirection si le user n'est pas Admin */
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('app_home');
        }

        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        $this->em->remove($produit);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('produit.supprimé'));

        return $this->redirectToRoute('app_home');
    }
}
