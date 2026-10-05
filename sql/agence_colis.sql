-- =====================================================================
--  HD Transit - Base de données "agence_colis"
--  Projet BTS SIO SLAM : envoi de colis France <-> Guinée (Conakry)
--
--  Ce script SUPPRIME puis RECRÉE toute la base.
--  À exécuter dans phpMyAdmin : onglet "Importer" (ou onglet "SQL"
--  au niveau du serveur, sans avoir sélectionné de base).
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS agence_colis;
CREATE DATABASE agence_colis CHARACTER SET utf8mb4;
USE agence_colis;


-- ---------------------------------------------------------------------
-- Table statut : les étapes possibles d'un colis
-- ---------------------------------------------------------------------
CREATE TABLE statut (
  id_statut INT NOT NULL AUTO_INCREMENT,
  libelle   VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_statut),
  UNIQUE (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table agence : les lieux de dépôt et de retrait des colis
-- ---------------------------------------------------------------------
CREATE TABLE agence (
  id_agence INT NOT NULL AUTO_INCREMENT,
  nom       VARCHAR(100) NOT NULL,
  adresse   VARCHAR(255) NOT NULL,
  ville     VARCHAR(100) NOT NULL,
  pays      VARCHAR(50)  NOT NULL,
  telephone VARCHAR(20)  DEFAULT NULL,
  PRIMARY KEY (id_agence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table tarif : le prix au kilo pour chaque sens d'envoi
-- ---------------------------------------------------------------------
CREATE TABLE tarif (
  id_tarif     INT NOT NULL AUTO_INCREMENT,
  pays_depart  VARCHAR(50)  NOT NULL,
  pays_arrivee VARCHAR(50)  NOT NULL,
  prix_kg      DECIMAL(6,2) NOT NULL,
  PRIMARY KEY (id_tarif),
  UNIQUE (pays_depart, pays_arrivee),   -- un seul tarif par sens
  CHECK (prix_kg > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table client : les personnes qui envoient des colis (compte en ligne)
-- ---------------------------------------------------------------------
CREATE TABLE client (
  id_client        INT NOT NULL AUTO_INCREMENT,
  nom              VARCHAR(50)  NOT NULL,
  prenom           VARCHAR(50)  NOT NULL,
  email            VARCHAR(100) NOT NULL,
  mot_de_passe     VARCHAR(255) NOT NULL,   -- mot de passe haché (password_hash)
  telephone        VARCHAR(20)  NOT NULL,
  adresse          VARCHAR(255) DEFAULT NULL,
  date_inscription DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_client),
  UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table agent : le personnel (employés et administrateur)
-- ---------------------------------------------------------------------
CREATE TABLE agent (
  id_agent     INT NOT NULL AUTO_INCREMENT,
  nom          VARCHAR(50)  NOT NULL,
  prenom       VARCHAR(50)  NOT NULL,
  identifiant  VARCHAR(50)  NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,       -- mot de passe haché (password_hash)
  role         ENUM('employe', 'admin') NOT NULL DEFAULT 'employe',
  actif        BOOLEAN      NOT NULL DEFAULT 1,
  id_agence    INT          NOT NULL,
  PRIMARY KEY (id_agent),
  UNIQUE (identifiant),
  FOREIGN KEY (id_agence) REFERENCES agence (id_agence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table destinataire : le carnet d'adresses de chaque client
-- ---------------------------------------------------------------------
CREATE TABLE destinataire (
  id_destinataire INT NOT NULL AUTO_INCREMENT,
  id_client       INT          NOT NULL,
  nom             VARCHAR(50)  NOT NULL,
  prenom          VARCHAR(50)  NOT NULL,
  telephone       VARCHAR(20)  NOT NULL,
  ville           VARCHAR(100) NOT NULL,
  pays            VARCHAR(50)  NOT NULL,
  PRIMARY KEY (id_destinataire),
  FOREIGN KEY (id_client) REFERENCES client (id_client)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table colis : la table centrale (1 colis = 1 envoi = 1 numéro de suivi)
-- ---------------------------------------------------------------------
CREATE TABLE colis (
  id_colis            INT NOT NULL AUTO_INCREMENT,
  numero_suivi        VARCHAR(20)  NOT NULL,
  description_contenu VARCHAR(255) NOT NULL,
  valeur_declaree     DECIMAL(8,2) DEFAULT NULL,   -- facultative
  poids_estime        DECIMAL(6,2) NOT NULL,       -- saisi par le client
  poids_reel          DECIMAL(6,2) DEFAULT NULL,   -- saisi par l'agent à la pesée
  prix_kg_applique    DECIMAL(6,2) DEFAULT NULL,   -- tarif "figé" à la validation
  montant             DECIMAL(8,2) DEFAULT NULL,   -- prix définitif
  est_paye            BOOLEAN      NOT NULL DEFAULT 0,
  date_enregistrement DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_client           INT NOT NULL,
  id_destinataire     INT NOT NULL,
  id_agence_depart    INT NOT NULL,
  id_agence_arrivee   INT NOT NULL,
  id_statut           INT NOT NULL,
  PRIMARY KEY (id_colis),
  UNIQUE (numero_suivi),
  FOREIGN KEY (id_client)         REFERENCES client (id_client),
  FOREIGN KEY (id_destinataire)   REFERENCES destinataire (id_destinataire),
  FOREIGN KEY (id_agence_depart)  REFERENCES agence (id_agence),
  FOREIGN KEY (id_agence_arrivee) REFERENCES agence (id_agence),
  FOREIGN KEY (id_statut)         REFERENCES statut (id_statut),
  CHECK (poids_estime > 0),
  CHECK (poids_reel > 0),
  CHECK (id_agence_depart <> id_agence_arrivee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- Table historique_statut : le journal de tous les changements de statut
-- ---------------------------------------------------------------------
CREATE TABLE historique_statut (
  id_historique   INT NOT NULL AUTO_INCREMENT,
  id_colis        INT NOT NULL,
  id_statut       INT NOT NULL,
  id_agent        INT DEFAULT NULL,   -- NULL quand c'est le client qui agit (création)
  date_changement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  commentaire     VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_historique),
  FOREIGN KEY (id_colis)  REFERENCES colis (id_colis),
  FOREIGN KEY (id_statut) REFERENCES statut (id_statut),
  FOREIGN KEY (id_agent)  REFERENCES agent (id_agent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
--  Données de départ
-- =====================================================================

INSERT INTO statut (id_statut, libelle) VALUES
(1, 'Créé'),
(2, 'Déposé en agence'),
(3, 'En transit'),
(4, 'Arrivé à l''agence'),
(5, 'Retiré'),
(6, 'Annulé');

INSERT INTO agence (nom, adresse, ville, pays, telephone) VALUES
('HD Transit Paris',   '12 rue de la Paix',      'Paris',   'France', '01 23 45 67 89'),
('HD Transit Conakry', 'Avenue de la République', 'Conakry', 'Guinée', '+224 620 00 00 00');

-- Prix fictifs, modifiables plus tard par l'administrateur
INSERT INTO tarif (pays_depart, pays_arrivee, prix_kg) VALUES
('France', 'Guinée', 10.00),
('Guinée', 'France', 10.00);
