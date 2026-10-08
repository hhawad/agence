<?php
// =============================================================
// Contrôleur : liste des colis du client (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Colis.php';

exigerClientConnecte();

$idClient = $_SESSION['id_client'];
$modeleColis = new Colis();

$erreurs = [];
$message = '';
if (($_GET['succes'] ?? '') === 'annulation') {
    $message = 'Votre envoi a bien été annulé.';
}

// Annulation d'un colis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
    $idColis = (int) ($_POST['id_colis'] ?? 0);

    if ($modeleColis->annulerParClient($idColis, $idClient)) {
        header('Location: mes-colis.php?succes=annulation');
        exit;
    }
    $erreurs[] = 'Ce colis ne peut pas être annulé : il a peut-être déjà été déposé en agence.';
}

$listeColis = $modeleColis->listerParClient($idClient);

require __DIR__ . '/../vues/client/mes-colis.php';
