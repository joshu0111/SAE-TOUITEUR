<?php

namespace touiteur\touites\user;


class Image {
    private int $id;
    private string $description;
    private string $chemin_fichier;

    public function __construct(int $id, string $description, string $chemin_fichier) {
        $this->id = $id;
        $this->description = $description;
        $this->chemin_fichier = $chemin_fichier;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getCheminFichier(): string {
        return $this->chemin_fichier;
    }
}
