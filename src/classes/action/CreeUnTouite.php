<?php
namespace touiteur\action;

use touiteur\touites\Touite;
use touiteur\repository\TouiteurRepository;

class CreeUnTouite extends Action {
    public function execute(): string {
        $user = $this->getUtilisateurConnecte();

        if ($this->http_method === 'POST' && $user !== null) {
            $contenu = trim($_POST['contenu'] ?? '');
            if ($contenu !== '' && mb_strlen($contenu) <= 235) {
                $touite = new Touite($contenu);
                TouiteurRepository::getInstance()->insererTouite($touite, $user['id']);
            }
        }

        header('Location: index.php?action=home');
        exit;
    }
}