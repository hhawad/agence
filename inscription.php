<?php
// =============================================================
// Contrôleur : inscription d'un nouveau client
// =============================================================

require_once 'includes/session.php';
require_once 'modeles/Client.php';

$erreurs = [];
$succes = false;

// Valeurs du formulaire (vides au premier affichage)
$nom = '';
$prenom = '';
$email = '';
$telephone = '';

// Le formulaire a-t-il été envoyé ?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupérer les données envoyées
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    // 2. Vérifier les données (ne jamais faire confiance au formulaire)
    if ($nom === '' || $prenom === '' || $email === '' || $telephone === '' || $motDePasse === '') {
        $erreurs[] = 'Tous les champs marqués d\'une * sont obligatoires.';
    }
    if (mb_strlen($nom) > 50 || mb_strlen($prenom) > 50 || mb_strlen($email) > 100 || mb_strlen($telephone) > 20) {
        $erreurs[] = 'Un des champs est trop long.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse email n\'est pas valide.';
    }
    if (mb_strlen($motDePasse) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($motDePasse !== $confirmation) {
        $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
    }

    // 3. Vérifier que l'email n'est pas déjà utilisé
    $client = new Client();
    if (empty($erreurs) && $client->emailExiste($email)) {
        $erreurs[] = 'Un compte existe déjà avec cette adresse email.';
    }

    // 4. Tout est correct : on enregistre le client
    if (empty($erreurs)) {
        $client->creer($nom, $prenom, $email, $telephone, $motDePasse);
        $succes = true;

        // On vide le formulaire
        $nom = '';
        $prenom = '';
        $email = '';
        $telephone = '';
    }
}

// 5. Afficher la vue
require 'vues/inscription.php';
