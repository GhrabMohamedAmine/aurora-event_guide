<?php
require_once __DIR__.'/../config.php';

class DemandeSponsoring {
    private $id_sponsoring;
    private $id_sponsor;
    private $id_organisateur;
    private $date_demande;
    private $montant;
    private $statut;
    private $mailsponsor;
    private $mailorganisateur;
    private $idevent;

    // Getters
    public function getIdSponsoring() { return $this->id_sponsoring; }
    public function getIdSponsor() { return $this->id_sponsor; }
    public function getIdOrganisateur() { return $this->id_organisateur; }
    public function getDateDemande() { return $this->date_demande; }
    public function getMontant() { return $this->montant; }
    public function getStatut() { return $this->statut; }
    public function getMailSponsor() { return $this->mailsponsor; }
    public function getMailOrganisateur() { return $this->mailorganisateur; }
    public function getIdEvent() { return $this->idevent; }

    // Setters
    public function setIdSponsoring($id) { $this->id_sponsoring = $id; }
    public function setIdSponsor($id) { $this->id_sponsor = $id; }
    public function setIdOrganisateur($id) { $this->id_organisateur = $id; }
    public function setDateDemande($date) { $this->date_demande = $date; }
    public function setMontant($montant) { $this->montant = $montant; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setMailSponsor($mail) { $this->mailsponsor = $mail; }
    public function setMailOrganisateur($mail) { $this->mailorganisateur = $mail; }
    public function setIdEvent($id) { $this->idevent = $id; }
}