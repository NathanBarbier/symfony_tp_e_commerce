<?php

namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/panier')]
class PanierController extends AbstractController
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

    #[Route('/delete/{id}', name: 'app_panier')]
    public function index(Panier $panier): Response
    {
        /** redirection avec message si le panier n'existe pas */
        if (null === $panier) {
            $this->addFlash('warning', $this->translator->trans('panier.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        try {
            $this->em->remove($panier);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('panier.delete.confirm'));
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->translator->trans('panier.delete.canceled'));
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }
}
