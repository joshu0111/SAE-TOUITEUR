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
     * Utilisateurs qui ont au moins un abonne, tries par nombre d'abonnes croissant
     */
    public function getInfluenceurs(): array
    {
        return $this->pdo->query(
            "SELECT u.id_utilisateur AS pseudo,
                    CONCAT(u.prenom, ' ', u.nom) AS nom,
                    COUNT(s.id_suiveur) AS nb_abonnes
             FROM UTILISATEUR u
             JOIN SUIVRE s ON s.id_suivi = u.id_utilisateur
             GROUP BY u.id_utilisateur, u.prenom, u.nom
             ORDER BY nb_abonnes DESC, u.id_utilisateur ASC"
        )->fetchAll();
    }

    /**
     * Tags utilises dans au moins un touite, avec leur nombre de mentions, par ordre croissant
     */
    public function getTendances(): array
    {
        return $this->pdo->query(
            "SELECT tg.libelle, COUNT(c.id_touite) AS nb
             FROM TAG tg
             JOIN CONTENIR c ON c.id_tag = tg.id_tag
             GROUP BY tg.id_tag, tg.libelle
             ORDER BY nb DESC, tg.libelle ASC"
        )->fetchAll();
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
    public function getTouites(int $limit, int $offset, ?string $idUser = null): array
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
     * Enregistre le like (+1) ou le dislike (-1) d'un utilisateur sur un touite
     * La note du touite est mise a jour par le trigger trg_evaluation_insert
     * @return bool true = evaluation enregistree,
     *              false = si l'utilisateur a deja evaluer ou touite inexistant
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

    /**
     * Un touite complet (auteur, date, contenu, image, note), avec le vote de $idUser
     */
    public function getTouiteById(int $id, ?string $idUser = null): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT LOWER(CONCAT(u.prenom, ' ', u.nom)) AS auteur,
                    u.prenom, u.nom,
                    t.id_touite,
                    u.id_utilisateur AS pseudo,
                    DATE_FORMAT(t.date_publication, '%d/%m/%Y à %H:%i') AS date,
                    t.contenu AS contenu,
                    t.note AS note,
                    e.valeur AS ma_note,
                    i.chemin_fichier AS image,
                    i.description AS image_alt
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN IMAGE i ON i.id_image = t.id_image
             LEFT JOIN EVALUATION e ON e.id_touite = t.id_touite AND e.id_utilisateur = :moi
             WHERE t.id_touite = :id"
        );
        $st->bindValue(':moi', $idUser);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }

    /**
     * Tags utilises dans un touite, avec leur nombre de mentions
     */
    public function getTagsDuTouite(int $id): array
    {
        $st = $this->pdo->prepare(
            "SELECT tg.libelle, COUNT(c2.id_touite) AS nb
             FROM CONTENIR c
             JOIN TAG tg ON tg.id_tag = c.id_tag
             LEFT JOIN CONTENIR c2 ON c2.id_tag = tg.id_tag
             WHERE c.id_touite = :id
             GROUP BY tg.id_tag, tg.libelle
             ORDER BY tg.libelle"
        );
        $st->execute([':id' => $id]);
        return $st->fetchAll();
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

    /*
    * Recupere les tags (libelle) et le nombre de fois qu'il a ete utlise
    * */
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

    /* Nombre de touites par user */
    public function compterTouitesParUtilisateur(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM TOUITE WHERE id_utilisateur = :p", $pseudo);
    }

    /**
     * Nombre d'abonne a un user
     */
    public function compterAbonnes(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM SUIVRE WHERE id_suivi = :p", $pseudo);
    }

    /**
     * Nombre d'abonnes a un compte
     */
    public function compterAbonnements(string $pseudo): int
    {
        return $this->compterPour("SELECT COUNT(*) FROM SUIVRE WHERE id_suiveur = :p", $pseudo);
    }

    /** Methode privee appelee par compterAbonnes et  compterAbonnements */
    private function compterPour(string $sql, string $pseudo): int
    {
        $st = $this->pdo->prepare($sql);
        $st->execute([':p' => $pseudo]);
        return (int) $st->fetchColumn();
    }

    /**
     * True si $suiveur suit $suivi
     */
    public function suitUtilisateur(string $suiveur, string $suivi): bool
    {
        $st = $this->pdo->prepare(
            "SELECT 1 FROM SUIVRE WHERE id_suiveur = :suiveur AND id_suivi = :suivi"
        );
        $st->execute([':suiveur' => $suiveur, ':suivi' => $suivi]);
        return $st->fetchColumn() !== false;
    }

    /** Methode qui permet d'ajoute une persone qui suit  */
    public function suivreUtilisateur(string $suiveur, string $suivi): void
    {
        $this->pdo->prepare(
            "INSERT INTO SUIVRE (id_suiveur, id_suivi) VALUES (:suiveur, :suivi)
             ON DUPLICATE KEY UPDATE id_suivi = id_suivi"
        )->execute([':suiveur' => $suiveur, ':suivi' => $suivi]);
    }

    /**
     * Methode qui permet d'enlever une personne qui suit une autre
     */
    public function desabonner(string $suiveur, string $suivi): void
    {
        $this->pdo->prepare(
            "DELETE FROM SUIVRE WHERE id_suiveur = :suiveur AND id_suivi = :suivi"
        )->execute([':suiveur' => $suiveur, ':suivi' => $suivi]);
    }


    /**
     * Methode qui permet de recuperer tous les users avec pattern correspondant a recherche
     */
    public function getUtilisateurs(string $recherche, ?string $idUser): array{
        // Échappe les caractères joker de LIKE pour qu'ils soient traites comme du texte
        $motif = '%' . addcslashes($recherche, '%_\\') . '%';

        $st = $this->pdo->prepare(
            "SELECT u.id_utilisateur AS pseudo,
                    CONCAT(u.prenom, ' ', u.nom) AS nom,
                    (SELECT COUNT(*) FROM TOUITE t WHERE t.id_utilisateur = u.id_utilisateur) AS nb_touites,
                    (SELECT COUNT(*) FROM SUIVRE s WHERE s.id_suivi = u.id_utilisateur) AS nb_abonnes,
                    (SELECT COUNT(*) FROM SUIVRE s WHERE s.id_suiveur = u.id_utilisateur)     AS nb_abonnements,
                    EXISTS(SELECT 1 FROM SUIVRE s
                           WHERE s.id_suiveur = :moi AND s.id_suivi = u.id_utilisateur)       AS suivi
             FROM UTILISATEUR u
             WHERE u.id_utilisateur LIKE :q1 OR CONCAT(u.prenom, ' ', u.nom) LIKE :q2
             ORDER BY u.id_utilisateur"
        );
        $st->bindValue(':moi', $idUser);
        $st->bindValue(':q1', $motif);
        $st->bindValue(':q2', $motif);
        $st->execute();
        return $st->fetchAll();
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

    /**
     * Methode qui permet de verifier la connexion d'un user
     * Renvoie le tableau avec les infos de l'user sans son mdp
     */
    public function connexionUser(string $pseudo, string $pass) : array {
        $st = $this->pdo->prepare(
                "SELECT id_utilisateur, prenom, nom, mdp, role
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

    /**
     * Permet d'inscrire un user en verifiant que :
     *  - pseudo n'est pas deja pris
     *  - email non associe a un compte existant
     */
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

    /**
     * Tags suivis par un utilisateur avec nombre de mentions
     */
    public function getTagsSuivis(string $idUser): array
    {
        $st = $this->pdo->prepare(
            "SELECT tg.libelle, COUNT(c.id_touite) AS nb
             FROM ABONNEMENT a
             JOIN TAG tg ON tg.id_tag = a.id_tag
             LEFT JOIN CONTENIR c ON c.id_tag = tg.id_tag
             WHERE a.id_utilisateur = :user
             GROUP BY tg.id_tag, tg.libelle
             ORDER BY tg.libelle"
        );
        $st->execute([':user' => $idUser]);
        return $st->fetchAll();
    }

    /**
     * Recherche parmi tous les tags avec indication de l'abonnement de $idUser
     */
    public function rechercherTags(string $recherche, string $idUser): array
    {
        // addcslashes() echappe les caracteres speciaux propres a SQL (%, _, \)
        // pour eviter qu'ils ne soient interpretes comme des jokers si l'utilisateur les tapes
        $motif = '%' . addcslashes($recherche, '%_\\\\') . '%';

        $st = $this->pdo->prepare(
            "SELECT tg.libelle,
                    COUNT(c.id_touite) AS nb,
                    EXISTS(SELECT 1 FROM ABONNEMENT a
                           WHERE a.id_utilisateur = :moi AND a.id_tag = tg.id_tag) AS suivi
             FROM TAG tg
             LEFT JOIN CONTENIR c ON c.id_tag = tg.id_tag
             WHERE tg.libelle LIKE :q
             GROUP BY tg.id_tag, tg.libelle
             ORDER BY nb DESC, tg.libelle
             LIMIT 20"
        );
        $st->execute([':moi' => $idUser, ':q' => $motif]);
        return $st->fetchAll();
    }

    /**
     * Bascule l'abonnement d'un utilisateur a un tag
     * @return ?bool true si abonne maintenant, false si desabonne, null si le tag n'existe pas
     */
    public function basculerAbonnementTag(string $idUser, string $libelle): ?bool
    {
        $st = $this->pdo->prepare("SELECT id_tag FROM TAG WHERE libelle = :libelle");
        $st->execute([':libelle' => $libelle]);
        $idTag = $st->fetchColumn();
        if ($idTag === false) {
            return null;
        }
        $idTag = (int) $idTag;

        $retire = $this->pdo->prepare(
            "DELETE FROM ABONNEMENT WHERE id_utilisateur = :user AND id_tag = :tag"
        );
        $retire->execute([':user' => $idUser, ':tag' => $idTag]);
        if ($retire->rowCount() > 0) {
            return false;
        }

        $this->pdo->prepare(
            "INSERT INTO ABONNEMENT (id_utilisateur, id_tag, date_abonnement)
             VALUES (:user, :tag, NOW())"
        )->execute([':user' => $idUser, ':tag' => $idTag]);
        return true;
    }

    /**
     * Condition du mur :
     *  -> touites des comptes suivis par $moi
     *  ou
     *  -> touites portant un tag suivi par $moi
     */
    private const CONDITION_MUR = "(t.id_utilisateur IN (SELECT s.id_suivi FROM SUIVRE s WHERE s.id_suiveur = :moi1)
            OR t.id_touite IN (SELECT c.id_touite FROM CONTENIR c
                               JOIN ABONNEMENT a ON a.id_tag = c.id_tag
                               WHERE a.id_utilisateur = :moi2))";

    /**
     * Nombre de touites du mur de l'utilisateur
     */
    public function compterMur(string $moi): int
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM TOUITE t WHERE " . self::CONDITION_MUR);
        $st->execute([':moi1' => $moi, ':moi2' => $moi]);
        return (int) $st->fetchColumn();
    }

    /**
     * Touites du mur de l'utilisateur du plus recent au plus ancien memes colonnes que getTouites
     */
    public function getMur(int $limit, int $offset, string $moi): array
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
                    i.description AS image_alt
             FROM TOUITE t
             JOIN UTILISATEUR u ON u.id_utilisateur = t.id_utilisateur
             LEFT JOIN IMAGE i ON i.id_image = t.id_image
             LEFT JOIN EVALUATION e ON e.id_touite = t.id_touite AND e.id_utilisateur = :moi3
             WHERE " . self::CONDITION_MUR . "
             ORDER BY t.date_publication DESC, t.id_touite DESC
             LIMIT :limite OFFSET :decalage"
        );
        $st->bindValue(':moi1', $moi);
        $st->bindValue(':moi2', $moi);
        $st->bindValue(':moi3', $moi);
        $st->bindValue(':limite', $limit, PDO::PARAM_INT);
        $st->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}
