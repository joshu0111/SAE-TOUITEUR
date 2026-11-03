<?php

class Touitte{

    private string $contenu;

    private int $note;

    private string $datePublication;

    
    public function __construct(string $contenu, int $note){
        $this->contenu = $contenu;
        $this->note = $note;
        $this->datePublication = date('d/m/Y H:i:s');
    }

    public function get_contenu() : string { return $this->contenu; }
    public function get_note() : int { return $this->note; }
    public function get_date_publication() : string { return $this->datePublication; }
    
}

?>