<?php
use touiteur\render\View;
use touiteur\render\Template;

/*
 * Données attendues dans $data :
 *   user    : ['nom', 'handle', 'bio', 'inscrit_le', 'nb_touites', 'nb_abonnes',
 *              'nb_abonnements', 'est_moi' (bool), 'suivi' (bool)]
 *   touites : liste des touites de cet utilisateur
 *   page, pages : pagination
 *   tags    : tags les plus utilisés par cet utilisateur
 *   follows : (optionnel) abonnements de cet utilisateur
 *   (voir ProfilAction pour la construction de ces données)
 */
$u = $data['user'];
$initiales = mb_strtoupper(mb_substr($u['nom'], 0, 2));
?>
<aside>
    <section class="win">
        <div class="titlebar">
            <span>Profil - @<?= View::e($u['handle']) ?></span>
            <div class="btns"><span>_</span><span>□</span><span>×</span></div>
        </div>
        <div class="profile-banner"></div>
        <div class="panel profile-mini profile-full">
            <div class="avatar v"><?= View::e($initiales) ?></div>
            <b class="profile-name"><?= View::e($u['nom']) ?></b><br>
            <span class="profile-handle">@<?= View::e($u['handle']) ?></span>

            <?php if (!empty($u['bio'])): ?>
                <p class="profile-bio"><?= nl2br(View::e($u['bio'])) ?></p>
            <?php endif; ?>

            <div class="profile-meta">
                Inscrit le <?= View::e($u['inscrit_le']) ?>
            </div>

            <div class="stats">
                <div><b><?= (int) $u['nb_touites'] ?></b>touites</div>
                <div><b><?= (int) $u['nb_abonnes'] ?></b>abonnés</div>
                <div><b><?= (int) $u['nb_abonnements'] ?></b>abonnements</div>
            </div>

            <div class="profile-actions">
                <?php if ($u['est_moi']): ?>
                    <button type="button">Modifier le profil</button>
                <?php else: ?>
                    <button type="button" class="<?= $u['suivi'] ? 'on' : '' ?>">
                        <?= $u['suivi'] ? 'Abonné ✓' : 'S\'abonner' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($data['follows'])): ?>
        <?= View::render(Template::SidebarFollows, ['follows' => $data['follows']]) ?>
    <?php endif; ?>
</aside>

<main>
    <section class="win">
        <div class="titlebar">
            <span>Touites de @<?= View::e($u['handle']) ?></span>
        </div>
        <div class="panel" style="padding:0">
            <?php if (empty($data['touites'])): ?>
                <div class="empty">
                    <?= $u['est_moi'] ? 'Vous n\'avez pas encore touité.' : 'Cet utilisateur n\'a pas encore touité.' ?>
                </div>
            <?php endif; ?>
            <?php foreach ($data['touites'] as $touite): ?>
                <?= View::render(Template::Touite, ['touite' => $touite, 'page' => $data['page']]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <?= View::render(Template::Pagination, [
        'page'  => $data['page'],
        'pages' => $data['pages'],
        'base'  => 'index.php?action=profil&user=' . urlencode($u['handle']),
    ]) ?>
</main>

<aside>
    <?= View::render(Template::SidebarTags, ['tags' => $data['tags']]) ?>
</aside>
