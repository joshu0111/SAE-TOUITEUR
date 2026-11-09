<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class ExplorerAction extends Action {
    public function execute(): string {
        $moi       = $this->getUtilisateurConnecte()['id'] ?? null;
        $recherche = trim((string) ($_GET['q'] ?? $moi));

        $utilisateurs = TouiteurRepository::getInstance()->getUtilisateurs($recherche, $moi);

        return $this->page('Explorer', Template::Explorer, [
            'recherche'    => $recherche,
            'utilisateurs' => $utilisateurs,
            'moi'          => $moi,
        ]);
    }
}
