<?php
use touiteur\render\View;
?>
<section class="win explorer">
    <div class="titlebar"><span>Explorer - Trouver des utilisateurs</span></div>

    <form class="panel explorer-search" method="get" action="index.php">
        <input type="hidden" name="action" value="explorer">
        <input type="search" name="q" value="<?= View::e($data['recherche']) ?>"
               placeholder="Rechercher un pseudo ou un nom (ex : yanis)" autofocus>
    </form>

    <div class="user-grid">
        <?php if (empty($data['utilisateurs'])): ?>
            <div class="empty">Aucun utilisateur trouvé.</div>
        <?php endif; ?>

        <?php foreach ($data['utilisateurs'] as $u): ?>
            <article class="user-card">
                <div class="avatar"><?= View::e(strtoupper(substr($u['nom'], 0, 2))) ?></div>

                <a class="nom" href="index.php?action=profil&amp;user=<?= urlencode($u['pseudo']) ?>">
                    <?= View::e($u['nom']) ?>
                </a>
                <span class="handle">@<?= View::e($u['pseudo']) ?></span>

                <div class="stats">
                    <div><b><?= (int) $u['nb_touites'] ?></b>touites</div>
                    <div><b><?= (int) $u['nb_abonnes'] ?></b>abonnés</div>
                    <div><b><?= (int) $u['nb_abonnements'] ?></b>abonnements</div>
                </div>

                <?php if ($u['pseudo'] !== $data['moi']): ?>
                    <form method="post" action="index.php?action=suivre">
                        <input type="hidden" name="id_suivi" value="<?= View::e($u['pseudo']) ?>">
                        <input type="hidden" name="retour" value="<?= View::e('index.php?' . ($_SERVER['QUERY_STRING'] ?? '')) ?>">
                        <button type="submit" class="<?= $u['suivi'] ? 'on' : '' ?>">
                            <?= $u['suivi'] ? 'Abonné ✓' : 'S\'abonner' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
