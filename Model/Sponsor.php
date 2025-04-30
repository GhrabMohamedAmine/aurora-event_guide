<?php
require_once __DIR__ . '/../config/db.php';

class Sponsor {
    private $id_sponsor;
    private $nom_sponsor;
    private $entreprise;
    private $mail;
    private $telephone;

    // Getters
    public function getIdSponsor() { return $this->id_sponsor; }
    public function getNomSponsor() { return $this->nom_sponsor; }
    public function getEntreprise() { return $this->entreprise; }
    public function getMail() { return $this->mail; }
    public function getTelephone() { return $this->telephone; }

    // Setters
    public function setIdSponsor($id) { $this->id_sponsor = $id; }
    public function setNomSponsor($nom_sponsor) { $this->nom_sponsor = $nom_sponsor; }
    public function setEntreprise($entreprise) { $this->entreprise = $entreprise; }
    public function setMail($mail) { $this->mail = $mail; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }

}