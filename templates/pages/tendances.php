<?php use touiteur\render\View; ?>
<section class="win">
    <div class="titlebar"><span>Tendances - Tags les plus mentionnés</span></div>
    <div class="panel">
        <?php if (empty($data['tendances'])): ?>
            <div class="empty">Aucun tag n'a encore été utilisé.</div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th class="num">Mentions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['tendances'] as $t): ?>
                        <tr>
                            <td>
                                <a href="index.php?action=home&amp;tag=<?= urlencode($t['libelle']) ?>">
                                    #<?= View::e($t['libelle']) ?>
                                </a>
                            </td>
                            <td class="num"><?= (int) $t['nb'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>