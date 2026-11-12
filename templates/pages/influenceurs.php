<?php use touiteur\render\View; ?>
<section class="win">
    <div class="titlebar"><span>Influenceurs - Utilisateurs les plus suivis</span></div>
    <div class="panel">
        <?php if (empty($data['influenceurs'])): ?>
            <div class="empty">Aucun utilisateur n'a encore d'abonnés.</div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Pseudo</th>
                        <th class="num">Abonnés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['influenceurs'] as $i): ?>
                        <tr>
                            <td><?= View::e($i['nom']) ?></td>
                            <td>
                                <a href="index.php?action=profil&amp;user=<?= urlencode($i['pseudo']) ?>">
                                    @<?= View::e($i['pseudo']) ?>
                                </a>
                            </td>
                            <td class="num"><?= (int) $i['nb_abonnes'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>