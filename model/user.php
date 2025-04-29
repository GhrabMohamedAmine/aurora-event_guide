<?php
class User {
    // Properties
    public $cin;
    public $id_user;
    public $nom;
    public $prenom;
    public $genre;
    public $date_naissance;
    public $type;
    public $telephone;
    public $email;
    public $mot_de_pass;

    // Getters
    public function getCin() { return $this->cin; }
    public function getIdUser() { return $this->id_user; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getGenre() { return $this->genre; }
    public function getDateNaissance() { return $this->date_naissance; }
    public function getType() { return $this->type; }
    public function getTelephone() { return $this->telephone; }
    public function getEmail() { return $this->email; }
    public function getMotDePass() { return $this->mot_de_pass; }

    // Setters
    public function setCin($cin) { $this->cin = $cin; }
    public function setIdUser($id_user) { $this->id_user = $id_user; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setGenre($genre) { $this->genre = $genre; }
    public function setDateNaissance($date_naissance) { $this->date_naissance = $date_naissance; }
    public function setType($type) { $this->type = $type; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePass($mot_de_pass) { $this->mot_de_pass = $mot_de_pass; }
}
?>