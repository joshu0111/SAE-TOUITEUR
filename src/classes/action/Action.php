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
    protected function getUtilisateurConnecte(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * true si l'utilisateur connecte est administrateur
     */
    protected function estAdmin(): bool {
        return ($_SESSION['user']['role'] ?? 'user') === 'admin';
    }

    /**
     * Redirige vers l'accueil si l'utilisateur n'est pas administrateur
     */
    protected function exigerAdmin(): void {
        if (!$this->estAdmin()) {
            header('Location: index.php');
            exit();
        }
    }
}
