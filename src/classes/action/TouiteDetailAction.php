<?php

namespace touiteur\action;

use touiteur\repository\TouiteurRepository;
use touiteur\render\Template;

class TouiteDetailAction extends Action
{
    public function execute(): string
    {
        $id = (int) ($_GET['id_touite'] ?? $_GET['id'] ?? 1);
        $repo = TouiteurRepository::getInstance();
        $touite = $repo->getTouiteById($id);

        return $this->page("Détail du touite", Template::Detail, [
            'touite' => $touite
        ]);
    }
}