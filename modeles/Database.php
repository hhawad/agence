<?php
// =============================================================
// Classe Database : gère la connexion à la base MySQL avec PDO
// =============================================================

require_once __DIR__ . '/../config/database.php';

class Database
{
    // La connexion est gardée ici pour être réutilisée (null au départ)
    private static ?PDO $connexion = null;

    // Renvoie la connexion à la base (la crée si elle n'existe pas encore)
    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=utf8mb4';
                self::$connexion = new PDO($dsn, DB_UTILISATEUR, DB_MOT_DE_PASSE);

                // En cas d'erreur SQL, PDO lance une exception
                self::$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Les résultats seront des tableaux avec le nom des colonnes
                self::$connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }
        return self::$connexion;
    }
}
