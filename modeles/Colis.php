<?php
// =============================================================
// Classe Colis : gère les colis (envois) dans la base de données
// =============================================================

require_once __DIR__ . '/Database.php';

class Colis
{
    // Identifiant du statut « Créé » dans la table statut
    private const STATUT_CREE = 1;
    // Identifiant du statut « Annulé »
    private const STATUT_ANNULE = 6;

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

    // Renvoie tous les colis d'un client, du plus récent au plus ancien,
    // avec le statut, le destinataire, les villes et le prix au kilo du trajet
    public function listerParClient(int $idClient): array
    {
        $requete = $this->pdo->prepare(
            'SELECT c.id_colis, c.numero_suivi, c.description_contenu, c.poids_estime,
                    c.montant, c.est_paye, c.date_enregistrement, c.id_statut,
                    s.libelle AS statut,
                    d.prenom AS prenom_destinataire, d.nom AS nom_destinataire,
                    ad.ville AS ville_depart, aa.ville AS ville_arrivee,
                    t.prix_kg
             FROM colis c
             JOIN statut s        ON s.id_statut = c.id_statut
             JOIN destinataire d  ON d.id_destinataire = c.id_destinataire
             JOIN agence ad       ON ad.id_agence = c.id_agence_depart
             JOIN agence aa       ON aa.id_agence = c.id_agence_arrivee
             LEFT JOIN tarif t    ON t.pays_depart = ad.pays AND t.pays_arrivee = aa.pays
             WHERE c.id_client = :id_client
             ORDER BY c.date_enregistrement DESC, c.id_colis DESC'
        );
        $requete->execute(['id_client' => $idClient]);

        return $requete->fetchAll();
    }

    // Annule un colis du client, seulement s'il est encore au statut « Créé »
    // Renvoie true si le colis a bien été annulé
    public function annulerParClient(int $idColis, int $idClient): bool
    {
        $this->pdo->beginTransaction();
        try {
            $requete = $this->pdo->prepare(
                'UPDATE colis SET id_statut = :annule
                 WHERE id_colis = :id AND id_client = :id_client AND id_statut = :cree'
            );
            $requete->execute([
                'annule'    => self::STATUT_ANNULE,
                'id'        => $idColis,
                'id_client' => $idClient,
                'cree'      => self::STATUT_CREE,
            ]);

            // Aucune ligne modifiée : colis introuvable, pas à lui, ou déjà déposé
            if ($requete->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $requete = $this->pdo->prepare(
                'INSERT INTO historique_statut (id_colis, id_statut, id_agent, commentaire)
                 VALUES (:id_colis, :id_statut, NULL, :commentaire)'
            );
            $requete->execute([
                'id_colis'    => $idColis,
                'id_statut'   => self::STATUT_ANNULE,
                'commentaire' => 'Envoi annulé par le client',
            ]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return true;
    }

    // Suivi public : renvoie les informations NON personnelles d'un colis, ou null
    public function trouverParNumeroSuivi(string $numeroSuivi): ?array
    {
        $requete = $this->pdo->prepare(
            'SELECT c.id_colis, c.numero_suivi, c.date_enregistrement,
                    s.libelle AS statut,
                    ad.ville AS ville_depart, ad.pays AS pays_depart,
                    aa.ville AS ville_arrivee, aa.pays AS pays_arrivee
             FROM colis c
             JOIN statut s  ON s.id_statut = c.id_statut
             JOIN agence ad ON ad.id_agence = c.id_agence_depart
             JOIN agence aa ON aa.id_agence = c.id_agence_arrivee
             WHERE c.numero_suivi = :numero'
        );
        $requete->execute(['numero' => $numeroSuivi]);
        $colis = $requete->fetch();

        if ($colis === false) {
            return null;
        }
        return $colis;
    }

    // Renvoie les étapes du colis (statut + date), de la plus ancienne à la plus récente
    public function listerHistorique(int $idColis): array
    {
        $requete = $this->pdo->prepare(
            'SELECT s.libelle AS statut, h.date_changement
             FROM historique_statut h
             JOIN statut s ON s.id_statut = h.id_statut
             WHERE h.id_colis = :id_colis
             ORDER BY h.date_changement, h.id_historique'
        );
        $requete->execute(['id_colis' => $idColis]);

        return $requete->fetchAll();
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
