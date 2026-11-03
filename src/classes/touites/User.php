<?php

namespace touiter\touites\user;
use touiter\exceptions\UndefinedAttribute;

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

    public function __get(string $att): string {
        if (property_exists($att)){
            return  $this->$att;
        }
        return new UndefinedAttribute();
    }

}
