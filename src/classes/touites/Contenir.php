<?php

namespace touitteur\src\classes\touittes;

class Contenir {
    private int $id_touite;
    private int $id_tag;

    public function __construct(int $id_touite, int $id_tag) {
        $this->id_touite = $id_touite;
        $this->id_tag = $id_tag;
    }

    public function getIdTouite(): int {
        return $this->id_touite;
    }

    public function getIdTag(): int {
        return $this->id_tag;
    }
}