<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\render\View;
use touiteur\repository\TouiteurRepository;

class RechercherTagsAction extends Action {
    public function execute(): string {
        $user = $this->getUtilisateurConnecte();
        $moi  = $user['id'] ?? '';
        $q    = ltrim(trim((string) ($_GET['q'] ?? '')), '#');
        $tag  = trim((string) ($_GET['tag'] ?? ''));
        $repo = TouiteurRepository::getInstance();

        // Sans recherche -> les tags suivis
        // Avec recherche -> tous les tags correspondants
        $tags = $q === ''
            ? $repo->getTagsSuivis($moi)
            : $repo->rechercherTags($q, $moi);

        // Pas de page complete uniqement le template des tags
        return View::render(Template::TagResults, [
            'tags'   => $tags,
            'q'      => $q,
            'retour' => $this->urlAccueil($tag, $q),
        ]);
    }
}
