<?php
use touiteur\render\View;
use touiteur\render\Template;

/*
 * Panneau des tags de l'accueil : recherche + liste (tags suivis par defaut).
 * Donnees attendues : tags, tq (recherche en cours), retour (adresse de la page)
 */
$tq = $data['tq'] ?? '';
?>
<section class="win">
    <div class="titlebar"><span>Tags</span></div>
    <div class="panel">
        <form method="get" action="index.php">
            <input type="hidden" name="action" value="home">
            <input type="search" id="tagSearch" name="tq" class="search tag-search"
                   placeholder="Rechercher un tag…" autocomplete="off"
                   value="<?= View::e($tq) ?>">
        </form>
        <div id="tagResults" class="tag-results">
            <?= View::render(Template::TagResults, [
                'tags'   => $data['tags'],
                'q'      => $tq,
                'retour' => $data['retour'] ?? 'index.php?action=home',
            ]) ?>
        </div>
    </div>
</section>
