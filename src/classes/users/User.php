<?php

class User
{
    private string $name;
    private string $idName;
    private int $nbTouite;
    private int $abonne;
    private int $abonnement;
    private array $listeSuivi;
    private array $listeAbonnement;
    private string $biographie;

    public function __construct(
        string $name,
        string $idName,
        int $nbTouite = 0,
        int $abonne = 0,
        int $abonnement = 0,
        array $listeSuivi = [],
        array $listeAbonnement = [],
        string $biographie = ''
    ) {
        $this->name = $name;
        $this->idName = $idName;
        $this->nbTouite = $nbTouite;
        $this->abonne = $abonne;
        $this->abonnement = $abonnement;
        $this->listeSuivi = $listeSuivi;
        $this->listeAbonnement = $listeAbonnement;
        $this->biographie = $biographie;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdName(): string
    {
        return $this->idName;
    }

    public function getNbTouite(): int
    {
        return $this->nbTouite;
    }

    public function getAbonne(): int
    {
        return $this->abonne;
    }

    public function getAbonnement(): int
    {
        return $this->abonnement;
    }

    public function getListeSuivi(): array
    {
        return $this->listeSuivi;
    }

    public function getListeAbonnement(): array
    {
        return $this->listeAbonnement;
    }

    public function getBiographie(): string
    {
        return $this->biographie;
    }
}