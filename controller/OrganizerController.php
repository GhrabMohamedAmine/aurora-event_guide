<?php
require_once __DIR__.'/../model/Database.php';
require_once __DIR__.'/../model/User.php';

class OrganizerController {
    private $userModel;

    public function __construct() {
        session_start();
        $this->checkAuth();
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function dashboard() {
        require_once __DIR__.'/../view/organizer/dashboard.php';
    }

    public function profile() {
        $user = $_SESSION['user'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'telephone' => $_POST['telephone'],
                'email' => $_POST['email']
            ];

            if ($this->userModel->update($user['cin'], $data)) {
                $_SESSION['user'] = $this->userModel->getById($user['cin']);
                $success = "Profil mis à jour avec succès";
            }
        }
        
        require_once __DIR__.'/../view/organizer/profile.php';
    }

    private function checkAuth() {
        if (!isset($_SESSION['user']) {
            header('Location: ../front.php');
            exit;
        }
        
        if ($_SESSION['user']['type'] !== 'organisator') {
            header('Location: ../front.php');
            exit;
        }
    }
}

$action = $_GET['action'] ?? 'dashboard';
$controller = new OrganizerController();
$controller->$action();
?>