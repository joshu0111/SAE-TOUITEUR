<?php

namespace touiteur\action;

class DefaultAction extends Action {

    public function __construct() {
        parent::__construct();
    }

    public function execute(): string {
        return <<<'HTML'
        <h1>Tout Fonctionne Bien </h1>
        HTML;
    }
}
