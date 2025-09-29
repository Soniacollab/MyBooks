<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

readonly class BookSearch
{
    public function __construct(private BookFetcher $bookFetcher) {}

    /**
     * Retourne un tableau contenant les livres filtrés et les valeurs de recherche.
     */
    public function getBooksFromRequest(Request $request, ?User $user = null): array
    {
        $search = $request->query->get('search', '');
        $genre  = $request->query->get('genre', '');

        $books = $this->bookFetcher->getBooks($user, $search ?: null, $genre ?: null);

        return [
            'books'  => $books,
            'search' => $search,
            'genre'  => $genre,
        ];
    }
}
