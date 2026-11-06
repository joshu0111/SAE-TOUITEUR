<?php

namespace touiteur\action;

use touiteur\render\Template;
use touiteur\exceptions\{PseudoAlreadyExists, AlreadyHaveAccount, AuthException};
use touiteur\repository\TouiteurRepository;

class InscriptionAction extends Action{
    public function execute(): string {
            //  user deja connecte
            if (isset($_SESSION['user']['id'])) {
                header('Location: index.php');
                exit;
            }

            // Si methode get recu
            if ($this->http_method === 'GET') {
                return $this->afficher([]);
            }


            $saisie = [
                'pseudo' => trim($_POST['pseudo'] ?? ''),
                'nom'    => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email'  => trim($_POST['email'] ?? ''),
            ];

            $pass    = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';

            // Fait valider le mot de passe pas nos exigences
            $erreur = $this->valider($saisie, $pass, $confirm);
            if ($erreur !== null) {
                return $this->afficher(array_merge($saisie, ['erreur' => $erreur]));
            }

            try {
                $bdd = TouiteurRepository::getInstance();
                $bdd->inscriptionUser(
                    $saisie['pseudo'],
                    $saisie['nom'],
                    $saisie['prenom'],
                    $saisie['email'],
                    $pass
                );
            } catch (PseudoAlreadyExists $e) {
                return $this->afficher(array_merge($saisie, ['erreur' => 'Ce pseudo est deja pris.']));
            } catch (AlreadyHaveAccount $e) {
                return $this->afficher(array_merge($saisie, ['erreur' => 'Cette adresse e-mail est deja associee à un compte.']));
            }


            header('Location: index.php?action=connexion');
            exit;
        }

        /**
         * Methode qui permet de filtrer les les mots de passes
        */
        private function valider(array $saisie, string $pass, string $confirm): ?string {
            if ($saisie['pseudo'] === '' || $saisie['nom'] === '' || $saisie['prenom'] === '' || $saisie['email'] === '') {
                return 'Tous les champs sont obligatoires.';
            }
            if (mb_strlen($saisie['pseudo']) > 50 || mb_strlen($saisie['nom']) > 50 || mb_strlen($saisie['prenom']) > 50) {
                return 'Les noms et le pseudo ne doivent pas depasser 50 caractères.';
            }
            if (filter_var($saisie['email'], FILTER_VALIDATE_EMAIL) === false) {
                return 'Adresse e-mail invalide.';
            }
            if (mb_strlen($pass) < 5) {
                return 'Le mot de passe doit contenir au moins 5 caractères.';
            }
            if ($pass !== $confirm) {
                return 'Les deux mots de passe ne correspondent pas.';
            }
            return null;
        }

        /**
         * Methode qui permet d'afficher la page
         */
        private function afficher(array $extra): string {
            return $this->page('Inscription', Template::Inscription, array_merge([
                'erreur'     => null,
                'pseudo'     => '',
                'nom'        => '',
                'prenom'     => '',
                'email'      => '',
            ], $extra));
        }

}
