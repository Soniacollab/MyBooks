<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use RuntimeException;
use InvalidArgumentException;


readonly class ImageUploader
{

    public function __construct(
        private string $uploadDir,
        private SluggerInterface $slugger
    ){}

    public function uploadCover(UploadedFile $imageFile, string $title, ?string $oldFileName = null): string
    {
        // vérif mime
        if (!in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png'], true)) {
            throw new InvalidArgumentException('Seuls les formats JPG, PNG et WEBP sont acceptés.');
        }

        $slug = (string) $this->slugger->slug($title)->lower();
        $fileName = $slug . '-' . time() . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move($this->uploadDir, $fileName);
        } catch (FileException $e) {
            throw new RuntimeException('Erreur lors de l\'upload de l\'image.');
        }

        if ($oldFileName) {
            $this->deleteFile($oldFileName);
        }

        return $fileName;
    }

    public function deleteFile(string $fileName): void
    {
        $path = $this->uploadDir . '/' . $fileName;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
