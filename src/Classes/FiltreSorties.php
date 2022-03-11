<?php

namespace App\Classes;

use App\Entity\Campus;

class FiltreSorties{

    private $campus;

    private $nom;

    private $dateMin;

    private $dateMax;

    private $isOrganisateur;

    private $isInscrit;

    private $isNonInscrit;

    private $isPassee;


    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateMin(): ?\DateTimeInterface
    {
        return $this->dateMin;
    }

    public function setDateMin(?\DateTimeInterface $dateMin): self
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    public function getDateMax(): ?\DateTimeInterface
    {
        return $this->dateMax;
    }

    public function setDateMax(?\DateTimeInterface $dateMax): self
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    public function getIsOrganisateur(): ?bool
    {
        return $this->isOrganisateur;
    }

    public function setIsOrganisateur(bool $isOrganisateur): self
    {
        $this->isOrganisateur = $isOrganisateur;

        return $this;
    }

    public function getIsInscrit(): ?bool
    {
        return $this->isInscrit;
    }

    public function setIsInscrit(bool $isInscrit): self
    {
        $this->isInscrit = $isInscrit;

        return $this;
    }

    public function getIsNonInscrit(): ?bool
    {
        return $this->isNonInscrit;
    }

    public function setIsNonInscrit(bool $isNonInscrit): self
    {
        $this->isNonInscrit = $isNonInscrit;

        return $this;
    }

    public function getIsPassee(): ?bool
    {
        return $this->isPassee;
    }

    public function setIsPassee(bool $isPassee): self
    {
        $this->isPassee = $isPassee;

        return $this;
    }

}