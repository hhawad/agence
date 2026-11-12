<?php
// =============================================================
// Contrôleur : connexion d'un agent (employé ou administrateur)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Agent.php';

// Un agent déjà connecté va directement dans son espace
if (agentConnecte()) {
    header('Location: accueil.php');
    exit;
}

$erreur = '';
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($identifiant === '' || $motDePasse === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $modeleAgent = new Agent();
        $agent = $modeleAgent->trouverParIdentifiant($identifiant);

        // Le compte doit exister, être actif, et le mot de passe doit être bon
        if ($agent !== null && $agent['actif'] && password_verify($motDePasse, $agent['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['id_agent'] = $agent['id_agent'];
            $_SESSION['prenom_agent'] = $agent['prenom'];
            $_SESSION['role'] = $agent['role'];
            $_SESSION['id_agence'] = $agent['id_agence'];

            header('Location: accueil.php');
            exit;
        }

        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}

require __DIR__ . '/../vues/agent/connexion.php';
