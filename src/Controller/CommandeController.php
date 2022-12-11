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
    #[Route('/show/{id}', name: 'app_commande')]
    public function index(Panier $panier = null): Response
    {
        // si le panier n'exste pas on redirige
        if (null === $panier) {
            $this->addFlash('warning', $this->translator->trans('commande.introuvable'));
            return $this->redirectToRoute('app_home');
        }


        return $this->render('commande/index.html.twig', [
            'panier' => $panier,
            'utilisateur' => $this->getUser(),
        ]);
    }
}
