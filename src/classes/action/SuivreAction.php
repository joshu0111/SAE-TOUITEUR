<?php

namespace touiteur\action;

use touiteur\repository\TouiteurRepository;

class SuivreAction extends Action {
    public function execute(): string {
        $user  = $this->getUtilisateurConnecte();
        $cible = trim((string) ($_POST['id_suivi'] ?? ''));
        $repo  = TouiteurRepository::getInstance();

        if ($this->http_method === 'POST' && $user !== null && $cible !== '' && $cible !== $user['id']
            && $repo->getUtilisateur($cible) !== null) {
            if ($repo->suitUtilisateur($user['id'], $cible)) {
                $repo->desabonner($user['id'], $cible);
            } else {
                $repo->suivreUtilisateur($user['id'], $cible);
            }
        }

        $retour = $this->retourValide($_POST['retour'] ?? '');
        header('Location: ' . $retour . $this->ancre($_POST['ancre'] ?? ''));
        exit;
    }
}
