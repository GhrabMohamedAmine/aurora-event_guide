<?php
require_once __DIR__.'/../model/Database.php';
require_once __DIR__.'/../model/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function list() {
        $users = $this->userModel->getAll();
        require_once __DIR__.'/../view/back/users/list.php';
    }

    public function add() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cin' => $_POST['cin'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'type' => $_POST['type'],
                'telephone' => $_POST['telephone'],
                'email' => $_POST['email'],
                'mot_de_pass' => password_hash($_POST['mot_de_pass'], PASSWORD_DEFAULT)
            ];

            // Validation
            if ($this->userModel->cinExists($data['cin'])) {
                $errors[] = "Le CIN existe déjà";
            }

            if ($this->userModel->emailExists($data['email'])) {
                $errors[] = "L'email existe déjà";
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide";
            }

            if (empty($errors)) {
                $data['id_user'] = $this->userModel->generateUserId($data['type']);
                if ($this->userModel->create($data)) {
                    header('Location: UserController.php?action=list');
                    exit;
                }
            }
        }
        require_once __DIR__.'/../view/back/users/add.php';
    }

    public function edit() {
        $cin = $_GET['id'];
        $user = $this->userModel->getById($cin);
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'type' => $_POST['type'],
                'telephone' => $_POST['telephone'],
                'email' => $_POST['email']
            ];

            // Validation
            if ($user['email'] !== $data['email'] && $this->userModel->emailExists($data['email'])) {
                $errors[] = "L'email existe déjà";
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide";
            }

            if (empty($errors)) {
                if ($this->userModel->update($cin, $data)) {
                    header('Location: UserController.php?action=list');
                    exit;
                }
            }
        }
        require_once __DIR__.'/../view/back/users/edit.php';
    }

    public function delete() {
        $cin = $_GET['id'];
        if ($this->userModel->delete($cin)) {
            header('Location: UserController.php?action=list');
            exit;
        }
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? 'list';
        $this->$action();
    }
}

$controller = new UserController();
$controller->handleRequest();
?>