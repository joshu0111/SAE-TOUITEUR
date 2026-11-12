<?php
use touiteur\render\View;

$liste = $data['influenceurs'];

$max   = 1;
$total = 0;
foreach ($liste as $i) {
    $max    = max($max, (int) $i['nb_abonnes']);
    $total += (int) $i['nb_abonnes'];
}
?>
<section class="win explorer">
    <div class="titlebar"><span>Influenceurs - Utilisateurs les plus suivis</span></div>

    <div class="panel admin-summary">
        <span><b><?= count($liste) ?></b> influenceur<?= count($liste) > 1 ? 's' : '' ?></span>
        <span><b><?= $total ?></b> abonnement<?= $total > 1 ? 's' : '' ?> au total</span>
        <span class="tri">Tri : abonnés croissant</span>
    </div>

    <div class="user-grid">
        <?php if (empty($liste)): ?>
            <div class="empty">Aucun utilisateur n'a encore d'abonnés.</div>
        <?php endif; ?>

        <?php foreach ($liste as $i):
            $nb  = (int) $i['nb_abonnes'];
            $pct = round($nb / $max * 100);
            $top = ($nb === $max && count($liste) > 1);
        ?>
            <article class="user-card rank-card<?= $top ? ' top' : '' ?>">
                <?php if ($top): ?><span class="rank-badge">★ TOP</span><?php endif; ?>

                <div class="avatar<?= $top ? ' v' : '' ?>">
                    <?= View::e(mb_strtoupper(mb_substr($i['nom'], 0, 2))) ?>
                </div>

                <a class="nom" href="index.php?action=profil&amp;user=<?= urlencode($i['pseudo']) ?>">
                    <?= View::e($i['nom']) ?>
                </a>
                <span class="handle">@<?= View::e($i['pseudo']) ?></span>

                <div class="rank-count"><b><?= $nb ?></b><?= $nb > 1 ? 'abonnés' : 'abonné' ?></div>
                <div class="rank-bar"><span style="width: <?= $pct ?>%"></span></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>