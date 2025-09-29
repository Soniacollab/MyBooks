<?php

namespace App\Service;

use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class BookManager
{
    public function __construct(
        private SluggerInterface $slugger,
        private EntityManagerInterface $entityManager,
        private ImageUploader $imageUploader,
    ) {}

    /**
     * Sauvegarde ou met à jour un livre.
     */
    public function saveBook(Book $book, ?UploadedFile $imageFile = null, bool $flush = true): void
    {
        // Génération du slug
        $slug = $this->slugger->slug($book->getTitle() ?? 'untitled');
        $book->setSlug(strtolower((string) $slug));

        // Upload de l'image si fournie
        if ($imageFile) {
            if (!str_starts_with($imageFile->getMimeType(), 'image/')) {
                throw new InvalidArgumentException('Le fichier doit être une image.');
            }
            $newName = $this->imageUploader->uploadCover($imageFile, $book->getTitle() ?? 'untitled', $book->getCoverImage());
            $book->setCoverImage($newName);
        }

        // Timestamps
        $now = new DateTimeImmutable();
        if (null === $book->getCreatedAt()) {
            $book->setCreatedAt($now);
        }
        $book->setUpdatedAt($now);

        $this->entityManager->persist($book);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Supprime un livre et son image si nécessaire.
     */
    public function delete(Book $book, bool $flush = true): void
    {
        if ($book->getCoverImage()) {
            $this->imageUploader->deleteFile($book->getCoverImage());
        }

        $this->entityManager->remove($book);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
