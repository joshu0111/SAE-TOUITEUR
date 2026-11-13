<?php use touiteur\render\View; ?>
<article class="touite" id="touite-<?= (int) $data['touite']['id_touite'] ?>">
    <a class="touite-lien" href="index.php?action=detail&amp;id=<?= (int) $data['touite']['id_touite'] ?>"
       aria-label="Ouvrir le touite de @<?= View::e($data['touite']['pseudo']) ?>"></a>
    <div class="avatar"><?= View::e(strtoupper(substr($data['touite']['auteur'], 0, 2))) ?></div>
    <div>
        <div class="head">
            <a class="lien-user" href="index.php?action=profil&amp;user=<?= urlencode($data['touite']['pseudo']) ?>"><span class="name"><?= View::e($data['touite']['auteur']) ?></span></a>
            <a class="lien-user" href="index.php?action=profil&amp;user=<?= urlencode($data['touite']['pseudo']) ?>"><span class="handle">@<?= View::e($data['touite']['pseudo']) ?></span></a>
            <span class="date"><?= View::e($data['touite']['date']) ?></span>
        </div>
        <div class="texte"><?= View::contenuAvecTags($data['touite']['contenu']) ?></div>
        <?php if (!empty($data['touite']['image'])): ?>
            <figure class="touite-image">
                <img src="<?= View::e($data['touite']['image']) ?>"
                     alt="<?= View::e($data['touite']['image_alt']) ?>"
                     loading="lazy">
            </figure>
        <?php endif; ?>
        <div class="actions">
    <?php
        $idTouite = (int) $data['touite']['id_touite'];
        $note     = (int) $data['touite']['note'];
        $maNote   = $data['touite']['ma_note'] !== null ? (int) $data['touite']['ma_note'] : null;
    ?>
    <form class="vote" method="post" action="index.php?action=evaluer-un-touite">
        <input type="hidden" name="retour" value="<?= View::e('index.php?' . ($_SERVER['QUERY_STRING'] ?? '')) ?>">
        <input type="hidden" name="id_touite" value="<?= $idTouite ?>">
        <input type="hidden" name="page" value="<?= (int) $data['page'] ?>">
        <button type="submit" name="vote" value="like"
                class="<?= $maNote === 1 ? 'on' : '' ?>"
                title="J'aime">▲ Like</button>
        <span class="note <?= $note > 0 ? 'pos' : ($note < 0 ? 'neg' : '') ?>" title="Note du touite"><?= $note > 0 ? '+' . $note : $note ?></span>
        <button type="submit" name="vote" value="dislike"
                class="<?= $maNote === -1 ? 'on neg' : '' ?>"
                title="Je n'aime pas">▼ Dislike</button>
    </form>
</div>
    </div>
</article>
