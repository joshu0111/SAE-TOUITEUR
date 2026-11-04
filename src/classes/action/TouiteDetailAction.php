<?php
namespace touiteur\action;

use touiteur\render\Template;

class TouiteDetailAction extends Action {
    public function execute(): string {
        return $this->page('Détail du touite', Template::Detail, [
            'touite' => [
                'auteur' => 'Marwane',
                'handle' => 'marwane',
                'date' => '04/11/2026 14:00',
                'contenu' => 'Test de l\'affichage du détail du touite.',
                'score' => 0
            ]
        ]);
    }
}