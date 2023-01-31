<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contenu/panier')]
class ContenuPanierController extends AbstractController
{
    #[Route('/', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository): Response
    {
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findAll(),
        ]);
    }
    
    #[Route('/new/{id}', name: 'app_contenu_panier_new', methods: ['GET', 'POST'])]
    public function new(
        $id,
        Request $request, 
        ContenuPanierRepository $contenuPanierRepository,
        PanierRepository $panierRepository,
        ProduitRepository $produitRepository,
    ): Response{
        $produit = $produitRepository->findBy(['id'=> $id]);
        $contenuPanier = $contenuPanierRepository->findOneBy(['produit' => $produit]);

        // au moment de rajouter le produit dans le panier;
        // on regarde s'il n'y est pas deja present, et si c'est le cas alors on
        // augmente sa quantite
        if($contenuPanier) {
            $quantiteProduit = $contenuPanier->getQuantite();
            $contenuPanier->setQuantite($quantiteProduit +1);
            $contenuPanier->setDate(new \DateTime());
            $contenuPanierRepository->save($contenuPanier, true);
            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        } else{

            $contenuPanier = new ContenuPanier();
            $user = $this->getUser();
            // $quantite = $request->get('produit_quantite');
    
            // $quantite = (int) $request->request->get('produit_quantite');
            // dd($quantite);
            if(!empty($user)){
                $panier = $panierRepository->findOneBy(['utilisateur'=> $user]);
                // ajouter automatiquement la date
                // le produit a ajouter et la panier actuel de l'utilisateur
                // si l'utilisateur ajoute plus d'une fois le meme produit
                // alors on doit incrémenter la quantité de ce produits
                $contenuPanier->setQuantite(1);
                $contenuPanier->setProduit($produit[0]);
                $contenuPanier->setPanier($panier);
                $contenuPanier->setDate(new \DateTime());
        
                $contenuPanierRepository->save($contenuPanier, true);
            }
            
            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

    }

    #[Route('/{id}', name: 'app_contenu_panier_show', methods: ['GET'])]
    public function show(ContenuPanier $contenuPanier): Response
    {
        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenuPanierRepository->save($contenuPanier, true);

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contenu_panier_delete', methods: ['POST'])]
    public function delete(Request $request, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contenuPanier->getId(), $request->request->get('_token'))) {
            $contenuPanierRepository->remove($contenuPanier, true);
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
    }

}
