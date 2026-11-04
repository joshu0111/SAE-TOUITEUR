<?php
session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();

// touiteur\repository\TouiteurRepository::setConfig(__DIR__ . '/db.config.ini');

$dispatch = new touiteur\dispatch\Dispatch();
$dispatch->addRoute('default', touiteur\action\HomeAction::class);
$dispatch->addRoute('home',    touiteur\action\HomeAction::class);
$dispatch->addRoute('test',    touiteur\action\DefaultAction::class);

echo $dispatch->run();
