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
     */
    abstract public function execute() : string;

    /**
     * Ancre HTML  pour revenir au bon endroit de la page apres une action
     */
    protected function ancre(string $brut): string {
        // Supprime tout ce qui n'est pas une lettre (\p{L}), un chiffre (\p{N}), un underscore, un point ou un tiret
        // Le modificateur /u est utilise pour bien traiter le format Unicode
        $propre = preg_replace('/[^\p{L}\p{N}_.-]/u', '', $brut);
        return $propre !== '' ? '#' . $propre : '';
    }

    /**
     * Adresse de retour
     */
    protected function retourValide(string $brut): string {
        // Teste si la chaine commence bine par index\.php sinon redirige
        return preg_match('#^index\.php\?#', $brut) ? $brut : 'index.php?action=home';
    }

    /**
     * Adresse de l'accueil avec le filtre par tag et la recherche de tags en cours
     */
    protected function urlAccueil(?string $tag, string $tq): string {
        $url = 'index.php?action=home';
        if ($tag !== null && $tag !== '') {
            $url .= '&tag=' . urlencode($tag);
        }
        if ($tq !== '') {
            $url .= '&tq=' . urlencode($tq);
        }
        return $url;
    }

    /**
     * Verifie si un user est connecte
     */
    protected function getUtilisateurConnecte(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * True si l'utilisateur connecte est administrateur
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
