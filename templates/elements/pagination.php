<?php
use touiteur\render\View;
<<<<<<< HEAD
$base = $data['base'] ?? 'index.php?action=home';
=======

$tagParam = !empty($data['tag']) ? '&amp;tag=' . urlencode($data['tag']) : '';
$lien = fn(int $p) => 'index.php?action=home' . $tagParam . '&amp;page=' . $p;
>>>>>>> feature/lister-par-tag
?>
<section class="win">
    <div class="pagination">
        <?php if ($data['page'] > 1): ?>
            <a class="btn" href="<?= $lien($data['page'] - 1) ?>">&laquo; Précédent</a>
        <?php else: ?>
            <button disabled>&laquo; Précédent</button>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $data['pages']; $i++): ?>
            <a class="btn <?= $i === $data['page'] ? 'on' : '' ?>" href="<?= $lien($i) ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($data['page'] < $data['pages']): ?>
            <a class="btn" href="<?= $lien($data['page'] + 1) ?>">Suivant &raquo;</a>
        <?php else: ?>
            <button disabled>Suivant &raquo;</button>
        <?php endif; ?>

        <span class="info">page <?= $data['page'] ?>/<?= $data['pages'] ?></span>
    </div>
</section>
