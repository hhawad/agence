<?php
// =============================================================
// Classe Colis : gère les colis (envois) dans la base de données
// =============================================================

require_once __DIR__ . '/Database.php';

class Colis
{
    // Identifiants des statuts (table statut)
    public const STATUT_CREE = 1;
    public const STATUT_DEPOSE = 2;
    public const STATUT_TRANSIT = 3;
    public const STATUT_ARRIVE = 4;
    public const STATUT_RETIRE = 5;
    public const STATUT_ANNULE = 6;

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

    // ---------------------------------------------------------
    //  Méthodes de l'espace agent
    // ---------------------------------------------------------

    // Colis à traiter : départs (Créé, Déposé) et arrivées (En transit, Arrivé).
    // Avec $idAgence = null (administrateur), on renvoie ceux de toutes les agences.
    public function listerATraiter(?int $idAgence): array
    {
        $sql = 'SELECT c.numero_suivi, c.date_enregistrement, c.id_statut,
                       s.libelle AS statut,
                       ad.ville AS ville_depart, aa.ville AS ville_arrivee
                FROM colis c
                JOIN statut s  ON s.id_statut = c.id_statut
                JOIN agence ad ON ad.id_agence = c.id_agence_depart
                JOIN agence aa ON aa.id_agence = c.id_agence_arrivee
                WHERE c.id_statut IN (1, 2, 3, 4)';
        $parametres = [];

        if ($idAgence !== null) {
            $sql .= ' AND ((c.id_agence_depart = :agence1 AND c.id_statut IN (1, 2))
                       OR (c.id_agence_arrivee = :agence2 AND c.id_statut IN (3, 4)))';
            $parametres = ['agence1' => $idAgence, 'agence2' => $idAgence];
        }
        $sql .= ' ORDER BY c.date_enregistrement';

        $requete = $this->pdo->prepare($sql);
        $requete->execute($parametres);

        return $requete->fetchAll();
    }

    // Toutes les informations d'un colis pour l'agent (y compris client et destinataire)
    public function trouverPourAgent(string $numeroSuivi): ?array
    {
        $requete = $this->pdo->prepare(
            'SELECT c.*,
                    s.libelle AS statut,
                    cl.nom AS nom_client, cl.prenom AS prenom_client, cl.telephone AS telephone_client,
                    d.nom AS nom_destinataire, d.prenom AS prenom_destinataire,
                    d.telephone AS telephone_destinataire, d.ville AS ville_destinataire,
                    ad.nom AS agence_depart, ad.pays AS pays_depart,
                    aa.nom AS agence_arrivee, aa.pays AS pays_arrivee
             FROM colis c
             JOIN statut s       ON s.id_statut = c.id_statut
             JOIN client cl      ON cl.id_client = c.id_client
             JOIN destinataire d ON d.id_destinataire = c.id_destinataire
             JOIN agence ad      ON ad.id_agence = c.id_agence_depart
             JOIN agence aa      ON aa.id_agence = c.id_agence_arrivee
             WHERE c.numero_suivi = :numero'
        );
        $requete->execute(['numero' => $numeroSuivi]);
        $colis = $requete->fetch();

        if ($colis === false) {
            return null;
        }
        return $colis;
    }

    // Historique complet pour l'agent : étape, date, agent qui l'a faite, commentaire
    public function listerHistoriqueAgent(int $idColis): array
    {
        $requete = $this->pdo->prepare(
            'SELECT s.libelle AS statut, h.date_changement, h.commentaire,
                    a.prenom AS prenom_agent, a.nom AS nom_agent
             FROM historique_statut h
             JOIN statut s     ON s.id_statut = h.id_statut
             LEFT JOIN agent a ON a.id_agent = h.id_agent
             WHERE h.id_colis = :id_colis
             ORDER BY h.date_changement, h.id_historique'
        );
        $requete->execute(['id_colis' => $idColis]);

        return $requete->fetchAll();
    }

    // Validation au guichet : pesée, prix définitif, paiement, statut « Déposé en agence »
    public function valider(int $idColis, float $poidsReel, float $prixKg, bool $estPaye, int $idAgent): bool
    {
        $montant = round($poidsReel * $prixKg, 2);

        $this->pdo->beginTransaction();
        try {
            $requete = $this->pdo->prepare(
                'UPDATE colis
                 SET poids_reel = :poids, prix_kg_applique = :prix_kg, montant = :montant,
                     est_paye = :paye, id_statut = :depose
                 WHERE id_colis = :id AND id_statut = :cree'
            );
            $requete->execute([
                'poids'   => $poidsReel,
                'prix_kg' => $prixKg,
                'montant' => $montant,
                'paye'    => $estPaye ? 1 : 0,
                'depose'  => self::STATUT_DEPOSE,
                'id'      => $idColis,
                'cree'    => self::STATUT_CREE,
            ]);

            // Aucune ligne modifiée : le colis n'est plus au statut « Créé »
            if ($requete->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $this->ajouterHistorique($idColis, self::STATUT_DEPOSE, $idAgent,
                'Colis pesé en agence : ' . number_format($poidsReel, 2, ',', ' ') . ' kg');

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return true;
    }

    // Fait passer un colis d'un statut au suivant (ex. Déposé → En transit)
    public function changerStatut(int $idColis, int $statutActuel, int $nouveauStatut, int $idAgent): bool
    {
        $this->pdo->beginTransaction();
        try {
            $requete = $this->pdo->prepare(
                'UPDATE colis SET id_statut = :nouveau WHERE id_colis = :id AND id_statut = :actuel'
            );
            $requete->execute([
                'nouveau' => $nouveauStatut,
                'id'      => $idColis,
                'actuel'  => $statutActuel,
            ]);

            if ($requete->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $this->ajouterHistorique($idColis, $nouveauStatut, $idAgent, null);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return true;
    }

    // Enregistre le paiement d'un colis déjà pesé
    public function marquerPaye(int $idColis): bool
    {
        $requete = $this->pdo->prepare(
            'UPDATE colis SET est_paye = 1 WHERE id_colis = :id AND montant IS NOT NULL AND est_paye = 0'
        );
        $requete->execute(['id' => $idColis]);

        return $requete->rowCount() > 0;
    }

    // Ajoute une ligne dans l'historique (utilisée à l'intérieur d'une transaction)
    private function ajouterHistorique(int $idColis, int $idStatut, ?int $idAgent, ?string $commentaire): void
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO historique_statut (id_colis, id_statut, id_agent, commentaire)
             VALUES (:id_colis, :id_statut, :id_agent, :commentaire)'
        );
        $requete->execute([
            'id_colis'    => $idColis,
            'id_statut'   => $idStatut,
            'id_agent'    => $idAgent,
            'commentaire' => $commentaire,
        ]);
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
