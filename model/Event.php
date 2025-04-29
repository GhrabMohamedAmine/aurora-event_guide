<?php
require_once __DIR__.'/../config.php';

class Event {
    private $id_event;
    private $titre;
    private $artiste;
    private $date;
    private $heure;
    private $lieu;
    private $description;
    private $image;
    private $prix;
    private $db;

    public function __construct($data = []) {
        $this->db = $this->getDBConnection();
        
        if (!empty($data)) {
            $this->id_event = $data['id_event'] ?? null;
            $this->titre = $data['titre'] ?? '';
            $this->artiste = $data['artiste'] ?? '';
            $this->date = $data['date'] ?? '';
            $this->heure = $data['heure'] ?? '';
            $this->lieu = $data['lieu'] ?? '';
            $this->description = $data['description'] ?? '';
            $this->image = $data['image'] ?? null;
            $this->prix = $data['prix'] ?? null;
        }
    }

    protected function getDBConnection() {
        return getDB();
    }

    // Getters et Setters
    public function getIdEvent() { return $this->id_event; }
    public function setIdEvent($id_event) { $this->id_event = $id_event; return $this; }
    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; return $this; }
    public function getArtiste() { return $this->artiste; }
    public function setArtiste($artiste) { $this->artiste = $artiste; return $this; }
    public function getDate() { return $this->date; }
    public function setDate($date) { $this->date = $date; return $this; }
    public function getHeure() { return $this->heure; }
    public function setHeure($heure) { $this->heure = $heure; return $this; }
    public function getLieu() { return $this->lieu; }
    public function setLieu($lieu) { $this->lieu = $lieu; return $this; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getImage() { return $this->image; }
    public function setImage($image) { $this->image = $image; return $this; }
    public function getPrix() { return $this->prix; }
    public function setPrix($prix) { $this->prix = $prix; return $this; }

    public static function getAll() {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM evenement ORDER BY date DESC");
            $events = [];
    
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $events[] = new self($row);
            }
    
            return $events;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans getAll(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans getAll(): " . $e->getMessage());
            return [];
        }
    }
    
    public static function getById($id_event) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM evenement WHERE id_event = :id_event LIMIT 1");
            $stmt->bindParam(':id_event', $id_event, PDO::PARAM_INT);
            $stmt->execute();
            $eventData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $eventData ? new self($eventData) : null;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans getById(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans getById(): " . $e->getMessage());
            return null;
        }
    }

    public function create() {
        try {
            $stmt = $this->db->prepare("INSERT INTO evenement 
                                      (titre, artiste, date, heure, lieu, description, image, prix) 
                                      VALUES (:titre, :artiste, :date, :heure, :lieu, :description, :image, :prix)");
            $success = $stmt->execute([
                ':titre' => $this->titre,
                ':artiste' => $this->artiste,
                ':date' => $this->date,
                ':heure' => $this->heure,
                ':lieu' => $this->lieu,
                ':description' => $this->description,
                ':image' => $this->image,
                ':prix' => $this->prix
            ]);
            
            if ($success) {
                $this->id_event = $this->db->lastInsertId();
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
            $stmt = $this->db->prepare("UPDATE evenement SET 
                titre = :titre, 
                artiste = :artiste, 
                date = :date, 
                heure = :heure, 
                lieu = :lieu,
                description = :description,
                image = :image,
                prix = :prix 
                WHERE id_event = :id_event");
            
            return $stmt->execute([
                ':id_event' => $this->id_event,
                ':titre' => $this->titre,
                ':artiste' => $this->artiste,
                ':date' => $this->date,
                ':heure' => $this->heure,
                ':lieu' => $this->lieu,
                ':description' => $this->description,
                ':image' => $this->image,
                ':prix' => $this->prix
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
            if ($this->image) {
                $this->deleteImageFile();
            }
            
            $stmt = $this->db->prepare("DELETE FROM evenement WHERE id_event = :id_event");
            return $stmt->execute([':id_event' => $this->id_event]);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Erreur SQL dans delete(): " . $e->getMessage());
            }
            error_log("Erreur SQL dans delete(): " . $e->getMessage());
            return false;
        }
    }
    
    private function deleteImageFile() {
        if ($this->image && file_exists(__DIR__ . '/../' . $this->image)) {
            unlink(__DIR__ . '/../' . $this->image);
        }
    }
}