<?php

session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();

touiteur\repository\TouiteurRepository::setConfig(__DIR__ . '/db.config.ini');

// Pages accessibles sans être connecté
$actionsPubliques = ['connexion', 'inscription'];

// Action demandée (accueil par défaut)
$action = $_GET['action'] ?? 'home';

// Un visiteur non connecté est redirigé vers la connexion, sauf pour les pages publiques
if (!isset($_SESSION['user']['id']) && !in_array($action, $actionsPubliques, true)) {
    $_GET['action'] = 'connexion';
}

$dispatch = new touiteur\dispatch\Dispatch();
$dispatch->addRoute('default', touiteur\action\HomeAction::class);
$dispatch->addRoute('home', touiteur\action\HomeAction::class);
$dispatch->addRoute('cree-un-touite', touiteur\action\CreeUnTouite::class);
$dispatch->addRoute('home',    touiteur\action\HomeAction::class);
$dispatch->addRoute('test',    touiteur\action\DefaultAction::class);
$dispatch->addRoute('detail', touiteur\action\TouiteDetailAction::class);
$dispatch->addRoute('evaluer-un-touite', touiteur\action\EvaluerTouite::class);
$dispatch->addRoute('profil', touiteur\action\ProfilAction::class);
$dispatch->addRoute('connexion', touiteur\action\ConnexionAction::class);
$dispatch->addRoute('deconnexion', touiteur\action\DeconnexionAction::class);
$dispatch->addRoute('inscription', touiteur\action\InscriptionAction::class);

echo $dispatch->run();
