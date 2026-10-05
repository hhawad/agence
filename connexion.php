<?php
// =============================================================
// Contrôleur : connexion d'un client
// =============================================================

require_once 'includes/session.php';
require_once 'modeles/Client.php';

// Un client déjà connecté n'a rien à faire ici : on l'envoie dans son espace
if (clientConnecte()) {
    header('Location: client/accueil.php');
    exit;
}

$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupérer les données envoyées
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    // 2. Vérifier que les champs sont remplis
    if ($email === '' || $motDePasse === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        // 3. Chercher le client et vérifier son mot de passe
        $modeleClient = new Client();
        $client = $modeleClient->trouverParEmail($email);

        if ($client !== null && password_verify($motDePasse, $client['mot_de_passe'])) {
            // 4. Connexion réussie : on enregistre le client dans la session
            session_regenerate_id(true);
            $_SESSION['id_client'] = $client['id_client'];
            $_SESSION['prenom'] = $client['prenom'];

            header('Location: client/accueil.php');
            exit;
        }

        // Même message dans les deux cas, pour ne pas révéler si l'email existe
        $erreur = 'Email ou mot de passe incorrect.';
    }
}

// 5. Afficher la vue
require 'vues/connexion.php';
