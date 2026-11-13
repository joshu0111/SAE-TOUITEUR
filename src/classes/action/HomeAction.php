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
     * Affiche l'accueil
     * Sans filtre, c'est le mur de l'utilisateur (comptes suivis + tags suivis)
     * Avec un tag, ce sont les touites de ce tag (sans avoir besoin de le suivre)
     */
    private function afficher(?string $tag): string {
        $repo = TouiteurRepository::getInstance();
        $user = $this->getUtilisateurConnecte();
        $idUser = $user['id'] ?? null;

        // Pagination : page demandee, bornee au nombre de pages disponibles
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);

        if ($tag !== null) {
            $total = $repo->compterTouitesFiltreParTag($tag);
        } else {
            $total = $idUser === null ? 0 : $repo->compterMur($idUser);
        }

        // Nombre de pages max
        $pages  = max(1, (int) ceil($total / self::TOUITE_PAR_PAGE));

        // Verifie que la page demande depasse le nombre de celle qui existe
        $page   = min($page, $pages);

        $offset = ($page - 1) * self::TOUITE_PAR_PAGE;

        if ($tag !== null) {
            $touites = $repo->getTouitesParTag($tag, self::TOUITE_PAR_PAGE, $offset, $idUser);
        } else {
            $touites = $idUser === null ? [] : $repo->getMur(self::TOUITE_PAR_PAGE, $offset, $idUser);
        }

        $tq = ltrim(trim((string) ($_GET['tq'] ?? '')), '#');

        return $this->page('Accueil', Template::Home, [
            'touites' => $touites,
            'follows' => $user !== null ? $repo->getAbonnements($user['id']) : [],
            'tags'    => $user === null ? [] : ($tq === '' ? $repo->getTagsSuivis($user['id']) : $repo->rechercherTags($tq, $user['id'])),
            'tq'      => $tq,
            'retour'  => $this->urlAccueil($tag, $tq),
            'page'    => $page,
            'pages'   => $pages,
            'tag'     => $tag,
        ]);
    }
}
