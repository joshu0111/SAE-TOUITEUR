<?php

namespace touiteur\action;

use touiteur\render\Template;

class ProfilAction extends Action {
    private const TOUITE_PAR_PAGE = 10;
    private const MON_HANDLE = 'josh_r';

    public function execute(): string {
        // Handle demande dans l'URL, sinon l'utilisateur connecte, sinon le handle par defaut
        $moi    = $this->getUtilisateurConnecte()['id_name'] ?? self::MON_HANDLE;
        $handle = ltrim(trim((string) ($_GET['user'] ?? $moi)), '@');

        $users = $this->demoUsers();
        if (!isset($users[$handle])) {
            http_response_code(404);
            return $this->page('Profil introuvable', Template::Profil, $this->introuvable($handle));
        }
        $u = $users[$handle];

        // Touites de cet utilisateur
        $touites = array_values(array_filter(
            $this->demoTouites(),
            fn(array $t) => $t['handle'] === $handle
        ));

        // Tags les plus utilises par cet utilisateur
        $comptes = [];
        foreach ($touites as $t) {
            if (preg_match_all('/#(\w+)/u', $t['contenu'], $m)) {
                foreach ($m[1] as $libelle) {
                    $comptes[$libelle] = ($comptes[$libelle] ?? 0) + 1;
                }
            }
        }
        arsort($comptes);
        $tags = [];
        foreach ($comptes as $libelle => $nb) {
            $tags[] = ['libelle' => $libelle, 'nb' => $nb];
        }

        // Abonnes = les utilisateurs qui suivent ce handle
        $nbAbonnes = 0;
        foreach ($users as $autre) {
            if (in_array($handle, $autre['follows'], true)) {
                $nbAbonnes++;
            }
        }

        // Abonnements, au format attendu par la sidebar
        $follows = [];
        foreach ($u['follows'] as $h) {
            if (isset($users[$h])) {
                $follows[] = ['nom' => $users[$h]['nom'], 'handle' => $h];
            }
        }

        // Pagination en filtrant l'entree
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);
        $pages   = max(1, (int) ceil(count($touites) / self::TOUITE_PAR_PAGE));
        $page    = min($page, $pages);
        $visible = array_slice($touites, ($page - 1) * self::TOUITE_PAR_PAGE, self::TOUITE_PAR_PAGE);

        return $this->page('Profil de @' . $handle, Template::Profil, [
            'user' => [
                'nom'            => $u['nom'],
                'handle'         => $handle,
                'bio'            => $u['bio'],
                'inscrit_le'     => $u['inscrit_le'],
                'nb_touites'     => count($touites),
                'nb_abonnes'     => $nbAbonnes,
                'nb_abonnements' => count($u['follows']),
                'est_moi'        => ($handle === $moi),
                'suivi'          => in_array($handle, $this->demoUsers()[$moi]['follows'] ?? [], true),
            ],
            'touites' => $visible,
            'follows' => $follows,
            'page'    => $page,
            'pages'   => $pages,
            'tags'    => $tags,
        ]);
    }

    /**
     * Donnees de demonstration, a remplacer par des requetes dans TouiteurRepository
     */
    private function demoUsers(): array {
        return [
            'josh_r'   => ['nom' => 'Josh',     'bio' => 'Étudiant en BUT info.',  'inscrit_le' => '01/09/2026', 'follows' => ['neo', 'trinity']],
            'neo'      => ['nom' => 'Neo',      'bio' => 'Le réseau est partout.', 'inscrit_le' => '01/09/2026', 'follows' => ['trinity', 'morpheus']],
            'trinity'  => ['nom' => 'Trinity',  'bio' => '',                       'inscrit_le' => '02/09/2026', 'follows' => ['neo']],
            'morpheus' => ['nom' => 'Morpheus', 'bio' => 'Pas de cuillère.',      'inscrit_le' => '02/09/2026', 'follows' => []],
        ];
    }

    private function demoTouites(): array {
        return [
            ['auteur' => 'Josh',     'handle' => 'josh_r',   'date' => '03/11/2026 08:00', 'contenu' => 'Premier touite ! #sae #php'],
            ['auteur' => 'Neo',      'handle' => 'neo',      'date' => '03/11/2026 09:14', 'contenu' => 'Le réseau est partout. #cyberpunk'],
            ['auteur' => 'Trinity',  'handle' => 'trinity',  'date' => '03/11/2026 10:02', 'contenu' => 'Docker Oracle met 4 minutes à démarrer. #bdd #docker'],
            ['auteur' => 'Morpheus', 'handle' => 'morpheus', 'date' => '03/11/2026 10:40', 'contenu' => 'Il n\'y a pas de cuillère. Juste un bug. #php'],
            ['auteur' => 'Neo',      'handle' => 'neo',      'date' => '03/11/2026 13:08', 'contenu' => 'Cookies httponly activés. Enfin. #php #securite'],
        ];
    }

    /**
     * Donnees affichees quand le profil demande n'existe pas
     */
    private function introuvable(string $handle): array {
        return [
            'user' => [
                'nom'            => '?',
                'handle'         => $handle,
                'bio'            => 'Cet utilisateur n\'existe pas.',
                'inscrit_le'     => '-',
                'nb_touites'     => 0,
                'nb_abonnes'     => 0,
                'nb_abonnements' => 0,
                'est_moi'        => false,
                'suivi'          => false,
            ],
            'touites' => [],
            'follows' => [],
            'page'    => 1,
            'pages'   => 1,
            'tags'    => [],
        ];
    }
}