<?php
// =============================================================
// Classe Agence : lit les agences HD Transit (Paris, Conakry...)
// =============================================================

require_once __DIR__ . '/Database.php';

class Agence
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Renvoie toutes les agences
    public function lister(): array
    {
        $requete = $this->pdo->query('SELECT * FROM agence ORDER BY pays, ville');

        return $requete->fetchAll();
    }

    // Renvoie une agence grâce à son id, ou null si elle n'existe pas
    public function trouver(int $idAgence): ?array
    {
        $requete = $this->pdo->prepare('SELECT * FROM agence WHERE id_agence = :id');
        $requete->execute(['id' => $idAgence]);
        $agence = $requete->fetch();

        if ($agence === false) {
            return null;
        }
        return $agence;
    }
}
