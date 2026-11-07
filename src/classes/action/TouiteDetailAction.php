<?php
namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class TouiteDetailAction extends Action {
    public function execute(): string {
        $id = filter_var($_GET['id'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1]
        ]);

        $repo = TouiteurRepository::getInstance();
        $touite = $repo->getTouiteById($id);

        return $this->page('Détail du touite', Template::Detail, [
            'touite' => $touite
        ]);
    }
}