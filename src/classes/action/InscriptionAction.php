<?php

namespace touiteur\action;

class InscriptionAction extends Action{
    public function execute() : string {
        // if (isset($_SESSION['user']['id'])){
        //     unset($_SESSION['user']);
        // }

        header('Location: index.php');
        exit;
    }
}
