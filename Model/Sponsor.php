<?php
require_once __DIR__ . '/../config/db.php';

class Sponsor {
    private $id_sponsor;
    private $cin;
    private $entreprise;
    private $mail;
    private $telephone;

    // Getters
    public function getIdSponsor() { return $this->id_sponsor; }
    public function getCin() { return $this->cin; }
    public function getEntreprise() { return $this->entreprise; }
    public function getMail() { return $this->mail; }
    public function getTelephone() { return $this->telephone; }

    // Setters
    public function setIdSponsor($id) { $this->id_sponsor = $id; }
    public function setCin($cin) { $this->cin = $cin; }
    public function setEntreprise($entreprise) { $this->entreprise = $entreprise; }
    public function setMail($mail) { $this->mail = $mail; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }

}