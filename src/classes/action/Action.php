<?php

namespace touiteur\action;
use touiteur\render\Template;
use touiteur\render\View;

abstract class Action {

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Methode qui permet d'afficher un template
     */
    protected function page(string $title, Template $template, array $data = []): string {
        return View::render(Template::Layout, [
            'title'   => $title,
            'content' => View::render($template, $data),
        ]);
    }

    /**
     * Methode qui permet d'executer le code en focntion de l'action
     * @return string : correspond au texte
     */
    abstract public function execute() : string;
}
