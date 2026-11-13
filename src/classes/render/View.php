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

    /**
     * Echappe un texte et transforme chaque #tag en lien vers la recherche de ce tag
     */
    public static function contenuAvecTags(string $s): string {
        // Decoupe le texte en morceaux en isolant les hashtags
        // Supprime tout ce qui n'est pas une lettre (\p{L}), un chiffre (\p{N}), d'une longueur de 50 char
        // L'option PREG_SPLIT_DELIM_CAPTURE permet de garder les hashtags dans le tableau de resultat, et NO_EMPTY evite les morceaux vides.
        $morceaux = preg_split('/(#[\p{L}\p{N}_]{1,50})/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $html = '';
        foreach ($morceaux as $m) {
            if (preg_match('/^#[\p{L}\p{N}_]{1,50}$/u', $m) === 1) {
                $tag = mb_strtolower(substr($m, 1));
                $html .= '<a class="hashtag" href="index.php?action=home&amp;tq=' . urlencode($tag) . '">'
                       . self::e($m) . '</a>';
            } else {
                $html .= self::e($m);
            }
        }
        return $html;
    }
}
