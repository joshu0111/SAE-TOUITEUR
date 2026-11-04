<?php
use touiteur\render\View;
use touiteur\render\Template;
?>
<aside>
    <section class="win">
        <div class="titlebar"><span>Mon profil</span></div>
        <div class="panel profile-mini">
            <div class="avatar v">JR</div>
            <b style="color:var(--vert)">Josh</b><br>
            <span style="color:var(--violet);font-family:var(--mono);font-size:12px">@josh_r</span>
        </div>
    </section>
    <?= View::render(Template::SidebarFollows, ['follows' => $data['follows']]) ?>
</aside>

<main>
    <?= View::render(Template::Compose) ?>

    <section class="win">
        <div class="titlebar"><span>Touites affichés</span></div>
        <div class="panel" style="padding:0">
            <?php if (empty($data['touites'])): ?>
                <div class="empty">Aucun touite.</div>
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
    <?= View::render(Template::SidebarTags, ['tags' => $data['tags']]) ?>
</aside>