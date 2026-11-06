<?php

namespace touiteur\action;

class DeconnexionAction extends Action{
    public function execute() : string {
        if (isset($_SESSION['user']['id'])){
            unset($_SESSION['user']);
        }

        header('Location: index.php');
        exit;
    }
}
