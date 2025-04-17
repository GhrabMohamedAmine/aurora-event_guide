<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($cin) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE cin = ?");
        $stmt->execute([$cin]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cinExists($cin) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE cin = ?");
        $stmt->execute([$cin]);
        return $stmt->fetchColumn() > 0;
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function generateUserId($type) {
        $prefix = '';
        switch ($type) {
            case 'admin': $prefix = 'ADM'; break;
            case 'organisator': $prefix = 'ORG'; break;
            case 'participant': $prefix = 'USR'; break;
            default: $prefix = 'USR';
        }

        do {
            $randomNumber = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
            $id_user = $prefix . $randomNumber;
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id_user = ?");
            $stmt->execute([$id_user]);
        } while ($stmt->fetchColumn() > 0);

        return $id_user;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users VALUES (:cin, :id_user, :nom, :prenom, :type, :telephone, :email, :mot_de_pass)");
        return $stmt->execute($data);
    }

    public function update($cin, $data) {
        $stmt = $this->db->prepare("UPDATE users SET nom = :nom, prenom = :prenom, type = :type, telephone = :telephone, email = :email WHERE cin = :cin");
        $data['cin'] = $cin;
        return $stmt->execute($data);
    }

    public function delete($cin) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE cin = ?");
        return $stmt->execute([$cin]);
    }
    // Ajouter cette méthode à la classe User
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}