<?php
// =============================================================
// Classe Client : gère les clients dans la base de données
// =============================================================

require_once __DIR__ . '/Database.php';

class Client
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    // Vérifie si un compte existe déjà avec cet email
    public function emailExiste(string $email): bool
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM client WHERE email = :email');
        $requete->execute(['email' => $email]);

        return $requete->fetchColumn() > 0;
    }

    // Enregistre un nouveau client (le mot de passe est haché avant l'enregistrement)
    public function creer(string $nom, string $prenom, string $email, string $telephone, string $motDePasse): void
    {
        $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

        $requete = $this->pdo->prepare(
            'INSERT INTO client (nom, prenom, email, telephone, mot_de_passe)
             VALUES (:nom, :prenom, :email, :telephone, :mot_de_passe)'
        );
        $requete->execute([
            'nom'          => $nom,
            'prenom'       => $prenom,
            'email'        => $email,
            'telephone'    => $telephone,
            'mot_de_passe' => $motDePasseHache,
        ]);
    }
}
