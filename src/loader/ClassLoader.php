<?php

namespace loader;

class ClassLoader {
    protected string $prefixe;
    protected string $racine;

    public function __construct(string $prefixe, string $racine){
        $this->prefixe = $prefixe;
        $this->racine = $racine;
    }

    /**
     * Methode qui permet de charger une classe a partir de son path
     * @param nomClass : string
     * @return void
     */
    public function loadClass(string $nomClass) : void{
        $pos = strpos($nomClass, $this->prefixe);
        if ($pos === 0){
            $sub = substr($nomClass, $pos+strlen($this->prefixe));
            $sub = str_replace("\\", "/", $sub);
            $path = $this->racine . '/' . $sub . ".php";

            if (is_file($path)){
                require_once($path);
            }else{
            throw new \Exception("Impossible de charger la classe {$nomClass}: fichier introuvable.");
            }
        }else{
            throw new \Exception("Impossible de charger la classe {$nomClass}: mauvais namespace.");
        }
    }

    /**
     * Methode qui permet d'enregistrer l'autoloader en tant que autoloader
     */
    public function register(): void{
        spl_autoload_register([$this,'loadClass']);
    }
}
