<?php use touiteur\render\View; ?>
<section class="win">
    <div class="titlebar"><span>Abonnements</span></div>
    <div class="panel">
        <?php foreach ($data['follows'] as $follow): ?>
            <div class="follow">
                <div class="avatar"><?= View::e(strtoupper(substr($follow['nom'], 0, 2))) ?></div>
                <div class="who">
                    <b><?= View::e($follow['nom']) ?></b>
                    <i>@<?= View::e($follow['handle']) ?></i>
                </div>
                <span class="dot" title="en ligne"></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
