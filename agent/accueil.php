<?php
// =============================================================
// Contrôleur : accueil de l'espace agent (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Agence.php';
require_once __DIR__ . '/../modeles/Colis.php';

exigerAgentConnecte();

$prenom = $_SESSION['prenom_agent'];
$estAdmin = $_SESSION['role'] === 'admin';

$modeleAgence = new Agence();
$agence = $modeleAgence->trouver($_SESSION['id_agence']);

// L'administrateur voit les colis de toutes les agences, l'employé ceux de son agence
$modeleColis = new Colis();
$listeColis = $modeleColis->listerATraiter($estAdmin ? null : (int) $_SESSION['id_agence']);

require __DIR__ . '/../vues/agent/accueil.php';
