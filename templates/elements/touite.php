<?php use touiteur\render\View; ?>
<article class="touite" id="touite-<?= (int) $data['touite']['id_touite'] ?>">
    <div class="avatar"><?= View::e(strtoupper(substr($data['touite']['auteur'], 0, 2))) ?></div>
    <div>
        <div class="head">
            <span class="name"><?= View::e($data['touite']['auteur']) ?></span>
            <span class="handle">@<?= View::e($data['touite']['pseudo']) ?></span>
            <span class="date"><?= View::e($data['touite']['date']) ?></span>
        </div>
        <div class="texte"><?= View::e($data['touite']['contenu']) ?></div>
        <div class="actions">
    <?php
        $idTouite = (int) $data['touite']['id_touite'];
        $note     = (int) $data['touite']['note'];
        $maNote   = $data['touite']['ma_note'] !== null ? (int) $data['touite']['ma_note'] : null;
        $dejaVote = $maNote !== null;
    ?>
    <form class="vote" method="post" action="index.php?action=evaluer-un-touite">
        <input type="hidden" name="id_touite" value="<?= $idTouite ?>">
        <input type="hidden" name="page" value="<?= (int) $data['page'] ?>">
        <button type="submit" name="vote" value="like"
                class="<?= $maNote === 1 ? 'on' : '' ?>"
                <?= $dejaVote ? 'disabled' : '' ?> title="J'aime">▲ Like</button>
        <span class="note <?= $note > 0 ? 'pos' : ($note < 0 ? 'neg' : '') ?>" title="Note du touite"><?= $note > 0 ? '+' . $note : $note ?></span>
        <button type="submit" name="vote" value="dislike"
                class="<?= $maNote === -1 ? 'on neg' : '' ?>"
                <?= $dejaVote ? 'disabled' : '' ?> title="Je n'aime pas">▼ Dislike</button>
    </form>
    <a href="index.php?action=detail&id=<?= $idTouite ?>"><button type="button">Détail</button></a>
    <button type="button">Répondre</button>
    <button type="button">Retouiter</button>
    <button type="button">★ Favori</button>
</div>
    </div>
</article>