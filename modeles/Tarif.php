<?php
// =============================================================
// Classe Tarif : lit le prix au kilo de chaque trajet
// =============================================================

require_once __DIR__ . '/Database.php';

class Tarif
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Renvoie le prix au kilo pour un trajet, ou null s'il n'y a pas de tarif
    public function prixParKg(string $paysDepart, string $paysArrivee): ?float
    {
        $requete = $this->pdo->prepare(
            'SELECT prix_kg FROM tarif WHERE pays_depart = :depart AND pays_arrivee = :arrivee'
        );
        $requete->execute(['depart' => $paysDepart, 'arrivee' => $paysArrivee]);
        $prix = $requete->fetchColumn();

        if ($prix === false) {
            return null;
        }
        return (float) $prix;
    }
}
