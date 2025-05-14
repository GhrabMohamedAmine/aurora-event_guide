<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/DemandeSponsoring.php';

class DemandeSponsoringController {
    private $pdo;

    public function __construct() {
        $this->pdo = $db = getDB();
    }

    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT ds.*, s.entreprise, s.mail as sponsor_email 
            FROM demandesponsoring ds
            JOIN sponsor s ON ds.id_sponsor = s.id_sponsor
            ORDER BY ds.date_demande DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT ds.*, s.entreprise 
            FROM demandesponsoring ds
            JOIN sponsor s ON ds.id_sponsor = s.id_sponsor
            WHERE ds.id_sponsoring = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByStatus($statut) {
        $stmt = $this->pdo->prepare("
            SELECT ds.*, s.entreprise, s.mail as sponsor_email 
            FROM demandesponsoring ds
            JOIN sponsor s ON ds.id_sponsor = s.id_sponsor
            WHERE ds.statut = ?
            ORDER BY ds.date_demande DESC
        ");
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($id_sponsor, $id_organisateur, $montant, $idevent) {
        $stmt = $this->pdo->prepare("
            INSERT INTO demandesponsoring 
            (id_sponsor, id_organisateur, montant, idevent, statut) 
            VALUES (?, ?,?, ?, 'enattente')
        ");
        return $stmt->execute([$id_sponsor, $id_organisateur, $montant, $idevent]);
    }

    public function update($id, $id_sponsor, $id_organisateur, $montant, $idevent, $statut) {

        $stmt = $this->pdo->prepare("
            UPDATE demandesponsoring 
            SET id_sponsor = ?,
                id_organisateur = ?,
                montant = ?,
                idevent = ?,
                statut = ?,
                date_demande = NOW()
            WHERE id_sponsoring = ?
        ");
        
        return $stmt->execute([
            $id_sponsor,
            $id_organisateur,
            $montant,
            $idevent,
            $statut,
            $id
        ]);
    }

    public function updateStatus($id, $statut) {
        $stmt = $this->pdo->prepare("
            UPDATE demandesponsoring 
            SET statut = ? 
            WHERE id_sponsoring = ?
        ");
        return $stmt->execute([$statut, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM demandesponsoring WHERE id_sponsoring = ?");
        return $stmt->execute([$id]);
    }

}