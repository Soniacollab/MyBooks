<?php

namespace App\Twig;

use App\Entity\Book;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'genres' => Book::GENRES
        ];
    }
}
