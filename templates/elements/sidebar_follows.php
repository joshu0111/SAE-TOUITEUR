<?php use touiteur\render\View; ?>
<section class="win">
    <div class="titlebar"><span>Abonnements</span></div>
    <div class="panel">
        <?php foreach ($data['follows'] as $follow): ?>
            <div class="follow">
                <div class="avatar"><?= View::e(strtoupper(substr($follow['nom'], 0, 2))) ?></div>
                <div class="who">
                    <a class="lien-user" href="index.php?action=profil&amp;user=<?= urlencode($follow['pseudo']) ?>"><b><?= View::e($follow['nom']) ?></b></a>
                    <a class="lien-user" href="index.php?action=profil&amp;user=<?= urlencode($follow['pseudo']) ?>"><i>@<?= View::e($follow['pseudo']) ?></i></a>
                </div>
                <span class="dot" title="en ligne"></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
