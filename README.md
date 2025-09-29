\## 📄 Notes TD (explications ligne par ligne)



\### 🔹 Création d’un slug

```php

$slugger = new AsciiSlugger();

$slugTitle = $slugger->slug($book->getTitle());



Trouvé dans la doc Symfony (Slugger) pour générer directement un slug à partir du titre.





🔹 Doctrine QueryBuilder

$qb = $this->bookRepository->createQueryBuilder('b')

&nbsp;   ->orderBy('b.createdAt', 'DESC');



if ($search) {

&nbsp;   $qb->andWhere('b.title LIKE :search')

&nbsp;      ->setParameter('search', '%' . $search . '%');

}



if ($genre) {

&nbsp;   $qb->andWhere('b.genre = :genre')

&nbsp;      ->setParameter('genre', $genre);

}



if ($limit) {

&nbsp;   $qb->setMaxResults($limit);

}



------> createQueryBuilder('b') construit une requête SQL mais en PHP (alias b).

------> %…% permet de rechercher une sous-chaîne.

------> Vu la docs symfony pour réaliser la requête 





🔹 Preview d’image dans Twig

'onchange' => 'previewImage(event)' // déclenche prévisualisation





------> Affiche un aperçu de l’image avant envoi.

------> Idée trouvée sur Medium + ajustée via doc Symfony.





🔹 Extension Twig pour genres

class AppExtension extends AbstractExtension implements GlobalsInterface

{

&nbsp;   public function getGlobals(): array

&nbsp;   {

&nbsp;       return \[

&nbsp;           'genres' => Book::GENRES

&nbsp;       ];

&nbsp;   }

}





----> Rend genres accessible partout sans le passer dans chaque contrôleur.

Avant je passe Book::GENRES dans tous les contrôleurs et sa faisait des warning symphony que je faisais du Repeat Yourself. Donc en regardant la doc Symfony et medium après avoir demandé une solution à chatgpt, j'avais reussi à créer une extension twig.





🔹 Correction services.yaml

parameters:

&nbsp;   upload\_dir: '%kernel.project\_dir%/public/uploads/covers'



services:

&nbsp;   \_defaults:

&nbsp;       autowire: true

&nbsp;       autoconfigure: true

&nbsp;       public: false



&nbsp;   \_instanceof:

&nbsp;       App\\Service\\ImageUploader:

&nbsp;           bind:

&nbsp;               $uploadDir: '%upload\_dir%'





---> Corrige un bug où Twig ne trouvait pas le service.

---> Corrigé grâce à IA debug (bin/console debug:container).





🔹 Service BookSearch (pagination + filtres)

$search = $request->query->get('search', '');

$genre  = $request->query->get('genre', '');

$page   = max(1, (int) $request->query->get('page', 1));

$limit  = 6;



$books = $this->bookFetcher->getBooks($user, $search ?: null, $genre ?: null, $page, $limit);



$totalBooks = $this->bookFetcher->countBooks($user, $search ?: null, $genre ?: null);

$totalPages = (int) ceil($totalBooks / $limit);





-----> Récupère recherche, genre et numéro de page depuis l’URL.

-----> Retourne les livres + infos de pagination.

-----> Corrigé et simplifié grâce à IA + Symfony docs.





📚 Sources utilisées



Docs Symfony : slug, upload, services, QueryBuilder, sécurité, Twig globals.



Docs Doctrine : QueryBuilder.



Medium : idée AppExtension Twig.



IA (ChatGPT) : debug services.yaml, pagination, DRY avec BookSearch.









--------- Résumé étudiant ---------------



J’ai utilisé doc Symfony pour les parties officielles (auth, services, upload, slug).



J’ai pris une idée Medium pour Twig Extension.



J’ai utilisé IA quand j’avais des bugs (services.yaml, DRY avec services, pagination).







