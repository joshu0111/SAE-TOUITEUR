<?php
use touiteur\render\View;

$utilisateur = $_SESSION['user'] ?? null;
?>
<header class="win">
    <div class="titlebar">
        <span>Touiteur - Accueil</span>
        <div class="btns"><span>_</span><span>□</span><span>×</span></div>
    </div>
    <div class="panel header">
        <a class="logo" href="index.php">TOUITEUR<small>.net</small></a>
        <nav class="nav">
            <a href="index.php?action=home" class="on-link">Accueil</a>
            <a href="index.php?action=explorer">Explorer</a>

            <?php if ($utilisateur !== null): ?>
                <?php if (($utilisateur['role'] ?? 'user') === 'admin'): ?>
                    <a href="index.php?action=influenceurs">Influenceurs</a>
                    <a href="index.php?action=tendances">Tendances</a>
                <?php endif; ?>
                <a href="index.php?action=profil">Mon profil</a>
                <a href="index.php?action=deconnexion">Déconnexion</a>
            <?php else: ?>
                <a href="index.php?action=connexion">Connexion</a>
            <?php endif; ?>
        </nav>
        <form class="header-search" method="get" action="index.php">
            <input type="hidden" name="action" value="recherche">
            <input class="search" type="search" name="q" placeholder="Rechercher #tag ou @user" autocomplete="off">
        </form>
    </div>
</header>
