<?php

namespace touiteur\repository;

use PDO;
use Exception;
use PDOException;
use touiteur\exceptions\IncorrectPassword;
use touiteur\exceptions\PseudoNotFound;
use touiteur\touites\Touite;
use touiteur\touites\Evaluation;
use touiter\exceptions;
class TouiteurRepository
{
    private PDO $pdo;
    private static ?TouiteurRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['dbname']};charset=utf8";
        $this->pdo = new PDO($dsn, $conf['username'], $conf['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Impossible de lire le fichier : $file");
        }
        self::$config = $conf;
    }

    public static function getInstance(): TouiteurRepository
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                throw new Exception("Appelez setConfig() d'abord.");
            }
            self::$instance = new self(self::$config);
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function insererTouite(Touite $t, string $idUser): int
    {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO TOUITE (contenu, date_publication, id_utilisateur)
                 VALUES (:contenu, NOW(), :user)"
            );
            $st->execute([
                ':contenu' => $t->get_contenu(),
                ':user'    => $idUser,
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $this->pdo->prepare(
                "UPDATE UTILISATEUR SET nb_touites = nb_touites + 1
                 WHERE id_utilisateur = :user"
            )->execute([':user' => $idUser]);

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Renvoie les touites du plus recent au plus ancien, sous la forme attendue par le template touite
     */
    public function getTouites(int $limit, int $offset, ?string $idUser = null): array   // MODIFIÉ (3e parametre)
    {
        // ma_note = evaluation de l'utilisateur connecte (1, -1 ou NULL s'il n'a pas encore evalue)
        $st = $this->pdo->prepare(

            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    t.id_touite,
                    u.id_utilisateur as  pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y %H:%i') AS date,
                    t.contenu AS contenu,
                    t.note AS note,
                    e.valeur AS ma_note
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN EVALUATION e ON e.id_touite = t.id_touite AND e.id_utilisateur = :user
             ORDER BY t.date_publication DESC, t.id_touite DESC
             LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':user', $idUser);
        $st->bindValue(':limite', $limit, PDO::PARAM_INT);
        $st->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Enregistre le like (+1) ou le dislike (-1) d'un utilisateur sur un touite.     // NOUVEAU
     * La note du touite est mise a jour par le trigger trg_evaluation_insert.
     * @return bool true si l'evaluation est enregistree, false si l'utilisateur
     *              a deja evalue ce touite (ou si le touite n'existe pas)
     */
    public function evaluerTouite(Evaluation $e): bool
    {
        try {
            $this->pdo->prepare(
                "INSERT INTO EVALUATION (id_utilisateur, id_touite, valeur)
                 VALUES (:user, :touite, :valeur)"
            )->execute([
                ':user'   => $e->getIdUser(),
                ':touite' => $e->getIdTouite(),
                ':valeur' => $e->getValeur(),
            ]);
            return true;
        } catch (PDOException $ex) {
            // 23000 = violation de contrainte (cle primaire en double ou touite inexistant)
            if ($ex->getCode() === '23000') {
                return false;
            }
            throw $ex;
        }
    }


    /**
     * Methode qui permet de compter les touites
     */

    public function compterTouites(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM TOUITE")->fetchColumn();
    }

    public function compterTouitesFiltreParTag(string $tag): int {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM CONTENIR c
                INNER JOIN TAG t ON t.id_tag = c.id_tag
                WHERE t.libelle = :libelle"
        );
        $st->bindValue(':libelle', $tag);
        $st->execute();
        return (int) $st->fetchColumn();
    }

    /**
     * Methode qui retourne les touites filtre par tag
     */
    public function getTouitesParTag(string $tag, int $limit, int $offset) : array {

        $st = $this->pdo->prepare(
            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    u.id_utilisateur AS pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y %H:%i') AS date,
                    t.contenu AS contenu
            FROM TOUITE t
            JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
            INNER JOIN CONTENIR c ON t.id_touite = c.id_touite
            INNER JOIN TAG ON c.id_tag = TAG.id_tag
            WHERE TAG.libelle = :libelle
            ORDER BY t.date_publication DESC, t.id_touite DESC;
            LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':libelle', $tag);
        $st->bindValue(':limite', $limit, PDO::PARAM_INT);
        $st->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function getFollows() : array {
        return $this->pdo->query("SELECT concat(nom, ' ', prenom) as nom,id_utilisateur as pseudo FROM UTILISATEUR")->fetchAll();

    }

    public function getTags() : array  {
        return $this->pdo->query(
                "SELECT t.libelle, COUNT(c.id_touite) AS nb
                 FROM TAG t
                 LEFT JOIN CONTENIR c ON c.id_tag = t.id_tag
                 GROUP BY t.id_tag, t.libelle
                 ORDER BY t.libelle"
            )->fetchAll();
    }

     /**
     * Utilisateur par pseudo, ou null s'il n'existe pas
     */
    public function getUtilisateur(string $pseudo): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT id_utilisateur, nom, prenom FROM UTILISATEUR WHERE id_utilisateur = :pseudo"
        );
        $st->execute([':pseudo' => $pseudo]);
        return $st->fetch() ?: null;
    }

    public function compterTouitesParUtilisateur(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM TOUITE WHERE id_utilisateur = :p", $pseudo);
    }

    public function compterAbonnes(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM SUIVRE WHERE id_suivi = :p", $pseudo);
    }

    public function compterAbonnements(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM SUIVRE WHERE id_suiveur = :p", $pseudo);
    }

    private function compterPour(string $sql, string $pseudo): int
    {
        $st = $this->pdo->prepare($sql);
        $st->execute([':p' => $pseudo]);
        return (int) $st->fetchColumn();
    }

    /**
     * true si $suiveur suit $suivi
     */
    public function suitUtilisateur(string $suiveur, string $suivi): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM SUIVRE WHERE id_suiveur = :suiveur AND id_suivi = :suivi"
        );
        $st->execute([':suiveur' => $suiveur, ':suivi' => $suivi]);
        return $st->fetchColumn() !== false;
    }

    /**
     * Utilisateurs suivis par $pseudo, au format de la sidebar (nom, pseudo)
     */
    public function getAbonnements(string $pseudo): array
    {
        $st = $this->pdo->prepare(
            "SELECT CONCAT(u.prenom, ' ', u.nom) AS nom, u.id_utilisateur AS pseudo
             FROM SUIVRE s
             JOIN UTILISATEUR u ON u.id_utilisateur = s.id_suivi
             WHERE s.id_suiveur = :p
             ORDER BY u.prenom, u.nom"
        );
        $st->execute([':p' => $pseudo]);
        return $st->fetchAll();
    }

    /**
     * Tags utilises par un utilisateur, du plus frequent au moins frequent
     */
    public function getTagsParUtilisateur(string $pseudo): array
    {
        $st = $this->pdo->prepare(
            "SELECT tg.libelle, COUNT(*) AS nb
             FROM TOUITE t
             JOIN CONTENIR c ON c.id_touite = t.id_touite
             JOIN TAG tg ON tg.id_tag = c.id_tag
             WHERE t.id_utilisateur = :p
             GROUP BY tg.id_tag, tg.libelle
             ORDER BY nb DESC, tg.libelle"
        );
        $st->execute([':p' => $pseudo]);
        return $st->fetchAll();
    }

    /**
     * Touites d'un utilisateur, memes colonnes que getTouites()
     * $idUser = utilisateur connecte (pour ma_note), peut etre null
     */
    public function getTouitesParUtilisateur(string $pseudo, int $limit, int $offset, ?string $idUser = null): array
    {
        $st = $this->pdo->prepare(
            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    t.id_touite,
                    u.id_utilisateur AS pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y %H:%i') AS date,
                    t.contenu AS contenu,
                    t.note AS note,
                    e.valeur AS ma_note
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN EVALUATION e ON e.id_touite = t.id_touite AND e.id_utilisateur = :moi
             WHERE t.id_utilisateur = :pseudo
             ORDER BY t.date_publication DESC, t.id_touite DESC
             LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':moi', $idUser);
        $st->bindValue(':pseudo', $pseudo);
        $st->bindValue(':limite', $limit, PDO::PARAM_INT);
        $st->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }


    public function connexionUser(string $pseudo, string $pass) : array {
        $st = $this->pdo->prepare(
                "SELECT id_utilisateur, prenom, nom, mdp
                 FROM UTILISATEUR
                 WHERE id_utilisateur = :user"
            );
            $st->execute([':user' => $pseudo]);
            $user = $st->fetch();

            // Si utilisateur inexistant
            if ($user === false) {
                throw new PseudoNotFound();
            }
            // Si password incorrect
            if (!password_verify($pass, $user['mdp'])) {
                throw new IncorrectPassword();
            }


            // On retire le password du tableau
            unset($user['mdp']);
            return $user;
    }

}
