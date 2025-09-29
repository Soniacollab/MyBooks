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

    // On injecte les services via le constructeur, ce qui va permettre de l'utiliser dans la classe.
    public function __construct(
        private SluggerInterface $slugger, // Service pour créer des slugs
        private EntityManagerInterface $entityManager, // Service Doctrine pour persister et supprimer des entités
        private ImageUploader $imageUploader, // Service pour gérer les images (upload et suppression)
    ) {}

    /**
     * Sauvegarde ou mise à jour d'un livre pour éviter les répétitions.
     */
    public function saveBook(Book $book, ?UploadedFile $imageFile = null, bool $flush = true): void
    {
        // Génère un slug à partir du titre du livre, on met 'untitled' si le titre est vide en stockant le slug en minuscules
        $slug = $this->slugger->slug($book->getTitle() ?? 'untitled');
        $book->setSlug(strtolower((string) $slug));

        // Si un fichier image est fourni, on le traite
        if ($imageFile) {

            // On vérifie que le fichier est bien une image
            if (!str_starts_with($imageFile->getMimeType(), 'image/')) {
                throw new InvalidArgumentException('Le fichier doit être une image.');
            }

            // Upload de l'image et récupération du nouveau nom de fichier
            $newName = $this->imageUploader->uploadCover($imageFile, $book->getTitle() ?? 'untitled', $book->getCoverImage());
            // On met à jour la couverture
            $book->setCoverImage($newName);
        }

        // Gestion des dates de création et de mise à jour
        $now = new DateTimeImmutable();
        if (null === $book->getCreatedAt()) {
            $book->setCreatedAt($now);
        }
        $book->setUpdatedAt($now);

        // Prépare le livre pour insertion ou mise à jour
        $this->entityManager->persist($book);

        // Exécute les requêtes SQL en base si demandé
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Supprime un livre et son image associée.
     */
    public function delete(Book $book, bool $flush = true): void
    {
        // On supprime l'image si elle existe
        if ($book->getCoverImage()) {
            $this->imageUploader->deleteFile($book->getCoverImage());
        }

        // On prépare l'entité pour suppression
        $this->entityManager->remove($book);

        // On exécute la suppression en base si demandé
        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
