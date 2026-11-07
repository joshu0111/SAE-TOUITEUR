<?php

namespace touiteur\exceptions;

class PseudoNotFound extends \Exception {
    public function __construct(){
            parent::__construct("Pseudo n'existe pas.");
        }
}
