<?php

namespace touiteur\touites;



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

    /**
     * Methode qui renvoie la liste des tags (sans le #) presents dans le contenu
     */
    public function getTags(): array{
        preg_match_all('/#([\p{L}\p{N}_]{1,50})/u', $this->contenu, $matches);
        return array_values(array_unique(array_map('mb_strtolower', $matches[1])));
    }
}
?>
