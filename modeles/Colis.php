<?php
// =============================================================
// Classe Colis : gère les colis (envois) dans la base de données
// =============================================================

require_once __DIR__ . '/Database.php';

class Colis
{
    // Identifiant du statut « Créé » dans la table statut
    private const STATUT_CREE = 1;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Crée un colis et son premier historique, puis renvoie son numéro de suivi
    public function creer(
        int $idClient,
        int $idDestinataire,
        int $idAgenceDepart,
        int $idAgenceArrivee,
        string $description,
        ?float $valeurDeclaree,
        float $poidsEstime
    ): string {
        $numeroSuivi = $this->genererNumeroSuivi();

        // Les deux INSERT doivent réussir ensemble, ou pas du tout
        $this->pdo->beginTransaction();
        try {
            $requete = $this->pdo->prepare(
                'INSERT INTO colis (numero_suivi, description_contenu, valeur_declaree, poids_estime,
                                    id_client, id_destinataire, id_agence_depart, id_agence_arrivee, id_statut)
                 VALUES (:numero, :description, :valeur, :poids,
                         :id_client, :id_destinataire, :id_depart, :id_arrivee, :id_statut)'
            );
            $requete->execute([
                'numero'          => $numeroSuivi,
                'description'     => $description,
                'valeur'          => $valeurDeclaree,
                'poids'           => $poidsEstime,
                'id_client'       => $idClient,
                'id_destinataire' => $idDestinataire,
                'id_depart'       => $idAgenceDepart,
                'id_arrivee'      => $idAgenceArrivee,
                'id_statut'       => self::STATUT_CREE,
            ]);

            $idColis = (int) $this->pdo->lastInsertId();

            // Première ligne de l'historique : le colis est créé par le client (pas d'agent)
            $requete = $this->pdo->prepare(
                'INSERT INTO historique_statut (id_colis, id_statut, id_agent, commentaire)
                 VALUES (:id_colis, :id_statut, NULL, :commentaire)'
            );
            $requete->execute([
                'id_colis'    => $idColis,
                'id_statut'   => self::STATUT_CREE,
                'commentaire' => 'Envoi créé en ligne par le client',
            ]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $numeroSuivi;
    }

    // Fabrique un numéro de suivi unique, par exemple HD2026-7K3P9Q
    private function genererNumeroSuivi(): string
    {
        // Sans 0, O, 1 et I, qui se confondent facilement
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $numero = 'HD' . date('Y') . '-';
            for ($i = 0; $i < 6; $i++) {
                $numero .= $caracteres[random_int(0, strlen($caracteres) - 1)];
            }
        } while ($this->numeroExiste($numero));

        return $numero;
    }

    // Indique si un numéro de suivi est déjà utilisé
    private function numeroExiste(string $numero): bool
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM colis WHERE numero_suivi = :numero');
        $requete->execute(['numero' => $numero]);

        return $requete->fetchColumn() > 0;
    }
}
