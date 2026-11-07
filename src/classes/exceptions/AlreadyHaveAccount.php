<?php

namespace touiteur\exceptions;

class AlreadyHaveAccount extends \Exception {
    public function __construct(){
            parent::__construct("Mail deja associé a un compte.");
        }
}
