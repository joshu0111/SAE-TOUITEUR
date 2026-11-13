<?php
use touiteur\render\View;

/*
 * Liste de tags : tags suivis (q vide) ou resultats de recherche (q renseigne).
 * Donnees attendues : tags (libelle, nb, suivi), q, retour (page a recharger)
 */
$q      = $data['q'] ?? '';
$retour = $data['retour'] ?? 'index.php?action=home';
?>
<?php if (empty($data['tags'])): ?>
    <div class="empty">
        <?= $q === ''
            ? 'Aucun tag suivi. Recherchez-en un ci-dessus.'
            : ('Aucun tag trouvé pour « ' . View::e($q) . ' ».') ?>
    </div>
<?php endif; ?>

<?php foreach ($data['tags'] as $t):
    $suivi = (bool) ($t['suivi'] ?? true);
    $nb    = (int) $t['nb'];
?>
    <div class="follow tag-row" id="tag-<?= View::e($t['libelle']) ?>">
        <div class="avatar">#</div>
        <div class="who">
            <a href="index.php?action=home&amp;tag=<?= urlencode($t['libelle']) ?>">
                <b>#<?= View::e($t['libelle']) ?></b>
            </a>
            <i><?= $nb ?> mention<?= $nb > 1 ? 's' : '' ?></i>
        </div>
        <form method="post" action="index.php?action=suivre-tag">
            <input type="hidden" name="tag" value="<?= View::e($t['libelle']) ?>">
            <input type="hidden" name="retour" value="<?= View::e($retour) ?>">
            <input type="hidden" name="ancre" value="<?= View::e('tag-' . $t['libelle']) ?>">
            <button type="submit" class="<?= $suivi ? 'on' : '' ?>">
                <?= $suivi ? 'Abonné ✓' : 'S\'abonner' ?>
            </button>
        </form>
    </div>
<?php endforeach; ?>
