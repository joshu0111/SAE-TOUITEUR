<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class ProfilAction extends Action {
    private const TOUITE_PAR_PAGE = 10;

    public function execute(): string {
        $repo = TouiteurRepository::getInstance();

        $moi    = $this->getUtilisateurConnecte()['id'] ?? null;
        $pseudo = ltrim(trim((string) ($_GET['user'] ?? $moi ?? '')), '@');

        $u = $repo->getUtilisateur($pseudo);
        if ($u === null) {
            http_response_code(404);
            return $this->page('Profil introuvable', Template::Profil, $this->introuvable($pseudo));
        }

        $nbTouites = $repo->compterTouitesParUtilisateur($pseudo);

        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);
        $pages = max(1, (int) ceil($nbTouites / self::TOUITE_PAR_PAGE));
        $page  = min($page, $pages);

        return $this->page('Profil de @' . $pseudo, Template::Profil, [
            'user' => [
                'nom'            => $u['prenom'] . ' ' . $u['nom'],
                'handle'         => $u['id_utilisateur'],
                'bio'            => '',
                'inscrit_le'     => '-',
                'nb_touites'     => $nbTouites,
                'nb_abonnes'     => $repo->compterAbonnes($pseudo),
                'nb_abonnements' => $repo->compterAbonnements($pseudo),
                'est_moi'        => ($pseudo === $moi),
                'suivi'          => $moi !== null && $moi !== $pseudo
                                    && $repo->suitUtilisateur($moi, $pseudo),
            ],
            'touites' => $repo->getTouitesParUtilisateur(
                $pseudo,
                self::TOUITE_PAR_PAGE,
                ($page - 1) * self::TOUITE_PAR_PAGE,
                $moi
            ),
            'follows' => $repo->getAbonnements($pseudo),
            'page'    => $page,
            'pages'   => $pages,
            'tags'    => $repo->getTagsParUtilisateur($pseudo),
        ]);
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
