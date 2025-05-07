<?php
require_once __DIR__ . '/../Model/Sponsor.php';
require_once __DIR__ . '/../config/db.php';

class SponsorController {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    public function getSponsorById($id_sponsor) {
        $query = "SELECT * FROM sponsor WHERE id_sponsor = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_sponsor, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        $sponsor = new Sponsor();
        $sponsor->setIdSponsor($row['id_sponsor']);
        $sponsor->setNomSponsor($row['nom_sponsor']);
        $sponsor->setEntreprise($row['entreprise']);
        $sponsor->setMail($row['mail']);
        $sponsor->setTelephone($row['telephone']);
        $sponsor->setPhoto($row['photo'] ?? null);
        
        return $sponsor;
    }
    
    public function getAllSponsors() {
        $query = "SELECT * FROM sponsor ORDER BY nom_sponsor";
        
        $stmt = $this->conn->query($query);
        $sponsors = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sponsor = new Sponsor();
            $sponsor->setIdSponsor($row['id_sponsor']);
            $sponsor->setNomSponsor($row['nom_sponsor']);
            $sponsor->setEntreprise($row['entreprise']);
            $sponsor->setMail($row['mail']);
            $sponsor->setTelephone($row['telephone']);
            $sponsor->setPhoto($row['photo'] ?? null);
            
            $sponsors[] = $sponsor;
        }
        
        return $sponsors;
    }
    
    public function getSponsors() {
        $query = "SELECT * FROM sponsor ORDER BY entreprise ASC";
        
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteFront($id) {
        try {
            // First, delete related sponsoring requests
            $queryDemandes = "DELETE FROM demandesponsoring WHERE id_sponsor = ?";
            $stmtDemandes = $this->conn->prepare($queryDemandes);
            $stmtDemandes->bindParam(1, $id, PDO::PARAM_INT);
            $stmtDemandes->execute();
            
            // Then delete the sponsor
            $querySponsor = "DELETE FROM sponsor WHERE id_sponsor = ?";
            $stmtSponsor = $this->conn->prepare($querySponsor);
            $stmtSponsor->bindParam(1, $id, PDO::PARAM_INT);
            
            return $stmtSponsor->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function createSponsor($nom_sponsor, $entreprise, $mail, $telephone, $photo = null) {
        $query = "INSERT INTO sponsor (nom_sponsor, entreprise, mail, telephone, photo) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nom_sponsor, PDO::PARAM_STR);
        $stmt->bindParam(2, $entreprise, PDO::PARAM_STR);
        $stmt->bindParam(3, $mail, PDO::PARAM_STR);
        $stmt->bindParam(4, $telephone, PDO::PARAM_STR);
        $stmt->bindParam(5, $photo, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function updateSponsor($id_sponsor, $nom_sponsor, $entreprise, $mail, $telephone, $photo = null) {
        $query = "UPDATE sponsor SET nom_sponsor = ?, entreprise = ?, mail = ?, telephone = ?, photo = ? WHERE id_sponsor = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nom_sponsor, PDO::PARAM_STR);
        $stmt->bindParam(2, $entreprise, PDO::PARAM_STR);
        $stmt->bindParam(3, $mail, PDO::PARAM_STR);
        $stmt->bindParam(4, $telephone, PDO::PARAM_STR);
        $stmt->bindParam(5, $photo, PDO::PARAM_STR);
        $stmt->bindParam(6, $id_sponsor, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    // Adding updateFront method needed by the application
    public function updateFront($id, $nom_sponsor, $entreprise, $mail, $telephone, $photo = null) {
        try {
            $query = "UPDATE sponsor SET nom_sponsor = ?, entreprise = ?, mail = ?, telephone = ?, photo = ? WHERE id_sponsor = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $nom_sponsor, PDO::PARAM_STR);
            $stmt->bindParam(2, $entreprise, PDO::PARAM_STR);
            $stmt->bindParam(3, $mail, PDO::PARAM_STR);
            $stmt->bindParam(4, $telephone, PDO::PARAM_STR);
            $stmt->bindParam(5, $photo, PDO::PARAM_STR);
            $stmt->bindParam(6, $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function deleteSponsor($id_sponsor) {
        $query = "DELETE FROM sponsor WHERE id_sponsor = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_sponsor, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function getSponsorsByNameOrCompany($search) {
        $search = "%$search%";
        $query = "SELECT * FROM sponsor WHERE nom_sponsor LIKE ? OR entreprise LIKE ? ORDER BY nom_sponsor";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $search, PDO::PARAM_STR);
        $stmt->bindParam(2, $search, PDO::PARAM_STR);
        $stmt->execute();
        
        $sponsors = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sponsor = new Sponsor();
            $sponsor->setIdSponsor($row['id_sponsor']);
            $sponsor->setNomSponsor($row['nom_sponsor']);
            $sponsor->setEntreprise($row['entreprise']);
            $sponsor->setMail($row['mail']);
            $sponsor->setTelephone($row['telephone']);
            
            $sponsors[] = $sponsor;
        }
        
        return $sponsors;
    }
}