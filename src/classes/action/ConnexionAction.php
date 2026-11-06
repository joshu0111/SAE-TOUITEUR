<?php

namespace touiteur\action;

use touiteur\render\{Template, View};
use touiteur\exceptions\{IncorrectPassword, PseudoNotFound};
use touiteur\repository\TouiteurRepository;


class ConnexionAction extends Action{
    public function execute() : string {
        // Si l'utilisateur est deja connecte
        if (isset($_SESSION['user']['id'])){
            // On revoie le home
            header("Location: index.php");
            exit();
        }

        // Si methode GET recu
        if ($this->http_method==="GET"){
            // Retour de la page de connexion
            header("Location: index.php");
            exit();
        }

        // Si methode POST recu
        if ($this->http_method==="POST"){
            // Si toutes les variables existent
            if ($_POST['pseudo'] && $_POST['password']){
                try {
                    $bdd = TouiteurRepository::getInstance();
                    $user = $bdd->connexionUser($_POST['pseudo'], $_POST['password']);
                    $_SESSION['user'] = [
                        'id'      => $_POST['pseudo'],
                        'name'    => $user['nom'] . " " . $user['prenom']
                    ];
                    header("Location: index.php");
                    exit();
                } catch (IncorrectPassword $e){
                    // On renvoie la page de connexion avec peudo inconnu
                    return View::render(Template::Connexion, ['erreur'=> "Pseudo inconnu.", 'pseudo'=>$_POST['pseudo']]);
                } catch (PseudoNotFound $e){
                    // On renvoie la page de connexion avec mdp incorrect
                    return View::render(Template::Connexion, ['erreur'=> "Mot de passe incorrect."]);
                }
            }
        }
    }
}
