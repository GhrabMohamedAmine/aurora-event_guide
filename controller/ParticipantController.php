<?php
require_once __DIR__.'/../model/Database.php';
require_once __DIR__.'/../model/User.php';

class ParticipantController {
    private $userModel;

    public function __construct() {
        session_start();
        $this->checkAuth();
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function dashboard() {
        require_once __DIR__.'/../view/participant/dashboard.php';
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

            if ($_POST['mot_de_pass'] !== '') {
                $data['mot_de_pass'] = password_hash($_POST['mot_de_pass'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($user['cin'], $data)) {
                $_SESSION['user'] = $this->userModel->getById($user['cin']);
                $success = "Profil mis à jour avec succès";
            }
        }
        
        require_once __DIR__.'/../view/participant/profile.php';
    }

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            header('Location: ../front.php');
            exit;
        }
        
        if ($_SESSION['user']['type'] !== 'participant') {
            header('Location: ../front.php');
            exit;
        }
    }
}

$action = $_GET['action'] ?? 'dashboard';
$controller = new ParticipantController();
$controller->$action();
?>