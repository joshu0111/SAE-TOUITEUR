<?php
namespace touiteur\action;

use touiteur\touites\Touite;
use touiteur\repository\TouiteurRepository;

class CreeUnTouite extends Action {
    public function execute(): string {
        if ($this->http_method === 'POST') {
            $contenu = trim($_POST['contenu'] ?? '');
            if ($contenu !== '' && mb_strlen($contenu) <= 280) {
                $touite = new Touite($contenu);
                $idUser = 1;
                TouiteurRepository::getInstance()->insererTouite($touite, $idUser);
            }
        }
        header('Location: index.php?action=home');
        exit;
    }
}