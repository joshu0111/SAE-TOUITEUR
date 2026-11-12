<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class TendancesAction extends Action {
    public function execute(): string {
        $this->exigerAdmin();

        $tendances = TouiteurRepository::getInstance()->getTendances();

        return $this->page('Tendances', Template::Tendances, [
            'tendances' => $tendances,
        ]);
    }
}