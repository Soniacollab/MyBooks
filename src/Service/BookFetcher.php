<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;


readonly class BookFetcher
{
    public function __construct(private BookRepository $bookRepository) {}


    /**
     * Récupère les livres avec filtres optionnels.
     */
    public function getBooks(?User $user = null,?string $search = null, ?string $genre = null){

        $books = $this->bookRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        if($user){
            $books->andWhere('b.user = :user')
                ->setParameter('user', $user);
        }

        if($search){
            $books->andWhere('LOWER(b.title) LIKE :search')
                ->setParameter('search', '%'.strtolower($search).'%');
        }
        if($genre){
            $books->andWhere('b.genre = :genre')
                ->setParameter('genre', $genre);
        }


        return $books->getQuery()->getResult();

    }

    public function getBookBySlug(string $slug): ?Book
    {
        return $this->bookRepository->findOneBy(['slug' => $slug]);
    }



}
