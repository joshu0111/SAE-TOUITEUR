<?php
namespace touiteur\action;

use touiteur\touites\Touite;
use touiteur\repository\TouiteurRepository;

class CreeUnTouite extends Action {
    private const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo
        private const TYPES = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

    public function execute(): string {
            $user = $this->getUtilisateurConnecte();

            if ($this->http_method === 'POST' && $user !== null) {
                $contenu = trim($_POST['contenu'] ?? '');
                if ($contenu !== '' && mb_strlen($contenu) <= 235) {
                    $idImage = $this->sauverImage($contenu);
                    TouiteurRepository::getInstance()->insererTouite(new Touite($contenu), $user['id'], $idImage);
                }
            }

            header('Location: index.php?action=home');
            exit;
        }

        /**
        * Methode qui permet de valider et enregistrer l'image envoyee avec le touite.
        * Renvoie l'id de l'image, ou null si pas d'image ou image refusée.
        */
        private function sauverImage(string $contenu): ?int {
            // On verifie qu'une image est enregistree
            $fichier = $_FILES['image'] ?? null;

            if ($fichier === null || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
                return null;
            }
            if ($fichier['error'] !== UPLOAD_ERR_OK || $fichier['size'] > self::TAILLE_MAX) {
                return null;
            }

            // Verificvation du type de l'image
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
            if (!isset(self::TYPES[$mime]) || getimagesize($fichier['tmp_name']) === false) {
                return null;
            }

            // On un nom de fichier aleatoire pour l'image
            $nom     = bin2hex(random_bytes(16)) . '.' . self::TYPES[$mime];
            $dossier = __DIR__ . '/../../../uploads/images/';

            if (!move_uploaded_file($fichier['tmp_name'], $dossier . $nom)) {
                return null;
            }

            // Le contenu du touite sert de texte alternatif (description NOT NULL en base)
            return TouiteurRepository::getInstance()->insererImage(
                mb_substr($contenu, 0, 255),
                'uploads/images/' . $nom
            );
        }

}
