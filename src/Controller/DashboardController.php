<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/dashboard')]
class DashboardController extends AbstractController
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
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        $panier = null;
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();
        }

        // on vérifie l'habilitation du user
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('success', $this->translator->trans('not_allowed'));
            return $this->redirectToRoute('app_home');
        }

        // on récupère tous les user, le array_reverse plus loin pour affiché du plus récent au moins récent
        $utilisateurs = $this->em->getRepository(User::class)->findAll();


        // on récupère les paniers en cours
        $paniers = $this->em->getRepository(Panier::class)->findBy([
            'etat' => 0,
        ]);

        return $this->render('dashboard/index.html.twig', [
            'paniers' => $paniers,
            'utilisateur' => $user,
            'utilisateurs' => array_reverse($utilisateurs),
            'panier' => $panier
        ]);
    }
}
