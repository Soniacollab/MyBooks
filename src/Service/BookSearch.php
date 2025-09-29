<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

readonly class BookSearch
{
    // On injecte le service BookFetcher pour récupérer les livres
    public function __construct(private BookFetcher $bookFetcher) {}

    /**
     * Retourne un tableau contenant les livres filtrés et les valeurs de recherche avec pagination.
     *
     * @param Request $request Requête HTTP pour récupérer les paramètres GET
     * @param User|null $user Filtrer par utilisateur connecté
     * @return array Contient : books, search, genre, page, totalPages
     */
    public function getBooksFromRequest(Request $request, ?User $user = null): array
    {
        // On récupère la valeur du paramètre 'search' de la requête ou une chaîne vide si absent
        $search = $request->query->get('search', '');

        // On récupère la valeur du paramètre 'genre' de la requête ou une chaîne vide si absent
        $genre  = $request->query->get('genre', '');

        // On récupère le numéro de page actuel (par défaut 1)
        $page   = max(1, (int) $request->query->get('page', 1));

        // Nombre de livres par page
        $limit  = 6;

        /**
         * Ici appelle BookFetcher pour récupérer les livres en fonction des filtres et de la page
         * On transforme les chaînes vides en null pour ne pas filtrer si le champ est vide
         */
        $books = $this->bookFetcher->getBooks($user, $search ?: null, $genre ?: null, $page, $limit);

        // Récupère le nombre total de livres correspondant aux filtres
        $totalBooks = $this->bookFetcher->countBooks($user, $search ?: null, $genre ?: null);

        // Calcule le nombre total de pages
        $totalPages = (int) ceil($totalBooks / $limit);

        return [
            'books'      => $books,
            'search'     => $search,
            'genre'      => $genre,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }
}
