<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class HomeAction extends Action {
    private const TOUITE_PAR_PAGE = 10;

    public function execute(): string {
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);

        $repo = TouiteurRepository::getInstance();

        $pages = max(1, (int) ceil($repo->compterTouites() / self::TOUITE_PAR_PAGE));

        $page = min($page, $pages);

        $user = $this->getUtilisateurConnecte();                                                   // NOUVEAU
        $visible = $repo->getTouites(self::TOUITE_PAR_PAGE, ($page - 1) * self::TOUITE_PAR_PAGE, $user['id'] ?? null);  // MODIFIÉ

        return $this->page('Accueil', Template::Home, [
            'touites' => $visible,
            'follows' => [
                ['nom' => 'Neo',      'handle' => 'neo'],
                ['nom' => 'Trinity',  'handle' => 'trinity'],
                ['nom' => 'Morpheus', 'handle' => 'morpheus'],
                ['nom' => 'Tank',     'handle' => 'tank'],
            ],
            'tags' => [
                ['libelle' => 'php',    'nb' => 3],
                ['libelle' => 'bdd',    'nb' => 2],
                ['libelle' => 'docker', 'nb' => 1],
            ],
            'page'  => $page,
            'pages' => $pages,
        ]);
    }

}