CREATE DATABASE IF NOT EXISTS touiteur CHARACTER SET utf8mb4;
USE touiteur;

-- Ordre inverse des dependances, pour pouvoir relancer le script
DROP TABLE IF EXISTS EVALUATION;
DROP TABLE IF EXISTS ABONNEMENT;
DROP TABLE IF EXISTS SUIVRE;
DROP TABLE IF EXISTS CONTENIR;
DROP TABLE IF EXISTS TOUITE;
DROP TABLE IF EXISTS IMAGE;
DROP TABLE IF EXISTS TAG;
DROP TABLE IF EXISTS UTILISATEUR;

CREATE TABLE UTILISATEUR (
    id_utilisateur  VARCHAR(50)  PRIMARY KEY,
    nom             VARCHAR(50)  NOT NULL,
    prenom          VARCHAR(50)  NOT NULL,
    mdp             VARCHAR(255) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    nb_touites      INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TAG (
    id_tag       INT AUTO_INCREMENT PRIMARY KEY,
    libelle      VARCHAR(50) NOT NULL UNIQUE,
    description  VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IMAGE (
    id_image        INT AUTO_INCREMENT PRIMARY KEY,
    description     VARCHAR(255) NOT NULL,
    chemin_fichier  VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE TOUITE (
    id_touite         INT AUTO_INCREMENT PRIMARY KEY,
    contenu           VARCHAR(235) NOT NULL,
    date_publication  DATETIME NOT NULL,
    note              INT NOT NULL DEFAULT 0,
    id_utilisateur    VARCHAR(50) NOT NULL,
    id_image          INT NULL,
    CONSTRAINT chk_touite_contenu CHECK (CHAR_LENGTH(contenu) <= 235),
    CONSTRAINT fk_touite_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES UTILISATEUR (id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_touite_image FOREIGN KEY (id_image)
        REFERENCES IMAGE (id_image) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE CONTENIR (
    id_touite  INT NOT NULL,
    id_tag     INT NOT NULL,
    PRIMARY KEY (id_touite, id_tag),
    CONSTRAINT fk_contenir_touite FOREIGN KEY (id_touite)
        REFERENCES TOUITE (id_touite) ON DELETE CASCADE,
    CONSTRAINT fk_contenir_tag FOREIGN KEY (id_tag)
        REFERENCES TAG (id_tag) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE SUIVRE (
    id_suiveur  VARCHAR(50) NOT NULL,
    id_suivi    VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_suiveur, id_suivi),
    CONSTRAINT chk_suivre_difference CHECK (id_suiveur <> id_suivi),
    CONSTRAINT fk_suivre_suiveur FOREIGN KEY (id_suiveur)
        REFERENCES UTILISATEUR (id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_suivre_suivi FOREIGN KEY (id_suivi)
        REFERENCES UTILISATEUR (id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ABONNEMENT (
    id_utilisateur   VARCHAR(50) NOT NULL,
    id_tag           INT NOT NULL,
    date_abonnement  DATETIME NULL,
    PRIMARY KEY (id_utilisateur, id_tag),
    CONSTRAINT fk_abonnement_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES UTILISATEUR (id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_abonnement_tag FOREIGN KEY (id_tag)
        REFERENCES TAG (id_tag) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Evaluation d'un touite : +1 (like) ou -1 (dislike), une seule fois
-- par utilisateur et par touite (garanti par la cle primaire).
CREATE TABLE EVALUATION (
    id_utilisateur   VARCHAR(50) NOT NULL,
    id_touite        INT         NOT NULL,
    valeur           TINYINT     NOT NULL,
    date_evaluation  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_touite),
    CONSTRAINT chk_evaluation_valeur CHECK (valeur IN (-1, 1)),
    CONSTRAINT fk_evaluation_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES UTILISATEUR (id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_evaluation_touite FOREIGN KEY (id_touite)
        REFERENCES TOUITE (id_touite) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Triggers : maintiennent TOUITE.note a jour
DELIMITER //

CREATE TRIGGER trg_evaluation_insert
AFTER INSERT ON EVALUATION
FOR EACH ROW
BEGIN
    UPDATE TOUITE SET note = note + NEW.valeur
    WHERE id_touite = NEW.id_touite;
END//

CREATE TRIGGER trg_evaluation_delete
AFTER DELETE ON EVALUATION
FOR EACH ROW
BEGIN
    UPDATE TOUITE SET note = note - OLD.valeur
    WHERE id_touite = OLD.id_touite;
END//

DELIMITER ;

-- Jeu d'essai minimal (utilisateur utilise par index.php)
INSERT INTO UTILISATEUR (id_utilisateur, nom, prenom, mdp, email)
VALUES ('josh_r', 'R', 'Josh', 'changeme', 'josh@example.com');