<?php
// =============================================================
// Contrôleur : création d'un colis par le client (page protégée)
// =============================================================

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../modeles/Destinataire.php';
require_once __DIR__ . '/../modeles/Agence.php';
require_once __DIR__ . '/../modeles/Tarif.php';
require_once __DIR__ . '/../modeles/Colis.php';

exigerClientConnecte();

$idClient = $_SESSION['id_client'];

$modeleDestinataire = new Destinataire();
$modeleAgence = new Agence();
$modeleTarif = new Tarif();
$modeleColis = new Colis();

// Listes pour les menus déroulants
$destinataires = $modeleDestinataire->listerParClient($idClient);
$agences = $modeleAgence->lister();

$erreurs = [];

// Valeurs du formulaire (vides au premier affichage)
$idDestinataire = 0;
$idAgenceDepart = 0;
$idAgenceArrivee = 0;
$description = '';
$poids = '';
$valeur = '';

// Confirmation après la création (message « flash » : affiché une seule fois)
$colisCree = $_SESSION['colis_cree'] ?? null;
unset($_SESSION['colis_cree']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupérer les données envoyées
    $idDestinataire = (int) ($_POST['id_destinataire'] ?? 0);
    $idAgenceDepart = (int) ($_POST['id_agence_depart'] ?? 0);
    $idAgenceArrivee = (int) ($_POST['id_agence_arrivee'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $poids = str_replace(',', '.', trim($_POST['poids'] ?? ''));
    $valeur = str_replace(',', '.', trim($_POST['valeur_declaree'] ?? ''));

    // 2. Vérifier le destinataire et les agences
    $destinataire = $modeleDestinataire->trouverPourClient($idDestinataire, $idClient);
    $agenceDepart = $modeleAgence->trouver($idAgenceDepart);
    $agenceArrivee = $modeleAgence->trouver($idAgenceArrivee);

    if ($destinataire === null) {
        $erreurs[] = 'Veuillez choisir un destinataire de votre carnet.';
    }
    if ($agenceDepart === null || $agenceArrivee === null) {
        $erreurs[] = 'Veuillez choisir l\'agence de départ et l\'agence d\'arrivée.';
    } elseif ($agenceDepart['pays'] === $agenceArrivee['pays']) {
        $erreurs[] = 'L\'agence de départ et l\'agence d\'arrivée doivent être dans deux pays différents.';
    } elseif ($destinataire !== null && $destinataire['pays'] !== $agenceArrivee['pays']) {
        $erreurs[] = 'Le destinataire doit habiter dans le pays de l\'agence d\'arrivée.';
    }

    // 3. Vérifier le contenu, le poids et la valeur
    if ($description === '' || mb_strlen($description) > 255) {
        $erreurs[] = 'Veuillez décrire le contenu du colis (255 caractères maximum).';
    }
    if (!is_numeric($poids) || $poids <= 0 || $poids > 9999.99) {
        $erreurs[] = 'Le poids doit être un nombre supérieur à 0.';
    }
    if ($valeur !== '' && (!is_numeric($valeur) || $valeur < 0 || $valeur > 999999.99)) {
        $erreurs[] = 'La valeur déclarée doit être un nombre positif.';
    }

    // 4. Trouver le prix au kilo du trajet
    if (empty($erreurs)) {
        $prixKg = $modeleTarif->prixParKg($agenceDepart['pays'], $agenceArrivee['pays']);
        if ($prixKg === null) {
            $erreurs[] = 'Aucun tarif n\'est défini pour ce trajet.';
        }
    }

    // 5. Tout est correct : on crée le colis
    if (empty($erreurs)) {
        $valeurDeclaree = $valeur === '' ? null : (float) $valeur;

        $numeroSuivi = $modeleColis->creer(
            $idClient,
            $idDestinataire,
            $idAgenceDepart,
            $idAgenceArrivee,
            $description,
            $valeurDeclaree,
            (float) $poids
        );

        // On garde les infos pour la page de confirmation, puis on redirige
        $_SESSION['colis_cree'] = [
            'numero_suivi' => $numeroSuivi,
            'prix_estime'  => round((float) $poids * $prixKg, 2),
        ];
        header('Location: nouveau-colis.php');
        exit;
    }
}

// 6. Afficher la vue
require __DIR__ . '/../vues/client/nouveau-colis.php';
