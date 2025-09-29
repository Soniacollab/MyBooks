<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;

readonly class BookFetcher
{
    // On injecte le repository via le constructeur, ce qui permet de l'utiliser dans la classe.
    public function __construct(private BookRepository $bookRepository) {}

    /**
     * Fonction pour récupèrer les livres avec filtres optionnels et pagination.
     *
     * @param User|null $user Filtrer par utilisateur
     * @param string|null $search Filtrer par titre
     * @param string|null $genre Filtrer par genre
     * @param int $page Numéro de page
     * @param int $limit Nombre de livres par page
     * @return Book[]
     */
    public function getBooks(?User $user = null, ?string $search = null, ?string $genre = null, int $page = 1, int $limit = 6): array
    {
        // On commence la requête sur l'entité Book, avec un alias 'b'
        $books = $this->bookRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        // Si un utilisateur est fourni, on filtre les livres de cet utilisateur
        if ($user) {
            $books->andWhere('b.user = :user')
                ->setParameter('user', $user);
        }

        // Si une recherche est fournie, on filtre les livres dont le titre contient la chaîne
        if ($search) {
            $books->andWhere('LOWER(b.title) LIKE :search')
                ->setParameter('search', '%'.strtolower($search).'%');
        }

        // Si un genre est fourni, on filtre les livres de ce genre
        if ($genre) {
            $books->andWhere('b.genre = :genre')
                ->setParameter('genre', $genre);
        }

        // Pagination : on commence à partir de (page-1) * limit et on limite les résultats
        $books->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // Exécute la requête et retourne le résultat
        return $books->getQuery()->getResult();
    }

    /**
     * Récupèrer les livres en fonction de leur slug
     */
    public function getBookBySlug(string $slug): ?Book
    {
        return $this->bookRepository->findOneBy(['slug' => $slug]);
    }

    /**
     * Compte le nombre total de livres selon les filtres
     *
     * Utile pour calculer le nombre de pages de pagination
     */
    public function countBooks(?User $user = null, ?string $search = null, ?string $genre = null): int
    {
        $qb = $this->bookRepository->createQueryBuilder('b')
            ->select('COUNT(b.id)');

        if ($user) {
            $qb->andWhere('b.user = :user')->setParameter('user', $user);
        }

        if ($search) {
            $qb->andWhere('LOWER(b.title) LIKE :search')
                ->setParameter('search', '%'.strtolower($search).'%');
        }

        if ($genre) {
            $qb->andWhere('b.genre = :genre')->setParameter('genre', $genre);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
