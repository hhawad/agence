<?php
// =============================================================
// Contrôleur : déconnexion
// =============================================================

require_once 'includes/session.php';

// On vide la session puis on la détruit
$_SESSION = [];
session_destroy();

header('Location: connexion.php');
exit;
