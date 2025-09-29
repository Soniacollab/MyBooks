<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException; // Exception pour les erreurs d'upload
use Symfony\Component\HttpFoundation\File\UploadedFile; // Représente un fichier uploadé
use Symfony\Component\String\Slugger\SluggerInterface; // Pour créer un slug à partir du titre
use RuntimeException; // Exception levée en cas d'erreur d'exécution
use InvalidArgumentException; // Exception levée si le fichier est invalide


readonly class ImageUploader
{

    public function __construct(
        private string $uploadDir, // On initialise le répertoire avec lequel les images seront stockées
        private SluggerInterface $slugger
    ){}

    public function uploadCover(UploadedFile $imageFile, string $title, ?string $oldFileName = null): string
    {

        // Vérifie le type MIME pour accepter uniquement JPG et PNG
        if (!in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png'], true)) {
            throw new InvalidArgumentException('Seuls les formats JPG et PNG sont acceptés.');
        }

        // Génère un nom de fichier unique à partir du titre et slug
        $slug = (string) $this->slugger->slug($title)->lower();
        $fileName = $slug . '-' . time() . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            // On déplace le fichier uploadé dans le répertoire cible
            $imageFile->move($this->uploadDir, $fileName);
        } catch (FileException $e) {
            // On lance une exception en cas d'erreur lors de l'upload
            throw new RuntimeException('Erreur lors de l\'upload de l\'image.');
        }

        // On supprime l'ancien fichier
        if ($oldFileName) {
            $this->deleteFile($oldFileName);
        }

        // Retourner le nouveau
        return $fileName;
    }


    /**
     * Supprime un fichier existant.
     *
     * @param string $fileName Nom du fichier à supprimer
     */
    public function deleteFile(string $fileName): void
    {
        $path = $this->uploadDir . '/' . $fileName;
        if (file_exists($path)) {
            @unlink($path); // Supprime le fichier, supprime les erreurs si le fichier n'existe pas

        }
    }
}
