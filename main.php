<?php
echo "salut";
session_start();

require_once __DIR__ . '/src/loader/ClassLoader.php';
$loader = new loader\ClassLoader('touiteur\\', __DIR__ . '/src/classes');
$loader->register();



// touiter\repository\DeefyRepository::setConfig(__DIR__ . '/deefy.db.ini');


$dispatch = new touiteur\dispatch\Dispatcher();
$dispatch   -> addRoute("default", touiteur\action\DefaultAction::class)

echo $dispatch->run();
