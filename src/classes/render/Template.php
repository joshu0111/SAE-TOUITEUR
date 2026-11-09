<?php

namespace touiteur\render;

/**
 * Ennumeration qui permet d'avoir des constantes pour les chemins des templates
 */
enum Template : string {
    case Layout  = 'layout';

    case Home = 'pages/home';
    case Detail = 'pages/touite_detail';
    case Profil = 'pages/profil';
    case Connexion = 'pages/connexion';
    case Inscription = 'pages/inscription';
    

    case Header = 'elements/header';
    case Touite = 'elements/touite';
    case Compose = 'elements/compose';
    case Pagination = 'elements/pagination';
    case SidebarFollows = 'elements/sidebar_follows';
    case SidebarTags = 'elements/sidebar_tags';
}