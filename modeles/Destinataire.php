<?php
// =============================================================
// Classe Destinataire : gère le carnet d'adresses des clients
// =============================================================

require_once __DIR__ . '/Database.php';

class Destinataire
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Renvoie tous les destinataires d'un client, triés par nom
    public function listerParClient(int $idClient): array
    {
        $requete = $this->pdo->prepare(
            'SELECT * FROM destinataire WHERE id_client = :id_client ORDER BY nom, prenom'
        );
        $requete->execute(['id_client' => $idClient]);

        return $requete->fetchAll();
    }

    // Ajoute un destinataire dans le carnet d'un client
    public function ajouter(int $idClient, string $nom, string $prenom, string $telephone, string $ville, string $pays): void
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO destinataire (id_client, nom, prenom, telephone, ville, pays)
             VALUES (:id_client, :nom, :prenom, :telephone, :ville, :pays)'
        );
        $requete->execute([
            'id_client' => $idClient,
            'nom'       => $nom,
            'prenom'    => $prenom,
            'telephone' => $telephone,
            'ville'     => $ville,
            'pays'      => $pays,
        ]);
    }

    // Indique si un destinataire est déjà utilisé par au moins un colis
    public function estUtilise(int $idDestinataire): bool
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM colis WHERE id_destinataire = :id');
        $requete->execute(['id' => $idDestinataire]);

        return $requete->fetchColumn() > 0;
    }

    // Supprime un destinataire, seulement s'il appartient bien à ce client
    // Renvoie true si une ligne a été supprimée
    public function supprimer(int $idDestinataire, int $idClient): bool
    {
        $requete = $this->pdo->prepare(
            'DELETE FROM destinataire WHERE id_destinataire = :id AND id_client = :id_client'
        );
        $requete->execute(['id' => $idDestinataire, 'id_client' => $idClient]);

        return $requete->rowCount() > 0;
    }
}
