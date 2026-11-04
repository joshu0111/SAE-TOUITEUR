<?php
namespace touiteur\action;
use touiteur\render\View;

class TouiteDetailAction extends Action {
    public function execute(): string {
        $vue = new View(""); 
        return $vue->render('touite_detail.php');
    }
}