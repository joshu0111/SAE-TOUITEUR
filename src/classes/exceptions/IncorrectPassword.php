<?php

namespace touiteur\exceptions;

class IncorrectPassword extends \Exception {
    public function __construct(){
            parent::__construct("Mot de passe incorrect.");
        }
}
