<?php
// =============================================================
// Contrôleur : carnet de destinataires du client (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Destinataire.php';

exigerClientConnecte();

$idClient = $_SESSION['id_client'];
$modeleDestinataire = new Destinataire();
$paysAutorises = ['France', 'Guinée'];

$erreurs = [];

// Valeurs du formulaire d'ajout (vides au premier affichage)
$nom = '';
$prenom = '';
$telephone = '';
$ville = '';
$pays = '';

// Message de confirmation après une redirection
$message = '';
if (($_GET['succes'] ?? '') === 'ajout') {
    $message = 'Le destinataire a bien été ajouté.';
}
if (($_GET['succes'] ?? '') === 'suppression') {
    $message = 'Le destinataire a bien été supprimé.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---------- Ajout d'un destinataire ----------
    if ($action === 'ajouter') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $pays = $_POST['pays'] ?? '';

        if ($nom === '' || $prenom === '' || $telephone === '' || $ville === '') {
            $erreurs[] = 'Tous les champs sont obligatoires.';
        }
        if (mb_strlen($nom) > 50 || mb_strlen($prenom) > 50 || mb_strlen($telephone) > 20 || mb_strlen($ville) > 100) {
            $erreurs[] = 'Un des champs est trop long.';
        }
        if (!in_array($pays, $paysAutorises, true)) {
            $erreurs[] = 'Le pays choisi n\'est pas valide.';
        }

        if (empty($erreurs)) {
            $modeleDestinataire->ajouter($idClient, $nom, $prenom, $telephone, $ville, $pays);
            header('Location: destinataires.php?succes=ajout');
            exit;
        }
    }

    // ---------- Suppression d'un destinataire ----------
    if ($action === 'supprimer') {
        $idDestinataire = (int) ($_POST['id_destinataire'] ?? 0);

        if ($modeleDestinataire->estUtilise($idDestinataire)) {
            $erreurs[] = 'Ce destinataire ne peut pas être supprimé, car il est lié à un colis.';
        } elseif ($modeleDestinataire->supprimer($idDestinataire, $idClient)) {
            header('Location: destinataires.php?succes=suppression');
            exit;
        } else {
            $erreurs[] = 'Destinataire introuvable.';
        }
    }
}

// Liste à afficher (toujours à jour)
$destinataires = $modeleDestinataire->listerParClient($idClient);

require __DIR__ . '/../vues/client/destinataires.php';
