<?php
/*
 * Page de connexion.
 * Données attendues dans $data (fournies par l'action Connexion) :
 *   erreur     : ?string  message d'erreur à afficher (null si aucune)
 *   email      : string   email saisi, pour le pré-remplir après un échec
 */
use touiteur\render\View;

$erreur = $data['erreur'] ?? null;
$email  = $data['pseudo'] ?? '';

?>
<section class="win connexion">
    <div class="titlebar"><span>Connexion.exe</span></div>

    <form class="panel" method="post" action="index.php?action=connexion" novalidate>

        <?php if ($erreur !== null): ?>
            <div class="alert alert-error" role="alert"><?= View::e($erreur) ?></div>
        <?php endif; ?>

        <label class="field">
            <span>Pseudo</span>
            <input type="text" name="pseudo" required autocomplete="pseudo"
                   placeholder="joshua.0111" value="<?= View::e($pseudo) ?>">
        </label>

        <label class="field">
            <span>Mot de passe</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <div class="row">
            <a href="index.php?action=inscription">Pas encore de compte ?</a>
            <button type="submit">Se connecter &gt;&gt;</button>
        </div>
    </form>
</section>
