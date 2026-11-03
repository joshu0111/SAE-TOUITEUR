<?php

namespace touitteur\src\classes\user;

class Abonnement {
    private int $id_user;
    private int $id_tag;
    private string $date_abonnement;

    public function __construct(int $id_user, int $id_tag) {
        $this->id_user = $id_user;
        $this->id_tag = $id_tag;
        $this->date_abonnement = date('Y-m-d H:i:s');
    }

    public function getIdUser(): int {
        return $this->id_user;
    }

    public function getIdTag(): int {
        return $this->id_tag;
    }

    public function getDateAbonnement(): string {
        return $this->date_abonnement;
    }
}