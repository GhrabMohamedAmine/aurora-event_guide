<?php
require_once __DIR__.'/../model/Database.php';
require_once __DIR__.'/../model/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function login() {
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            $user = $this->userModel->getByEmail($email);

            if ($user && password_verify($password, $user['mot_de_pass'])) {
                if ($this->checkUserRole($user, $role)) {
                    session_start();
                    $_SESSION['user'] = $user;
                    
                    switch($role) {
                        case 'admin':
                            header('Location: ../UserController.php?action=list');
                            break;
                        case 'organisator':
                            header('Location: OrganizerController.php?action=dashboard');
                            break;
                        default:
                            header('Location: ParticipantController.php?action=dashboard');
                    }
                    exit;
                } else {
                    $error = "Vous n'avez pas les droits pour ce rôle";
                }
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        }
        
        require_once __DIR__.'/../view/front/login.php';
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: ../front.php');
        exit;
    }

    private function checkUserRole($user, $requestedRole) {
        $userType = $user['type'];
        
        return ($requestedRole === 'participant' && $userType === 'participant') ||
               ($requestedRole === 'organisator' && $userType === 'organisator') ||
               ($requestedRole === 'admin' && $userType === 'admin');
    }
}

// Gestion des requêtes
$action = $_GET['action'] ?? 'login';
$controller = new AuthController();

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    header('HTTP/1.1 404 Not Found');
    echo 'Page non trouvée';
}
?>