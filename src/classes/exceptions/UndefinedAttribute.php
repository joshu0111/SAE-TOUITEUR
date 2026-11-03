<?php

namespace touiter\exceptions;

class UndefinedAttribute extends \Exception {
    public function __construct(){
            parent::__construct("L'attribut donnee n'existe pas.", 400, null);
        }
}
