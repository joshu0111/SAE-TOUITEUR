<?php

namespace touiteur\action;

use touiteur\repository\TouiteurRepository;

class SuivreTagAction extends Action {
    public function execute(): string {
        $user    = $this->getUtilisateurConnecte();
        $libelle = mb_strtolower(trim((string) ($_POST['tag'] ?? '')));

        if ($this->http_method === 'POST' && $user !== null && $libelle !== '') {
            TouiteurRepository::getInstance()->basculerAbonnementTag($user['id'], $libelle);
        }

        $retour = $this->retourValide($_POST['retour'] ?? '');
        header('Location: ' . $retour . $this->ancre($_POST['ancre'] ?? ''));
        exit;
    }
}
