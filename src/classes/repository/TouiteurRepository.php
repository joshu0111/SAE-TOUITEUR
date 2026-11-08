<?php

namespace touiteur\repository;

use PDO;
use Exception;
use touiteur\exceptions\{IncorrectPassword, AlreadyHaveAccount, PseudoAlreadyExists, PseudoNotFound, AuthException};
use touiteur\touites\{Touite, Evaluation};


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

    /**
     * Methode qui permet d'inserer un touites avec son id d'image dans la bdd
     */
    public function insererTouite(Touite $t, string $idUser, ?int $idImage = null): int
    {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "INSERT INTO TOUITE (contenu, date_publication, id_utilisateur, id_image)
                 VALUES (:contenu, NOW(), :user, :image)"
            );
            $st->execute([
                ':contenu' => $t->get_contenu(),
                ':user'    => $idUser,
                ':image'   => $idImage,
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $this->associerTags($id, $t->getTags());

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
     * Methode qui permet d'associer les tags fournis en prarametres a un touites
    */
    private function associerTags(int $idTouite, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        // Cree le tag s'il n'existe pas, sinon recupere son id
        $creerTag = $this->pdo->prepare(
            "INSERT INTO TAG (libelle) VALUES (:libelle)
             ON DUPLICATE KEY UPDATE id_tag = LAST_INSERT_ID(id_tag)"
        );

        // On lie les tags au touite
        $lier = $this->pdo->prepare(
            "INSERT INTO CONTENIR (id_touite, id_tag) VALUES (:touite, :tag)"
        );

        // On recupere/ajoute le tag et le lie a son touite sur tout les tags fournis en parametres
        foreach ($tags as $libelle) {
            $creerTag->execute([':libelle' => $libelle]);
            $idTag = (int) $this->pdo->lastInsertId();

            $lier->execute([':touite' => $idTouite, ':tag' => $idTag]);
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
                    e.valeur AS ma_note, i.chemin_fichier AS image,
                    i.description AS image_alt
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN IMAGE i ON i.id_image = t.id_image
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
    /**
     * Vote d'un utilisateur sur un touite :
     * - pas encore voté     -> ajout
     * - même vote re-cliqué -> retrait
     * - vote opposé         -> changement d'avis
     * La note est mise à jour par les triggers.
     */
    public function evaluerTouite(Evaluation $e): void
    {
        $params = [
            ':user'   => $e->getIdUser(),
            ':touite' => $e->getIdTouite(),
            ':valeur' => $e->getValeur(),
        ];

        // Tente le retrait si le vote est identique, sinon ajoute ou change
        $retire = $this->pdo->prepare(
            "DELETE FROM EVALUATION
             WHERE id_utilisateur = :user AND id_touite = :touite AND valeur = :valeur"
        );
        $retire->execute($params);

        if ($retire->rowCount() === 0) {
            $this->pdo->prepare(
                "INSERT INTO EVALUATION (id_utilisateur, id_touite, valeur)
                 VALUES (:user, :touite, :valeur)
                 ON DUPLICATE KEY UPDATE valeur = VALUES(valeur), date_evaluation = NOW()"
            )->execute($params);
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
    public function getTouitesParTag(string $tag, int $limit, int $offset, ?string $idUser = null): array
    {
        $st = $this->pdo->prepare(
            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    t.id_touite,
                    u.id_utilisateur AS pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y %H:%i') AS date,
                    t.contenu AS contenu,
                    t.note AS note,
                    e.valeur AS ma_note,
                    i.chemin_fichier AS image,
                    i.description    AS image_alt
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN IMAGE i ON i.id_image = t.id_image
             INNER JOIN CONTENIR c ON t.id_touite = c.id_touite
             INNER JOIN TAG ON c.id_tag = TAG.id_tag
             LEFT JOIN EVALUATION e ON e.id_touite = t.id_touite AND e.id_utilisateur = :moi
             WHERE TAG.libelle = :libelle
             ORDER BY t.date_publication DESC, t.id_touite DESC
             LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':moi', $idUser);
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
                    e.valeur AS ma_note,
                    i.chemin_fichier AS image,
                    i.description    AS image_alt
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN IMAGE i ON i.id_image = t.id_image
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


    public function inscriptionUser(string $pseudo, string $nom, string $prenom, string $email, string $pass): void {
        // Le pseudo deja pris
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM UTILISATEUR WHERE id_utilisateur = :pseudo");
        $st->execute([':pseudo' => $pseudo]);
        if ($st->fetchColumn() > 0) {
            throw new PseudoAlreadyExists();
        }

        // L'email deja associe a un compte
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM UTILISATEUR WHERE email = :email");
        $st->execute([':email' => $email]);
        if ($st->fetchColumn() > 0) {
            throw new AlreadyHaveAccount();
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 12]);

        $st = $this->pdo->prepare(
            "INSERT INTO UTILISATEUR (id_utilisateur, nom, prenom, email, mdp)
             VALUES (:pseudo, :nom, :prenom, :email, :mdp)"
        );
        $st->execute([
            ':pseudo' => $pseudo,
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':mdp'    => $hash,
        ]);
    }

    /**
     * Methode qui permet d'inserer une image dans le base de donnee
     */
    public function insererImage(string $description, string $chemin): int
    {
        $this->pdo->prepare(
            "INSERT INTO IMAGE (description, chemin_fichier) VALUES (:description, :chemin)"
        )->execute([':description' => $description, ':chemin' => $chemin]);

        return (int) $this->pdo->lastInsertId();
    }
}
