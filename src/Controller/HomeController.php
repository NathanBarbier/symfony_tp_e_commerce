<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class HomeController extends AbstractController
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

    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
    ): Response {

        $panier = new Panier();
        /** @var User $user */
        if (null !== $user = $this->getUser()) {
            /** @var Panier $panier */
            $panier = $user->getActivePanier();
        }

        // on récupère les produits
        $produits = $this->em->getRepository(Produit::class)->findAll();
        $categorie = $this->em->getRepository(Categorie::class)->findAll();

        // on vérifie le role si il est bien admin
        // on crée le formulaire de création d'un produit
        $form = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            $produit = new Produit();
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

                    $produit = new Produit();

                    $form = $this->createForm(ProduitType::class, $produit, [
                        'csrf_protection' => false, /** j'ai ajouté ça parce que sinon il me mettait une erreur au niveau du csrf */
                    ]);
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', $this->translator->trans('produit.update.canceled'));
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('home/index.html.twig', [
            'panier' => $panier,
            'form' => $form,
            'produits' => $produits,
            'utilisateur' => $user,
            'categorie' => $categorie,
        ]);
    }
}
