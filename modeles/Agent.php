<?php
// =============================================================
// Classe Agent : gère le personnel (employés et administrateur)
// =============================================================

require_once __DIR__ . '/Database.php';

class Agent
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Renvoie le nombre d'agents enregistrés
    public function compter(): int
    {
        $requete = $this->pdo->query('SELECT COUNT(*) FROM agent');

        return (int) $requete->fetchColumn();
    }

    // Renvoie l'agent qui a cet identifiant, ou null s'il n'existe pas
    public function trouverParIdentifiant(string $identifiant): ?array
    {
        $requete = $this->pdo->prepare('SELECT * FROM agent WHERE identifiant = :identifiant');
        $requete->execute(['identifiant' => $identifiant]);
        $agent = $requete->fetch();

        if ($agent === false) {
            return null;
        }
        return $agent;
    }

    // Enregistre un nouvel agent (le mot de passe est haché avant l'enregistrement)
    public function creer(string $nom, string $prenom, string $identifiant, string $motDePasse, string $role, int $idAgence): void
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO agent (nom, prenom, identifiant, mot_de_passe, role, id_agence)
             VALUES (:nom, :prenom, :identifiant, :mot_de_passe, :role, :id_agence)'
        );
        $requete->execute([
            'nom'          => $nom,
            'prenom'       => $prenom,
            'identifiant'  => $identifiant,
            'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'role'         => $role,
            'id_agence'    => $idAgence,
        ]);
    }
}
