<?php

namespace touitteur\src\classes\user;

class Suivre {
    private int $id_suiveur;
    private int $id_suivi;

    public function __construct(int $id_suiveur, int $id_suivi) {
        $this->id_suiveur = $id_suiveur;
        $this->id_suivi = $id_suivi;
    }

    public function getIdSuiveur(): int {
        return $this->id_suiveur;
    }

    public function getIdSuivi(): int {
        return $this->id_suivi;
    }
}