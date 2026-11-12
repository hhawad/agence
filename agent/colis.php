<?php
// =============================================================
// Contrôleur : fiche d'un colis dans l'espace agent
// L'agent y pèse le colis, encaisse le paiement et change le statut.
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Colis.php';
require_once __DIR__ . '/../modeles/Tarif.php';

exigerAgentConnecte();

$idAgent = (int) $_SESSION['id_agent'];
$idAgenceAgent = (int) $_SESSION['id_agence'];
$estAdmin = $_SESSION['role'] === 'admin';

$modeleColis = new Colis();
$modeleTarif = new Tarif();

$numero = strtoupper(trim($_GET['numero'] ?? ''));
$colis = $modeleColis->trouverPourAgent($numero);

$erreurs = [];
$message = '';
if (($_GET['succes'] ?? '') === '1') {
    $message = 'Le colis a bien été mis à jour.';
}

if ($colis !== null) {
    $idColis = (int) $colis['id_colis'];
    $statut = (int) $colis['id_statut'];

    // Droits : l'administrateur peut tout faire ; l'employé agit seulement
    // au départ si c'est son agence de départ, à l'arrivée si c'est son agence d'arrivée
    $agitAuDepart = $estAdmin || $idAgenceAgent === (int) $colis['id_agence_depart'];
    $agitALArrivee = $estAdmin || $idAgenceAgent === (int) $colis['id_agence_arrivee'];

    // Le prix au kilo du trajet, pour la pesée
    $prixKg = $modeleTarif->prixParKg($colis['pays_depart'], $colis['pays_arrivee']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $reussi = false;

        if ($action === 'valider' && $agitAuDepart) {
            $poids = str_replace(',', '.', trim($_POST['poids_reel'] ?? ''));
            $estPaye = isset($_POST['est_paye']);

            if (!is_numeric($poids) || $poids <= 0 || $poids > 9999.99) {
                $erreurs[] = 'Le poids réel doit être un nombre supérieur à 0.';
            } elseif ($prixKg === null) {
                $erreurs[] = 'Aucun tarif n\'est défini pour ce trajet.';
            } else {
                $reussi = $modeleColis->valider($idColis, (float) $poids, $prixKg, $estPaye, $idAgent);
            }
        } elseif ($action === 'transit' && $agitAuDepart) {
            $reussi = $modeleColis->changerStatut($idColis, Colis::STATUT_DEPOSE, Colis::STATUT_TRANSIT, $idAgent);
        } elseif ($action === 'arrivee' && $agitALArrivee) {
            $reussi = $modeleColis->changerStatut($idColis, Colis::STATUT_TRANSIT, Colis::STATUT_ARRIVE, $idAgent);
        } elseif ($action === 'retrait' && $agitALArrivee) {
            $reussi = $modeleColis->changerStatut($idColis, Colis::STATUT_ARRIVE, Colis::STATUT_RETIRE, $idAgent);
        } elseif ($action === 'payer' && ($agitAuDepart || $agitALArrivee)) {
            $reussi = $modeleColis->marquerPaye($idColis);
        } else {
            $erreurs[] = 'Vous n\'avez pas le droit de faire cette action sur ce colis.';
        }

        if ($reussi) {
            header('Location: colis.php?numero=' . urlencode($numero) . '&succes=1');
            exit;
        }
        if (empty($erreurs)) {
            $erreurs[] = 'L\'action n\'a pas pu être faite : le statut du colis a peut-être déjà changé.';
        }
    }

    $historique = $modeleColis->listerHistoriqueAgent($idColis);
}

require __DIR__ . '/../vues/agent/colis.php';
