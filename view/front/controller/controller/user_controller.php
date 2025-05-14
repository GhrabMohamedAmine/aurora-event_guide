<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../config.php';

class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }


    public function createUser($userData) {
        // Validation des champs requis
        $required = ['cin', 'nom', 'prenom', 'genre', 'date_naissance', 'type', 'telephone', 'email', 'mot_de_pass'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'message' => "Le champ $field est requis"];
            }
        }

        // Vérification des doublons
        if ($this->cinExists($userData['cin'])) {
            return ['success' => false, 'message' => 'Ce CIN existe déjà'];
        }
        if ($this->emailExists($userData['email'])) {
            return ['success' => false, 'message' => 'Cet email existe déjà'];
        }
        if ($this->telephoneExists($userData['telephone'])) {
            return ['success' => false, 'message' => 'Ce téléphone existe déjà'];
        }

        // Génération ID
        $newId = 'USR' . str_pad($this->getNextUserId(), 5, '0', STR_PAD_LEFT);

        $user = new User();
        $user->setIdUser($newId);
        $user->setCin($userData['cin']);
        $user->setNom($userData['nom']);
        $user->setPrenom($userData['prenom']);
        $user->setGenre($userData['genre']);
        $user->setDateNaissance($userData['date_naissance']);
        $user->setType($userData['type']);
        $user->setTelephone($userData['telephone']);
        $user->setEmail($userData['email']);
        $user->setMotDePass($userData['mot_de_pass']);

        try {
            $stmt = $this->db->prepare("INSERT INTO users VALUES (:id_user, :cin, :nom, :prenom, :genre, :date_naissance, :type, :telephone, :email, :mot_de_pass)");
            $params = [
                ':id_user' => $user->getIdUser(),
                ':cin' => $user->getCin(),
                ':nom' => $user->getNom(),
                ':prenom' => $user->getPrenom(), 
                ':genre' => $user->getGenre(),
                ':date_naissance' => $user->getDateNaissance(),
                ':type' => $user->getType(),
                ':telephone' => $user->getTelephone(),
                ':email' => $user->getEmail(),
                ':mot_de_pass' => $user->getMotDePass()
            ];
            $stmt->execute($params);
            return ['success' => true, 'message' => 'Utilisateur créé avec succès', 'id' => $newId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de création : ' . $e->getMessage()];
        }
    }

    public function getUser($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_user = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->setIdUser($userData['id_user']);
            $user->setCin($userData['cin']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setGenre($userData['genre']);
            $user->setDateNaissance($userData['date_naissance']);
            $user->setType($userData['type']);
            $user->setTelephone($userData['telephone']);
            $user->setEmail($userData['email']);
            $user->setMotDePass($userData['mot_de_pass']);
            return $user;
        }
        return null;
    }

    public function getAllUsers($sortBy = 'id_user', $order = 'ASC') {
        // Validate sortBy and order
        $allowedSort = ['id_user', 'nom'];
        $allowedOrder = ['ASC', 'DESC'];
        $sortBy = in_array($sortBy, $allowedSort) ? $sortBy : 'id_user';
        $order = in_array(strtoupper($order), $allowedOrder) ? strtoupper($order) : 'ASC';
        $stmt = $this->db->query("SELECT * FROM users ORDER BY $sortBy $order");
        $users = [];
        while ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User();
            $user->setIdUser($userData['id_user']);
            $user->setCin($userData['cin']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setGenre($userData['genre']);
            $user->setDateNaissance($userData['date_naissance']);
            $user->setType($userData['type']);
            $user->setTelephone($userData['telephone']);
            $user->setEmail($userData['email']);
            $user->setMotDePass($userData['mot_de_pass']);
            $users[] = $user;
        }
        return $users;
    }

    public function updateUser($id, $updateData) {
        $currentUser = $this->getUser($id);
        if (!$currentUser) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        
        try {
            $allowedFields = ['cin', 'nom', 'prenom', 'genre', 'date_naissance', 'type', 'telephone', 'email', 'mot_de_pass'];
            $updates = [];
            $params = [':id_user' => $id];
            
            foreach ($allowedFields as $field) {
                if (isset($updateData[$field]) && $updateData[$field] !== '') {
                    $updates[] = "$field = :$field";
                    $params[":$field"] = $updateData[$field];
                }
            }
            
            if (!empty($updates)) {
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id_user = :id_user";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                
                return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
            }
            
            return ['success' => true, 'message' => 'Aucune modification effectuée'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de mise à jour: ' . $e->getMessage()];
        }
    }

    public function changePassword($userId, $newPassword) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET mot_de_pass = :password WHERE id_user = :id");
            $stmt->execute([
                ':password' => $newPassword,
                ':id' => $userId
            ]);
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors du changement de mot de passe: ' . $e->getMessage()];
        }
    }

    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur de suppression : ' . $e->getMessage()];
        }
    }

    public function authenticateUser($email, $password) {
        $stmt = $this->db->prepare("SELECT id_user, type, mot_de_pass FROM users WHERE email = :email AND mot_de_pass = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if (!isset($_SESSION)) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_type'] = $user['type'];
            
            if ($user['type'] === 'admin' || $user['type'] === 'organisateur') {
                $redirect = '../view/back/user_back.php?userid=' . $user['id_user'];
            } else {
                $redirect = '../view/front/user_front.php?userid=' . $user['id_user'];
            }
            
            return ['success' => true, 'redirect' => $redirect];
        }
        
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // For plain text passwords in database
                if ($password === $user['mot_de_pass']) {
                    $authenticated = true;
                } 
                if (isset($authenticated)) {
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['type'];
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }


    private function cinExists($cin) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE cin = :cin");
        $stmt->bindParam(':cin', $cin);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    private function telephoneExists($telephone) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE telephone = :telephone");
        $stmt->bindParam(':telephone', $telephone);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    private function getNextUserId() {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(id_user, 4) AS UNSIGNED)) FROM users");
        $maxId = $stmt->fetchColumn();
        return $maxId ? $maxId + 1 : 1;
    }
}


?>