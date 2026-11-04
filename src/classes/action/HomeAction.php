<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class HomeAction extends Action {
    private const TOUITE_PAR_PAGE = 10;

    public function execute(): string {


        $tag = trim($_GET['tag'] ?? '');
        return $this->afficher($tag !== '' ? $tag : null);
    }

    /**
        * Affiche la page d'accueil, filtree par tag si $tag n'est pas null.
        * La meme logique de pagination s'applique dans les deux cas.
        */
    private function afficher(?string $tag): string {
        $repo = TouiteurRepository::getInstance();

        // Recuperation de la page a afficher en filtrant l'entree

        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);

        // Recupere le nombre de touites
        $total = $tag === null
            ? $repo->compterTouites()
            : $repo->compterTouitesFiltreParTag($tag);


        $pages = max(1, (int) ceil($repo->compterTouites() / self::TOUITE_PAR_PAGE));

        $page = min($page, $pages);

        $user = $this->getUtilisateurConnecte();                                                   // NOUVEAU
        $visible = $repo->getTouites(self::TOUITE_PAR_PAGE, ($page - 1) * self::TOUITE_PAR_PAGE, $user['id'] ?? null);  // MODIFIÉ

        // Recupere le nombre de page totale, la page actuelle(en verifiant que la page demande est possible a affihcher) et la longueur
        $pages  = max(1, (int) ceil($total / self::TOUITE_PAR_PAGE));
        $page   = min($page, $pages);
        $offset = ($page - 1) * self::TOUITE_PAR_PAGE;

        // Recupere tout les touites
        $touites = $tag === null
            ? $repo->getTouites(self::TOUITE_PAR_PAGE, $offset)
            : $repo->getTouitesParTag($tag, self::TOUITE_PAR_PAGE, $offset);

        return $this->page('Accueil', Template::Home, [
            'touites' => $touites,
            'follows' => $repo->getFollows(),
            'tags'    => $repo->getTags(),
            'page'    => $page,
            'pages'   => $pages,
            'tag'     => $tag,
        ]);
    }
}
