<?php

namespace App\Traits;

use App\Entity\Book;
use App\Form\BookType;
use App\Service\BookManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait BookFormTrait
{
    /**
     * Gère la création ou la modification d'une entité Book pour éviter les répétitions.
     *
     * @param Book $book L'entité Book à créer ou modifier
     * @param Request $request La requête HTTP courante
     * @param BookManager $bookManager Service pour gérer l'enregistrement et la suppression
     * @param bool $isAdminRedirect Indique si l'on doit rediriger vers le tableau de bord admin après soumission
     * @param bool $isEdit Indique s'il s'agit d'une modification (true) ou d'une création (false)
     * @return Response|null Retourne une Response si le formulaire est soumis et valide, sinon null
     */

    public function handleBookForm(Book $book, Request $request, BookManager $bookManager, bool $isAdminRedirect = false, bool $isEdit = false): ?Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);


        // Si le formulaire est soumis, on enregistre les données
        if ($form->isSubmitted() && $form->isValid()) {
            $bookManager->saveBook($book, $form->get('coverImage')->getData());
            $this->addFlash('success', $isEdit ? 'Livre modifié avec succès' : 'Livre publié avec succès');

            if ($isAdminRedirect && $this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }

            return $this->redirectToRoute('book_my_books');
        }


        // Afficher la vue
        return $this->render('book/form.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }
}
