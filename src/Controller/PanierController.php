<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $user = $this->getUser();
        $total = 0;

        
        // $newPanier = new Panier;
        // $contenuPaniers = $newPanier->getContenuPaniers();

        if(!empty($user)){
            $userId = $user->getId();
            $panier = $panierRepository->findPanier($userId);
            // sert a calculer le total
            $contenuPaniers = $contenuPanierRepository->findBy(['panier'=>$panier[0]]);
            // query qui récupere le contenu panier join avec les produits
            $contenuPaniersAndProduits = $contenuPanierRepository->findContenuPanier($panier[0]['id']);

            foreach($contenuPaniers as $contenu){
                $total += $contenu->getQuantite() * $contenu->getProduit()->getPrix();
            }
    
            return $this->render('panier/index.html.twig', [
                'panier' => $panier,
                'contenuPaniers' => $contenuPaniersAndProduits,
                'total' => $total,
            ]);
        }


        // if($panier){
        //     return $this->render('panier/index.html.twig', [
        //         'paniers' => $panier,
        //     ]);
        // }else {
        //     // si l'utilisateur n'a pas de panier on lui créer
        //     // return $this->redirectToRoute('app_panier_new', [], Response::HTTP_SEE_OTHER);
        // }
        
    }

    #[Route('/new', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PanierRepository $panierRepository): Response
    {
        // quand l'utilisateur s'inscrit on lui creer automatiquement un panier
        // quand il se connecte on recupere sont panier qui est en etat false
        // a chaque fois qu'un utilisateur paye un panier et que celui si passe à l'etat true
        // on lui crée un nouveau panier

        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

            $panier->setUtilisateur($this->getUser());
            $panier->setDate(new \DateTime());
            $panier->setEtat(0);
            $panierRepository->save($panier, true);
        
        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, PanierRepository $panierRepository): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $panierRepository->save($panier, true);

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, PanierRepository $panierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            $panierRepository->remove($panier, true);
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/{id}', name: 'app_panier_delete_contenu')]
    public function deletecontenu($id, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $contenuPanier = $contenuPanierRepository->findBy(['id'=> $id]);
        // dd($contenuPanier);
        if ($contenuPanier) {
            $contenuPanierRepository->remove($contenuPanier[0], true);
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);



        // $userId = $user->getId();
        //     $panier = $panierRepository->findPanier($userId);
        //     // $contenuPaniers = $contenuPanierRepository->findContenuPanier($panier[0]['id']);
        //     $contenuPaniers = $contenuPanierRepository->findBy(['panier'=> $panier[0]]);
    }

    #[Route('achat/validation/{id}', name: 'app_panier_achat', methods: ['GET', 'POST'])]
    public function achat(Request $request, Panier $panier, PanierRepository $panierRepository)
    {
        // on valide l'achat, on ferme le panier en mettant le status "etat" à true
        $panier->setEtat(1);
        // on save
        $panierRepository->save($panier, true);
        // on créer un nouveau panier, avec un "etat" à false
        return $this->redirectToRoute('app_panier_new', [], Response::HTTP_SEE_OTHER);

    }
}
