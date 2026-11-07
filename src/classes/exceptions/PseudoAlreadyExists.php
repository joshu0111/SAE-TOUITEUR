<?php

namespace touiteur\exceptions;

class PseudoAlreadyExists extends \Exception {
    public function __construct(){
            parent::__construct("Un utilisateur dispose deja de ce pseudo.");
        }
}
