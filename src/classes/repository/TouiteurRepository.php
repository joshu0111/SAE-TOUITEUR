<?php

namespace touiteur\repository;

use PDO;
use Exception;
use PDOException;
use touiteur\touites\Touite;
use touiteur\touites\Evaluation;
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
            "SELECT t.id_touite AS id,
                    u.prenom AS auteur,
                    u.id_utilisateur AS handle,
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

    public function compterTouites(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM TOUITE")->fetchColumn();
    }
}