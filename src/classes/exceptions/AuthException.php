<?php

namespace touiteur\exceptions;

class AuthException extends \Exception {
    public function __construct(){
            parent::__construct("Mail invalid");
        }
}
