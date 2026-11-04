<?php

namespace touiteur\repository;

use PDO;
use Exception;
use touiteur\touites\Touite;

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

    public function insererTouite(Touite $t, int $idUser): int
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
    public function getTouites(int $limit, int $offset): array
    {
        $st = $this->pdo->prepare(
            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    u.id_utilisateur as  pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y %H:%i') AS date,
                    t.contenu AS contenu
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             ORDER BY t.date_publication DESC, t.id_touite DESC
             LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':limite', $limit, PDO::PARAM_INT);
        $st->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
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


    }
