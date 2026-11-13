<?php
use touiteur\render\View;

/*
 * Donnees attendues : touite, tags, nb_touites, nb_abonnes
 */
$t = $data['touite'];
?>
<?php if ($t === null): ?>
    <main class="detail-pleine">
        <section class="win">
            <div class="titlebar"><span>Touite introuvable</span></div>
            <div class="panel empty">
                Ce touite n'existe pas ou a été supprimé.
                <a class="btn" href="index.php?action=home">Retour à l'accueil</a>
            </div>
        </section>
    </main>
<?php else:
    $idT    = (int) $t['id_touite'];
    $note   = (int) $t['note'];
    $maNote = $t['ma_note'] !== null ? (int) $t['ma_note'] : null;
    $init   = mb_strtoupper(mb_substr($t['prenom'], 0, 1) . mb_substr($t['nom'], 0, 1));
    $profil = 'index.php?action=profil&amp;user=' . urlencode($t['pseudo']);
    $retour = 'index.php?' . ($_SERVER['QUERY_STRING'] ?? '');
    $noteTxt = $note > 0 ? '+' . $note : (string) $note;
    $noteCls = $note > 0 ? 'pos' : ($note < 0 ? 'neg' : '');
?>
    <main class="detail-pleine">
        <section class="win">
            <div class="titlebar"><span>Touite n°<?= $idT ?></span></div>

            <article class="detail" id="touite-<?= $idT ?>">

                <header class="detail-auteur">
                    <a href="<?= $profil ?>"><div class="avatar v detail-avatar"><?= View::e($init) ?></div></a>
                    <div class="detail-identite">
                        <a class="detail-nom" href="<?= $profil ?>"><?= View::e($t['prenom'] . ' ' . $t['nom']) ?></a>
                        <a class="detail-pseudo" href="<?= $profil ?>">@<?= View::e($t['pseudo']) ?></a>
                        <span class="detail-stats">
                            <?= (int) $data['nb_touites'] ?> touites · <?= (int) $data['nb_abonnes'] ?> abonnés
                        </span>
                    </div>
                    <a class="btn detail-profil" href="<?= $profil ?>">Voir le profil</a>
                </header>

                <div class="detail-texte"><?= View::contenuAvecTags($t['contenu']) ?></div>

                <?php if (!empty($t['image'])): ?>
                    <figure class="detail-image">
                        <a href="<?= View::e($t['image']) ?>" target="_blank" rel="noopener">
                            <img src="<?= View::e($t['image']) ?>" alt="<?= View::e($t['image_alt']) ?>">
                        </a>
                    </figure>
                <?php endif; ?>

                <div class="detail-barre">
                    <form class="detail-vote" method="post" action="index.php?action=evaluer-un-touite">
                        <input type="hidden" name="id_touite" value="<?= $idT ?>">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="retour" value="<?= View::e($retour) ?>">
                        <button type="submit" name="vote" value="like" title="J'aime"
                                class="detail-vote-btn <?= $maNote === 1 ? 'on' : '' ?>">▲ J'aime</button>
                        <span class="detail-note <?= $noteCls ?>" title="Note du touite"><?= $noteTxt ?></span>
                        <button type="submit" name="vote" value="dislike" title="Je n'aime pas"
                                class="detail-vote-btn <?= $maNote === -1 ? 'on neg' : '' ?>">▼ Je n'aime pas</button>
                    </form>
                    <div class="detail-meta">
                        Publié le <?= View::e($t['date']) ?><br>
                        <?= $maNote === 1 ? 'Vous avez aimé ce touite' : ($maNote === -1 ? 'Vous n\'avez pas aimé ce touite' : 'Pas encore voté') ?>
                    </div>
                </div>

                <div class="detail-tags">
                    <h3>Tags de ce touite</h3>
                    <?php if (empty($data['tags'])): ?>
                        <div class="empty">Ce touite ne contient aucun tag.</div>
                    <?php else: ?>
                        <div class="detail-chips">
                            <?php foreach ($data['tags'] as $tg): ?>
                                <a class="detail-chip" href="index.php?action=home&amp;tq=<?= urlencode($tg['libelle']) ?>">
                                    #<?= View::e($tg['libelle']) ?> · <?= (int) $tg['nb'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </article>
        </section>

        <p class="detail-retour">
            <a class="btn" href="index.php?action=home">← Retour au mur</a>
        </p>
    </main>
<?php endif; ?>
