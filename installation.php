<?php
// =============================================================
// Contrôleur : création du premier administrateur
// Cette page ne fonctionne que tant qu'aucun agent n'existe.
// =============================================================

require_once 'includes/session.php';
require_once 'modeles/Agent.php';
require_once 'modeles/Agence.php';

$modeleAgent = new Agent();
$modeleAgence = new Agence();

// Sécurité : dès qu'un agent existe, l'installation est terminée
$installationTerminee = $modeleAgent->compter() > 0;

$agences = $modeleAgence->lister();
$erreurs = [];
$succes = false;

$nom = '';
$prenom = '';
$identifiant = '';
$idAgence = 0;

if (!$installationTerminee && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $idAgence = (int) ($_POST['id_agence'] ?? 0);

    if ($nom === '' || $prenom === '' || $identifiant === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (mb_strlen($nom) > 50 || mb_strlen($prenom) > 50 || mb_strlen($identifiant) > 50) {
        $erreurs[] = 'Un des champs est trop long.';
    }
    if (mb_strlen($motDePasse) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($motDePasse !== $confirmation) {
        $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
    }
    if ($modeleAgence->trouver($idAgence) === null) {
        $erreurs[] = 'Veuillez choisir une agence.';
    }

    if (empty($erreurs)) {
        $modeleAgent->creer($nom, $prenom, $identifiant, $motDePasse, 'admin', $idAgence);
        $succes = true;
        $installationTerminee = true;
    }
}

require 'vues/installation.php';
