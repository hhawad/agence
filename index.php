<?php
// =============================================================
// Contrôleur : page d'accueil et suivi public d'un colis
// =============================================================

require_once 'includes/session.php';
require_once 'modeles/Colis.php';

// Le numéro tapé dans le formulaire (en majuscules, sans espaces autour)
$numero = strtoupper(trim($_GET['numero'] ?? ''));

$colis = null;
$historique = [];
$erreur = '';

if ($numero !== '') {
    if (mb_strlen($numero) > 20) {
        $erreur = 'Ce numéro de suivi n\'est pas valide.';
    } else {
        $modeleColis = new Colis();
        $colis = $modeleColis->trouverParNumeroSuivi($numero);

        if ($colis === null) {
            $erreur = 'Aucun colis ne correspond à ce numéro de suivi.';
        } else {
            $historique = $modeleColis->listerHistorique((int) $colis['id_colis']);
        }
    }
}

require 'vues/accueil.php';
