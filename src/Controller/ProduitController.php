<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\ContenuPanier;
use App\Form\ProduitType;
use App\Form\ContenuPanierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/produit')]
class ProduitController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected TranslatorInterface $translator;
    protected SluggerInterface $slugger;

    public function __construct (
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        SluggerInterface $slugger,
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->slugger = $slugger;
    }

    /**
     * Page d'affichage d'un produit
     * elle permet également l'update du produit si l'utilisateur est ADMIN.
     */
    #[Route('/show/{id}', name: 'app_produit')]
    public function index(Request $request, Produit $produit = null): Response
    {
        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        $form = "";
        $panier = new Panier();

        /** Si l'utilisateur est connecté on récupère son panier */
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();
        }

        /** si le user est admin on crèe le form de modification. */
        if ($this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(ProduitType::class, $produit, [
                'csrf_protection' => false, /** j'ai ajouté ça parce que sinon il me mettait une erreur au niveau du csrf */
            ]);

            try {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $imageFile = $form->get('photo')->getData();

                    // this condition is needed because the 'image' field is not required
                    // so the PDF file must be processed only when a file is uploaded
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename = $this->slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                        // Move the file to the directory where images are stored
                        try {
                            $imageFile->move(
                                $this->getParameter('upload_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                            $this->addFlash('danger', "Impossible d'uploader le fichier");
                            return $this->redirectToRoute('app_marque');
                        }

                        // updates the 'imageFilename' property to store the PDF file name
                        // instead of its contents
                        $produit->setPhoto($newFilename);
                    }

                    $this->em->persist($produit);
                    $this->em->flush();

                    $this->addFlash('success', $this->translator->trans('produit.update.confirm'));
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', $this->translator->trans('produit.update.canceled'));
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('produit/index.html.twig', [
            'panier' => $panier,
            'produit' => $produit,
            'form' => $form,
            'utilisateur' => $user
        ]);
    }

    /**
     * On supprime le produit.
     */
    #[Route('/delete/{id}', name: 'delete_produit')]
    public function delete(Produit $produit = null)
    {

        /** redirection si le user n'est pas Admin */
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('app_home');
        }

        /** redirection avec message si le produit n'existe pas */
        if (null === $produit) {
            $this->addFlash('danger', $this->translator->trans('produit.introuvable'));
            return $this->redirectToRoute('app_home');
        }

        try {
            $this->em->remove($produit);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $this->addFlash('success', $this->translator->trans('produit.supprimé'));

        return $this->redirectToRoute('app_home');
    }
}
