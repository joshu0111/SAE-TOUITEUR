<?php

class Touitte{

    private string $contenu;

    private int $note;

    private string $datePublication;

    
    public function __construct(string $contenu, int $note, string $date){
        $this->contenu = $contenu;
        $this->note = $note;
        $this->datePublication = $date;
    }

    public function get_contenu() : string { return $this->contenu; }
    public function get_note() : int { return $this->note; }
    public function get_date_publication() : string { return $this->datePublication; }
    
}

?>