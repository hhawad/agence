<?php
// =============================================================
// Gestion de la session : à inclure au début de chaque contrôleur
// =============================================================

require_once __DIR__ . '/../config/database.php';

session_start();

// Indique si un client est connecté
function clientConnecte(): bool
{
    return isset($_SESSION['id_client']);
}

// Bloque l'accès à la page si aucun client n'est connecté
function exigerClientConnecte(): void
{
    if (!clientConnecte()) {
        header('Location: ' . URL_SITE . '/connexion.php');
        exit;
    }
}
