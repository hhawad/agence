<?php
// =============================================================
// Contrôleur : accueil de l'espace agent (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Agence.php';

exigerAgentConnecte();

$prenom = $_SESSION['prenom_agent'];
$estAdmin = $_SESSION['role'] === 'admin';

$modeleAgence = new Agence();
$agence = $modeleAgence->trouver($_SESSION['id_agence']);

require __DIR__ . '/../vues/agent/accueil.php';
