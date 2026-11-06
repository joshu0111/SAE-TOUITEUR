<?php
/*
 * Page d'inscription.
 * Données attendues dans $data (fournies par l'action Inscription) :
 *   erreur     : ?string  message d'erreur global (null si aucun)
 *   pseudo     : string   pseudo saisi, pour le pré-remplir après un échec
 *   nom        : string   nom saisi
 *   prenom     : string   prénom saisi
 *   email      : string   email saisi
 * Le mot de passe n'est jamais ré-affiché.
 */
use touiteur\render\View;

$erreur = $data['erreur'] ?? null;
$pseudo = $data['pseudo'] ?? '';
$nom    = $data['nom'] ?? '';
$prenom = $data['prenom'] ?? '';
$email  = $data['email'] ?? '';
?>
<section class="win connexion">
    <div class="titlebar"><span>Inscription.exe</span></div>

    <form class="panel" method="post" action="index.php?action=inscription" novalidate>

        <?php if ($erreur !== null): ?>
            <div class="alert alert-error" role="alert"><?= View::e($erreur) ?></div>
        <?php endif; ?>

        <label class="field">
            <span>Pseudo</span>
            <input type="text" name="pseudo" required maxlength="50"
                   autocomplete="username" value="<?= View::e($pseudo) ?>">
        </label>

        <label class="field">
            <span>Prénom</span>
            <input type="text" name="prenom" required maxlength="50"
                   autocomplete="given-name" value="<?= View::e($prenom) ?>">
        </label>

        <label class="field">
            <span>Nom</span>
            <input type="text" name="nom" required maxlength="50"
                   autocomplete="family-name" value="<?= View::e($nom) ?>">
        </label>

        <label class="field">
            <span>Adresse e-mail</span>
            <input type="email" name="email" required maxlength="100"
                   autocomplete="email" placeholder="vous@exemple.fr" value="<?= View::e($email) ?>">
        </label>

        <label class="field">
            <span>Mot de passe</span>
            <input type="password" name="password" required
                   autocomplete="new-password">
        </label>

        <label class="field">
            <span>Confirmer le mot de passe</span>
            <input type="password" name="password_confirm" required
                   autocomplete="new-password">
        </label>

        <div class="row">
            <a href="index.php?action=connexion">Déjà un compte ?</a>
            <button type="submit">Créer mon compte &gt;&gt;</button>
        </div>
    </form>
</section>
