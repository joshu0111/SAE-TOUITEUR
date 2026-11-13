<?php
use touiteur\render\View;
use touiteur\render\Template;

// Utilisateur connecte (null si personne n'est connecté)
$moi = $_SESSION['user'] ?? null;
?>
<aside>
    <section class="win">
        <div class="titlebar"><span>Mon profil</span></div>
        <div class="panel profile-mini">
            <?php if ($moi !== null): ?>
                <div class="avatar v"><?= View::e(strtoupper(substr($moi['name'], 0, 2))) ?></div>
                <b style="color:var(--vert)"><?= View::e($moi['name']) ?></b><br>
                <span style="color:var(--violet);font-family:var(--mono);font-size:12px">@<?= View::e($moi['id']) ?></span>
            <?php else: ?>
                <div class="empty">Vous n'êtes pas connecté.</div>
                <a class="btn" href="index.php?action=connexion">Se connecter</a>
            <?php endif; ?>
        </div>
    </section>
    <?php if ($moi !== null): ?>
        <?= View::render(Template::SidebarFollows, ['follows' => $data['follows']]) ?>
    <?php endif; ?>
</aside>

<main>
    <?php if ($moi !== null): ?>
        <?= View::render(Template::Compose) ?>
    <?php else: ?>
        <section class="win">
            <div class="titlebar"><span>Rejoindre la conversation</span></div>
            <div class="panel empty">
                <a class="btn" href="index.php?action=connexion">Connectez-vous</a> pour publier un touite.
            </div>
        </section>
    <?php endif; ?>

    <section class="win">
        <div class="titlebar"><span>Touites affichés</span></div>
        <div class="panel" style="padding:0">
            <?php if (empty($data['touites'])): ?>
                <div class="empty">
                    <?= $data['tag'] === null
                        ? 'Votre mur est vide. Suivez des personnes ou abonnez-vous à des tags pour voir leurs touites ici.'
                        : 'Aucun touite pour ce tag.' ?>
                </div>
            <?php endif; ?>
            <?php foreach ($data['touites'] as $touite): ?>
                <?= View::render(Template::Touite, ['touite' => $touite, 'page' => $data['page']]) ?>  <!-- MODIFIÉ -->
            <?php endforeach; ?>
        </div>
    </section>

    <?= View::render(Template::Pagination, [
        'page'  => $data['page'],
        'pages' => $data['pages'],
        'tag'   => $data['tag'],
    ]) ?>
</main>

<aside>
    <?= View::render(Template::TagPanel, ['tags' => $data['tags'], 'tq' => $data['tq'], 'retour' => $data['retour']]) ?>
</aside>
