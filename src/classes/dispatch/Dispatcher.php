<?php

namespace touiter\dispath;

use Yaf\Dispatcher;

class Dispatch {
    protected $actions = array();
    protected ?string $action = null;

    public function __construct(){
        $this->action = $_GET["action"] ?? "default";
    }

    /**
     * Methode qui permet d'ajouter une action au dispatcher
     * @param $act : string : correspond a l'action qui apparaitra dans l'url
     * @param $action_class :  string : correspond au chemin pour aller jusqu'a la methode action qui s'occupe de l'action specifier dans $act
     * @return Dispatcher
     */
    public function addRoute(string $act, string $action_class) : Dispatcher{
        $this->actions[$act] = $action_class;
        return $this;
    }

    /**
     * Methode qui permet de lancer le Dispacter pour qu'il renvoie aux bonnes classe action en fonction de ce qui se trouve dans l'url
     * @return string
     */
    public function run() : string {
        if (!isset($this->actions[$this->action])){
            throw new \Exception("Action '{$this->action}' not found");
        }
        $class_action = (new $this->actions[$this->action]);
        return $class_action->execute();
    }
}
