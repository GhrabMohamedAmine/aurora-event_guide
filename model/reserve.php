<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controller/reserveC.php';

class Reservation {
    private $id_reservation;
    private $id_event;
    private $id_user;
    private $nom;
    private $telephone;
    private $nombre_places;
    private $categorie;
    private $mode_paiement;

    // Constructor
    public function __construct($id_reservation = null, $id_event = null, $id_user = null, $nom = null, $telephone = null, $nombre_places = null, $categorie = null, $mode_paiement = null) {
        $this->id_reservation = $id_reservation;
        $this->id_event = $id_event;
        $this->id_user = $id_user;
        $this->nom = $nom;
        $this->telephone = $telephone;
        $this->nombre_places = $nombre_places;
        $this->categorie = $categorie;
        $this->mode_paiement = $mode_paiement;
    }

    // Getters
    public function getIdReservation() {
        return $this->id_reservation;
    }

    public function getIdEvent() {
        return $this->id_event;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getNombrePlaces() {
        return $this->nombre_places;
    }

    public function getCategorie() {
        return $this->categorie;
    }

    public function getModePaiement() {
        return $this->mode_paiement;
    }

    // Setters
    public function setIdReservation($id_reservation) {
        $this->id_reservation = $id_reservation;
    }

    public function setIdEvent($id_event) {
        $this->id_event = $id_event;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }

    public function setNombrePlaces($nombre_places) {
        $this->nombre_places = $nombre_places;
    }

    public function setCategorie($categorie) {
        $this->categorie = $categorie;
    }

    public function setModePaiement($mode_paiement) {
        $this->mode_paiement = $mode_paiement;
    }

    // Static method to get reservation by ID
    public static function getById($id) {
        $sql = "SELECT r.*, u.nom, u.telephone 
                FROM reservation r 
                JOIN users u ON r.id_user = u.id_user 
                WHERE r.id_reservation = :id_reservation";
        $db = getDB();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id_reservation', $id, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return new Reservation(
                    $result['id_reservation'],
                    $result['id_event'],
                    $result['id_user'],
                    $result['nom'],
                    $result['telephone'],
                    $result['nombre_places'],
                    $result['categorie'],
                    $result['mode_paiement']
                );
            }
            return null;
        } catch (Exception $e) {
            error_log("Error fetching reservation: " . $e->getMessage());
            return null;
        }
    }

    // Update method
    public function update() {
        $reservationC = new ReservationC();
        return $reservationC->modifierReservation($this);
    }

    // Delete method
    public function delete() {
        $reservationC = new ReservationC();
        return $reservationC->supprimerReservation($this->id_reservation);
    }
}
?>