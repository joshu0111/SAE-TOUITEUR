<?php

use touiteur\action\CreeUnTouite;
session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();

if (!isset($_SESSION['user']) || $_SESSION['user']['id'] !== 'josh_r') {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'      => 'josh_r',
        'name'    => 'Josh',
        'id_name' => 'josh_r',
    ];
}
touiteur\repository\TouiteurRepository::setConfig(__DIR__ . '/db.config.ini');

$dispatch = new touiteur\dispatch\Dispatch();
$dispatch->addRoute('default', touiteur\action\HomeAction::class);
$dispatch->addRoute('cree-un-touite', touiteur\action\CreeUnTouite::class);

$dispatch->addRoute('evaluer-un-touite', touiteur\action\EvaluerTouite::class); // NOUVEAU
$dispatch->addRoute('home',    touiteur\action\HomeAction::class);
$dispatch->addRoute('profil', touiteur\action\ProfilAction::class);
$dispatch->addRoute('home', touiteur\action\HomeAction::class);


echo $dispatch->run();
