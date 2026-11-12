<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\repository\TouiteurRepository;

class InfluenceursAction extends Action {
    public function execute(): string {
        $this->exigerAdmin();

        $influenceurs = TouiteurRepository::getInstance()->getInfluenceurs();

        return $this->page('Influenceurs', Template::Influenceurs, [
            'influenceurs' => $influenceurs,
        ]);
    }
}