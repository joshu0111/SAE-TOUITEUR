<?php

use touiteur\action\CreeUnTouite;
session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();

if (!isset($_SESSION['user'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'      => 1,
        'name'    => 'Josh',
        'id_name' => 'josh_r',
    ];
}
touiteur\repository\TouiteurRepository::setConfig(__DIR__ . '/db.config.ini');

$dispatch = new touiteur\dispatch\Dispatch();
$dispatch->addRoute('default', touiteur\action\HomeAction::class);
$dispatch->addRoute('cree-un-touite', touiteur\action\CreeUnTouite::class);
<<<<<<< HEAD
$dispatch->addRoute('evaluer-un-touite', touiteur\action\EvaluerTouite::class); // NOUVEAU
$dispatch->addRoute('home',    touiteur\action\HomeAction::class);
$dispatch->addRoute('profil', touiteur\action\ProfilAction::class);
$dispatch->addRoute('test',    touiteur\action\DefaultAction::class);
=======
$dispatch->addRoute('home', touiteur\action\HomeAction::class);
>>>>>>> feature/lister-par-tag

echo $dispatch->run();
