<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Service\BookFetcher;
use App\Service\BookManager;
use App\Service\BookSearch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Traits\BookFormTrait;

#[Route('/books', name: 'book_')]
class BookController extends AbstractController
{
    use BookFormTrait; // Trait contenant la logique réutilisable pour gérer les formulaires de Book

    public function __construct(private readonly BookManager $bookManager) {}


    // Injection du service BookManager pour créer/modifier/supprimer des livres

    /**
     * Création d’un nouveau livre.
     * Accessible uniquement aux utilisateurs connectés (ROLE_USER) et aux admins.
     */
    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        $book = new Book();
        $book->setUser($this->getUser());

        // On délègue le traitement du formulaire au trait
        return $this->handleBookForm($book, $request, $this->bookManager);
    }


    /**
     * Modification d’un livre.
     * Vérifie que l’utilisateur est propriétaire ou admin.
     */

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Book $book, Request $request): Response
    {

        // Seul le propriétaire ou admin pourra éditer
        $this->denyAccessIfNotOwnerOrAdmin($book);

        // On délègue le traitement du formulaire au trait
        return $this->handleBookForm($book, $request, $this->bookManager, true, true);
    }


    /**
     * Suppression d’un livre.
     * Vérifie que l’utilisateur est propriétaire ou admin.
     */
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Book $book): Response
    {
        // Seul le propriétaire ou admin peut éditer
        $this->denyAccessIfNotOwnerOrAdmin($book);


        // Supprime le livre et son image
        $this->bookManager->delete($book);

        $this->addFlash('success', 'Le livre a bien été supprimé.');

        // Redirection : admin vers le dashboard, user normal vers la page de ses livres
        $redirect = $this->isGranted('ROLE_ADMIN') ? 'admin_dashboard' : 'book_my_books';
        return $this->redirectToRoute($redirect);
    }

    private function denyAccessIfNotOwnerOrAdmin(Book $book): void
    {
        $currentUser = $this->getUser();
        $bookUser    = $book->getUser();

        // Si l'utilisateur n'est pas connecté ou le livre n'a pas de propriétaire
        if (!$currentUser || !$bookUser) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour effectuer cette action.");
        }

        // Si ce n'est pas l'admin et que l'utilisateur n'est pas le propriétaire
        if (!$this->isGranted('ROLE_ADMIN') && $bookUser->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour effectuer cette action.");
        }
    }

    #[Route('/my-books', name: 'my_books')]
    #[IsGranted('ROLE_USER')]
    public function myBooks(BookSearch $bookSearch, Request $request): Response
    {
        // Récupère uniquement les livres appartenant à l'utilisateur connecté
        $data = $bookSearch->getBooksFromRequest($request, $this->getUser());


        // Afficher la vue
        return $this->render('book/my_books.html.twig', $data);
    }

    #[Route('/{slug}', name: 'showdetails', methods: ['GET'])]
    public function showDetails(BookFetcher $bookFetcher, string $slug): Response
    {
        // Cherche le livre correspondant au slug
        $book = $bookFetcher->getBookBySlug($slug);

        if (!$book) {
            throw $this->createNotFoundException('Aucun livre trouvé.');
        }

        $data = ['book' => $book];

        // On affiche la vue
        return $this->render('default/book.html.twig', $data);
    }
}
