<?php
require_once __DIR__.'/../config.php';

class Reservation {
    private $id_reservation;
    private $id_event;
    private $nom;
    private $telephone;
    private $nombre_places;
    private $categorie;
    private $mode_paiement;
    private $db;

    public function __construct($data = []) {
        $this->db = $this->getDBConnection();
        
        if (!empty($data)) {
            $this->id_reservation = $data['id_reservation'] ?? null;
            $this->id_event = $data['id_event'] ?? null;
            $this->nom = $data['nom'] ?? '';
            $this->telephone = $data['telephone'] ?? '';
            $this->nombre_places = $data['nombre_places'] ?? 0;
            $this->categorie = $data['categorie'] ?? '';
            $this->mode_paiement = $data['mode_paiement'] ?? '';
        }
    }

    protected function getDBConnection() {
        return getDB();
    }

    // Getters et Setters
    public function getIdReservation() { return $this->id_reservation; }
    public function setIdReservation($id_reservation) { $this->id_reservation = $id_reservation; return $this; }
    public function getIdEvent() { return $this->id_event; }
    public function setIdEvent($id_event) { $this->id_event = $id_event; return $this; }
    public function getNom() { return $this->nom; }
    public function setNom($nom) { $this->nom = $nom; return $this; }
    public function getTelephone() { return $this->telephone; }
    public function setTelephone($telephone) { $this->telephone = $telephone; return $this; }
    public function getNombrePlaces() { return $this->nombre_places; }
    public function setNombrePlaces($nombre_places) { $this->nombre_places = $nombre_places; return $this; }
    public function getCategorie() { return $this->categorie; }
    public function setCategorie($categorie) { $this->categorie = $categorie; return $this; }
    public function getModePaiement() { return $this->mode_paiement; }
    public function setModePaiement($mode_paiement) { $this->mode_paiement = $mode_paiement; return $this; }

    public static function getAll() {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM reservation");
            $reservations = [];
    
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $reservations[] = new self($row);
            }
    
            return $reservations;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans getAll(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans getAll(): " . $e->getMessage());
            return [];
        }
    }
    
    public static function getById($id_reservation) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM reservation WHERE id_reservation = :id_reservation LIMIT 1");
            $stmt->bindParam(':id_reservation', $id_reservation, PDO::PARAM_INT);
            $stmt->execute();
            $reservationData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $reservationData ? new self($reservationData) : null;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans getById(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans getById(): " . $e->getMessage());
            return null;
        }
    }

    public static function getByEventId($id_event) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM reservation WHERE id_event = :id_event");
            $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
            $stmt->execute();
            $reservations = [];
    
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $reservations[] = new self($row);
            }
    
            return $reservations;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans getByEventId(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans getByEventId(): " . $e->getMessage());
            return [];
        }
    }

    public function create() {
        try {
            $stmt = $this->db->prepare("INSERT INTO reservation 
                                      (id_event, nom, telephone, nombre_places, categorie, mode_paiement) 
                                      VALUES (:id_event, :nom, :telephone, :nombre_places, :categorie, :mode_paiement)");
            $success = $stmt->execute([
                ':id_event' => $this->id_event,
                ':nom' => $this->nom,
                ':telephone' => $this->telephone,
                ':nombre_places' => $this->nombre_places,
                ':categorie' => $this->categorie,
                ':mode_paiement' => $this->mode_paiement
            ]);
            
            if ($success) {
                $this->id_reservation = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans create(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans create(): " . $e->getMessage());
            return false;
        }
    }
    
    public function update() {
        try {
            $stmt = $this->db->prepare("UPDATE reservation SET 
                id_event = :id_event, 
                nom = :nom, 
                telephone = :telephone, 
                nombre_places = :nombre_places, 
                categorie = :categorie,
                mode_paiement = :mode_paiement 
                WHERE id_reservation = :id_reservation");
            
            return $stmt->execute([
                ':id_reservation' => $this->id_reservation,
                ':id_event' => $this->id_event,
                ':nom' => $this->nom,
                ':telephone' => $this->telephone,
                ':nombre_places' => $this->nombre_places,
                ':categorie' => $this->categorie,
                ':mode_paiement' => $this->mode_paiement
            ]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans update(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans update(): " . $e->getMessage());
            return false;
        }
    }
    
    public function delete() {
        try {
            $stmt = $this->db->prepare("DELETE FROM reservation WHERE id_reservation = :id_reservation");
            return $stmt->execute([':id_reservation' => $this->id_reservation]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans delete(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans delete(): " . $e->getMessage());
            return false;
        }
    }
}