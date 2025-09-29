<?php

namespace App\Controller;

use App\Service\BookSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_home', methods: ['GET'])]
    public function home(BookSearch $bookSearch, Request $request): Response
    {
        // Tous les livres pour non-connecté ou ROLE_USER
        $data = $bookSearch->getBooksFromRequest($request); // $user = null = tous les livres

        return $this->render('default/home.html.twig', $data);
    }
}
