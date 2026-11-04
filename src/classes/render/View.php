<?php

namespace touiteur\render;

class View {
    /**
     * Methde qui permet d'afficher le template fourni en parametre
     */
    public static function render(Template $template, array $data = []) : string {
        // Bufferise les sortie
        ob_start();
        // Insere le fichier de template
        require __DIR__ . '/../../../templates/' . $template->value . '.php';
        // Renvoie tout ce qui a ete bufferise
        return ob_get_clean();
    }

    /**
     * Methode qui permet nettoyer les donnée et d'eviter les injection XSS
     */
    public static function e(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}
