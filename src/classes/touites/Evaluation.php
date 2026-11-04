<?php

namespace touiteur\touites;

class Evaluation {
    private string $id_user;
    private int $id_touite;
    private int $valeur;

    public function __construct(string $id_user, int $id_touite, int $valeur) {
        if ($valeur !== 1 && $valeur !== -1) {
            throw new \InvalidArgumentException("La valeur d'une evaluation doit etre 1 ou -1.");
        }
        $this->id_user = $id_user;
        $this->id_touite = $id_touite;
        $this->valeur = $valeur;
    }

    public function getIdUser(): string {
        return $this->id_user;
    }

    public function getIdTouite(): int {
        return $this->id_touite;
    }

    public function getValeur(): int {
        return $this->valeur;
    }
}