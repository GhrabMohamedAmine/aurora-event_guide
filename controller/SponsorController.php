<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/Sponsor.php';

class SponsorController {
    private $db;

    public function __construct() {

        $this->db = Database::getConnection();
    }

    public function getAll() {
        $query = $this->db->prepare("SELECT * FROM sponsor");
        $query->execute();
        return $query->fetchAll();
    }

    public function getById($id) {
        $query = $this->db->prepare("SELECT * FROM sponsor WHERE id_sponsor = ?");
        $query->execute([$id]);
        return $query->fetch();
    }

    public function add($cin, $entreprise, $mail, $telephone) {
        $query = $this->db->prepare("INSERT INTO sponsor (cin, entreprise, mail, telephone) VALUES (?, ?, ?, ?)");
        return $query->execute([$cin, $entreprise, $mail, $telephone]);
    }

    public function update($id, $cin, $entreprise, $mail, $telephone) {
        if (!$this->exists($id)) {
            return false;
        }
        $query = $this->db->prepare("UPDATE sponsor SET cin = ?, entreprise = ?, mail = ?, telephone = ? WHERE id_sponsor = ?");
        return $query->execute([$cin, $entreprise, $mail, $telephone, $id]);
    }

    public function delete($id) {
        try {
            $queryDemandes = $this->db->prepare("DELETE FROM demandesponsoring WHERE id_sponsor = ?");
            $queryDemandes->execute([$id]);

            $querySponsor = $this->db->prepare("DELETE FROM sponsor WHERE id_sponsor = ?");
            if($querySponsor->execute([$id])) {
                header('Location: ../back/sponsoring.php');
                exit();
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function exists($id) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM sponsor WHERE id_sponsor = ?");
        $query->execute([$id]);
        return $query->fetchColumn() > 0;
    }
}
