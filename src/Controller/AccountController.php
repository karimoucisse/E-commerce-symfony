<?php

namespace App\Controller;

use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account/{id}', name: 'app_account')]
    public function index($id, PanierRepository $panierRepository ): Response
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $commandes = $panierRepository->buildCommande($userId);
        return $this->render('account/index.html.twig', [
            'id' => $id,
            'commandes'=> $commandes
        ]);
    }
}

