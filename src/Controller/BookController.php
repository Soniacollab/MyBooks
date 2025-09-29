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
    use BookFormTrait;

    public function __construct(private readonly BookManager $bookManager) {}

    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        $book = new Book();
        $book->setUser($this->getUser());

        return $this->handleBookForm($book, $request, $this->bookManager);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Book $book, Request $request): Response
    {
        $this->denyAccessIfNotOwnerOrAdmin($book);

        return $this->handleBookForm($book, $request, $this->bookManager, true, true);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Book $book): Response
    {
        $this->denyAccessIfNotOwnerOrAdmin($book);

        $this->bookManager->delete($book);

        $this->addFlash('success', 'Le livre a bien été supprimé.');

        $redirect = $this->isGranted('ROLE_ADMIN') ? 'admin_dashboard' : 'default_home';
        return $this->redirectToRoute($redirect);
    }

    private function denyAccessIfNotOwnerOrAdmin(Book $book): void
    {
        $currentUser = $this->getUser();
        $bookUser    = $book->getUser();

        if (!$currentUser || !$bookUser) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour effectuer cette action.");
        }

        if (!$this->isGranted('ROLE_ADMIN') && $bookUser->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les droits pour effectuer cette action.");
        }
    }

    #[Route('/my-books', name: 'my_books')]
    #[IsGranted('ROLE_USER')]
    public function myBooks(BookSearch $bookSearch, Request $request): Response
    {
        $data = $bookSearch->getBooksFromRequest($request, $this->getUser());

        return $this->render('book/my_books.html.twig', $data);
    }

    #[Route('/{slug}', name: 'showdetails', methods: ['GET'])]
    public function showDetails(BookFetcher $bookFetcher, string $slug): Response
    {
        $book = $bookFetcher->getBookBySlug($slug);

        if (!$book) {
            throw $this->createNotFoundException('Aucun livre trouvé.');
        }

        $data = ['book' => $book];

        return $this->render('default/book.html.twig', $data);
    }
}
