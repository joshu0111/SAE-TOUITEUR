<?php

namespace touiteur\touites\user;


class Touite{

    private string $contenu;

    private int $note;

    private string $datePublication;


    public function __construct(string $contenu){
        $this->contenu = $contenu;
        $this->note = 0;
        $this->datePublication = date('d/m/Y H:i:s');
    }

    public function get_contenu() : string { return $this->contenu; }
    public function get_note() : int { return $this->note; }
    public function get_date_publication() : string { return $this->datePublication; }
}
?>
