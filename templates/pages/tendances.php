<?php
use touiteur\render\View;

$liste = $data['tendances'];

$max   = 1;
$total = 0;
foreach ($liste as $t) {
    $max    = max($max, (int) $t['nb']);
    $total += (int) $t['nb'];
}
?>
<section class="win explorer">
    <div class="titlebar"><span>Tendances - Tags les plus mentionnés</span></div>

    <div class="panel admin-summary">
        <span><b><?= count($liste) ?></b> tag<?= count($liste) > 1 ? 's' : '' ?></span>
        <span><b><?= $total ?></b> mention<?= $total > 1 ? 's' : '' ?> au total</span>
        <span class="tri">Tri : mentions croissantes</span>
    </div>

    <div class="user-grid">
        <?php if (empty($liste)): ?>
            <div class="empty">Aucun tag n'a encore été utilisé.</div>
        <?php endif; ?>

        <?php foreach ($liste as $t):
            $nb  = (int) $t['nb'];
            $pct = round($nb / $max * 100);
            $top = ($nb === $max && count($liste) > 1);
        ?>
            <article class="user-card rank-card<?= $top ? ' top' : '' ?>">
                <?php if ($top): ?><span class="rank-badge">★ TOP</span><?php endif; ?>

                <div class="avatar<?= $top ? ' v' : '' ?>">#</div>

                <a class="nom" href="index.php?action=home&amp;tag=<?= urlencode($t['libelle']) ?>">
                    #<?= View::e($t['libelle']) ?>
                </a>
                <span class="handle">voir les touites</span>

                <div class="rank-count"><b><?= $nb ?></b> <?= $nb > 1 ? 'mentions' : 'mention' ?></div>
                <div class="rank-bar"><span style="width: <?= $pct ?>%"></span></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
