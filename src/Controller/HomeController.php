<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class HomeController extends AbstractController
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

    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('/');
        }

        /** @var Panier $panier */
        $panier = $this->em->getRepository(Panier::class)->findActivePanier($this->getUser());

        $produits = $this->em->getRepository(Produit::class)->findAll();

        if ($this->isGranted('ROLE_ADMIN')) {
            $produit = new Produit();
            $form = $this->createForm(ProduitType::class, $produit);

            try {
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->em->persist($produit);
                    $this->em->flush();

                    $this->addFlash('success', $this->translator->trans('produit.update.confirm'));
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', $this->translator->trans('produit.update.canceled'));
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('home/index.html.twig', [
            'panier' => $panier,
            'produits' => $produits,
            'controller_name' => 'HomeController',
        ]);
    }
}
