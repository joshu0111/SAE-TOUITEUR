<?php

namespace touiteur\action;

use touiteur\render\Template;

class HomeAction extends Action {
    private const TOUITE_PAR_PAGE = 10;

    public function execute(): string {
        // Recuperation de la page a afficher en filtrant l'entree
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);

        // Données factices : à remplacer par TouiteurRepository
        $touites = $this->demoTouites();

        // Recupere le nombre de page max
        $pages = max(1, (int) ceil(count($touites) / self::TOUITE_PAR_PAGE));

        // Recupere la page demande en verifiant qu'elle est possible a afficher
        $page = min($page, $pages);

        //  Recuperation de tout les touites dans la plage
        $visible = array_slice($touites, ($page - 1) * self::TOUITE_PAR_PAGE, self::TOUITE_PAR_PAGE);

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

    private function demoTouites(): array {
        return [
            ['auteur' => 'Neo',      'handle' => 'neo',      'date' => '03/11/2026 09:14', 'contenu' => 'Le réseau est partout. #cyberpunk'],
            ['auteur' => 'Trinity',  'handle' => 'trinity',  'date' => '03/11/2026 10:02', 'contenu' => 'Docker Oracle met 4 minutes à démarrer. #bdd #docker'],
            ['auteur' => 'Morpheus', 'handle' => 'morpheus', 'date' => '03/11/2026 10:40', 'contenu' => 'Il n\'y a pas de cuillère. Juste un bug. #php'],
            ['auteur' => 'Cypher',   'handle' => 'cypher',   'date' => '03/11/2026 11:15', 'contenu' => 'Un bon café, un bon exemple de sécurité web. #securite'],
            ['auteur' => 'Tank',     'handle' => 'tank',     'date' => '03/11/2026 12:01', 'contenu' => 'Rendu du dépôt git le 13/11 à 20h. #sae'],
            ['auteur' => 'Switch',   'handle' => 'switch',   'date' => '03/11/2026 12:30', 'contenu' => 'Le rouge ou le bleu ? Le vert néon. #design'],
            ['auteur' => 'Neo',      'handle' => 'neo',      'date' => '03/11/2026 13:08', 'contenu' => 'Cookies httponly activés. Enfin. #php #securite'],
        ];
    }
}
