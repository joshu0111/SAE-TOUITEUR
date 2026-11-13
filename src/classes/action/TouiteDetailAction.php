<?php

namespace touiteur\action;

use touiteur\repository\TouiteurRepository;
use touiteur\render\Template;

class TouiteDetailAction extends Action
{
    public function execute(): string
    {
        $id     = filter_var($_GET['id'] ?? $_GET['id_touite'] ?? null, FILTER_VALIDATE_INT);
        $user   = $this->getUtilisateurConnecte();
        $repo   = TouiteurRepository::getInstance();
        $touite = $id !== false && $id !== null
            ? $repo->getTouiteById((int) $id, $user['id'] ?? null)
            : null;

        if ($touite === null) {
            // Si on ne trouve pas le touite dans la bdd
            http_response_code(404);
            return $this->page('Touite introuvable', Template::Detail, [
                'touite' => null,
                'tags'   => [],
            ]);
        }

        return $this->page('Touite de @' . $touite['pseudo'], Template::Detail, [
            'touite'     => $touite,
            'tags'       => $repo->getTagsDuTouite((int) $id),
            'nb_touites' => $repo->compterTouitesParUtilisateur($touite['pseudo']),
            'nb_abonnes' => $repo->compterAbonnes($touite['pseudo']),
        ]);
    }
}
