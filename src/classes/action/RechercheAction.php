<?php

namespace touiteur\action;

/**
 * Recherche du champ de l'en-tete : #tag -> recherche de tags, @user -> Explorer
 */
class RechercheAction extends Action {
    public function execute(): string {
        $q = trim((string) ($_GET['q'] ?? ''));

        if (str_starts_with($q, '@')) {
            header('Location: index.php?action=explorer&q=' . urlencode(ltrim(substr($q, 1), '@')));
            exit;
        }

        header('Location: ' . $this->urlAccueil(null, ltrim($q, '#')));
        exit;
    }
}
