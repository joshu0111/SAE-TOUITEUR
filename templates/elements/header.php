<?php use touiteur\render\View; ?>
<header class="win">
    <div class="titlebar">
        <span>Touiteur - Accueil</span>
        <div class="btns"><span>_</span><span>□</span><span>×</span></div>
    </div>
    <div class="panel header">
        <a class="logo" href="index.php">TOUITEUR<small>.net</small></a>
        <nav class="nav">
            <a href="index.php?action=home" class="on-link">Accueil</a>
            <a href="#">Explorer</a>
            <a href="#">Notifications</a>
            <a href="#">Mon profil</a>
            <a href="#">Déconnexion</a>
        </nav>
        <input class="search" type="search" placeholder="Rechercher #tag ou @user">
    </div>
</header>