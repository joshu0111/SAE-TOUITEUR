<?php use touiteur\render\View; ?>
<article class="touite">
    <div class="avatar"><?= View::e(strtoupper(substr($data['touite']['auteur'], 0, 2))) ?></div>
    <div>
        <div class="head">
            <span class="name"><?= View::e($data['touite']['auteur']) ?></span>
            <span class="handle">@<?= View::e($data['touite']['pseudo']) ?></span>
            <span class="date"><?= View::e($data['touite']['date']) ?></span>
        </div>
        <div class="texte"><?= View::e($data['touite']['contenu']) ?></div>
        <div class="actions">
            <button type="button">Répondre</button>
            <button type="button">Retouiter</button>
            <button type="button">★ Favori</button>
        </div>
    </div>
</article>
