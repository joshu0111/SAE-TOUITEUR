<?php

class User {
    private string $name;
    private string $id_name;
    private string $date_creation;
    private int $nb_touites;
    private int $nb_followers;
    private int $nb_following;
    private array $list_followers;
    private array $list_following;
    private string $bio;

    public function __construct(string $name, string $id_name, ?string $bio = "No bio yet") {
        $this->name = $name;
        $this->id_name = $id_name;
        $this->date_creation = date('Y-m-d');
        $this->nb_touites = 0;
        $this->nb_followers = 0;
        $this->nb_following = 0;
        $this->list_followers = [];
        $this->list_following = [];
        $this->bio = $bio;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getIdName(): string {
        return $this->id_name;
    }

    public function getDateCreation(): string {
        return $this->date_creation;
    }

    public function getNbTouites(): int {
        return $this->nb_touites;
    }

    public function getNbFollowers(): int {
        return $this->nb_followers;
    }

    public function getNbFollowing(): int {
        return $this->nb_following;
    }

    public function getListFollowers(): array {
        return $this->list_followers;
    }

    public function getListFollowing(): array {
        return $this->list_following;
    }

    public function getBio(): string {
        return $this->bio;
    }
}