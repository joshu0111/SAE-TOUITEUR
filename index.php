<?php

session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();





touiteur\repository\TouiteurRepository::setConfig(__DIR__ . '/db.config.ini');

// On change l'action si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    // Si l'action est differente de inscription ou de connexion
    if ($_GET["action"]!=='connexion' ||  $_GET["action"]!=='inscription'){
        $_GET["action"] = 'connexion';
    }
}

$dispatch = new touiteur\dispatch\Dispatch();
$dispatch->addRoute('default', touiteur\action\HomeAction::class);
$dispatch->addRoute('cree-un-touite', touiteur\action\CreeUnTouite::class);
$dispatch->addRoute('evaluer-un-touite', touiteur\action\EvaluerTouite::class); // NOUVEAU
$dispatch->addRoute('home',    touiteur\action\HomeAction::class);
$dispatch->addRoute('profil', touiteur\action\ProfilAction::class);
$dispatch->addRoute('home', touiteur\action\HomeAction::class);
$dispatch->addRoute('connexion', touiteur\action\ConnexionAction::class);
$dispatch->addRoute('deconnexion', touiteur\action\ConnexionAction::class);
$dispatch->addRoute('inscription', touiteur\action\InscriptionAction::class);

echo $dispatch->run();
