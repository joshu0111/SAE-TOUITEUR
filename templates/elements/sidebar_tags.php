<?php use touiteur\render\View; ?>
<section class="win">
    <div class="titlebar"><span>Tags</span></div>
    <div class="panel">
        <div class="tag-list">
            <?php foreach ($data['tags'] as $tag): ?>
                <a class="tag-item" href="main.php?action=home&amp;tag=<?= View::e($tag['libelle']) ?>">
                    <span>#<?= View::e($tag['libelle']) ?></span>
                    <em><?= (int) $tag['nb'] ?></em>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
