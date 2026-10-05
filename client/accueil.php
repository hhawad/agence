<?php
// =============================================================
// Contrôleur : accueil de l'espace client (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';

// Seul un client connecté peut voir cette page
exigerClientConnecte();

$prenom = $_SESSION['prenom'];

require __DIR__ . '/../vues/client/accueil.php';
