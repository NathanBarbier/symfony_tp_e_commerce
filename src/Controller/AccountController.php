<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/account')]
class AccountController extends AbstractController
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

    #[Route('/show/{id}', name: 'app_account')]
    public function index(User $user = null): Response
    {
        /** redirection avec message si le user n'existe pas */
        if (null === $user) {
            $this->addFlash('warning', $this->translator->trans('user.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(UserType::class, $user);

        return $this->render('account/index.html.twig', [
            'form' => $form,
            'utilisateur' => $this->getUser(),
            'paniers' => array_reverse($this->getUser()->getPaniers()->toArray())
        ]);
    }
}
