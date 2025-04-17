<?php
require_once __DIR__.'/../model/Event.php';

class EventController {
    private $model;

    public function __construct() {
        $this->model = new Event();
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        try {
            switch ($action) {
                case 'add':
                    $this->addEvent();
                    break;
                case 'edit':
                    $this->editEvent();
                    break;
                case 'delete':
                    $this->deleteEvent();
                    break;
                case 'get':
                    $this->getEvent();
                    break;
                case 'list':
                default:
                    $this->listEvents();
                    break;
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    private function addEvent() {
        $required = ['titre', 'artiste', 'date', 'heure', 'lieu', 'description'];
        $data = $this->sanitizeInput($_POST, $required);

        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadImage();
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }
        
        $event = new Event();
        $event->hydrate($data);
        
        $success = $event->create();
        $this->jsonResponse($success, $success ? 'Événement ajouté avec succès' : 'Erreur lors de l\'ajout');
    }

    private function editEvent() {
        $required = ['id', 'titre', 'artiste', 'date', 'heure', 'lieu', 'description'];
        $data = $this->sanitizeInput($_POST, $required);

        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->uploadImage();
            if ($imagePath) {
                $data['image'] = $imagePath;
                if (!empty($_POST['old_image'])) {
                    $this->deleteImage($_POST['old_image']);
                }
            }
        } elseif (!empty($_POST['old_image'])) {
            $data['image'] = $_POST['old_image'];
        }
        
        $event = new Event();
        $event->hydrate($data);
        
        $success = $event->update();
        $this->jsonResponse($success, $success ? 'Événement modifié avec succès' : 'Erreur lors de la modification');
    }

    private function deleteEvent() {
        if (!isset($_POST['id'])) {
            $this->jsonResponse(false, 'ID manquant');
            return;
        }
    
        $id = (int)$_POST['id'];
        $event = new Event();
        $event->setId($id);
    
        if (!empty($_POST['image'])) {
            $this->deleteImage($_POST['image']);
        }
    
        try {
            $success = $event->delete();
            $this->jsonResponse($success, $success ? 'Supprimé' : 'Échec');
        } catch (Exception $e) {
            $this->jsonResponse(false, 'Erreur: ' . $e->getMessage());
        }
    }

    private function getEvent() {
        if (!isset($_GET['id'])) {
            throw new Exception('ID manquant');
        }

        $id = (int)$_GET['id'];
        $event = $this->model->getById($id);

        if (!$event) {
            throw new Exception('Événement non trouvé');
        }

        $this->jsonResponse(true, '', $event);
    }

    private function listEvents() {
        $events = $this->model->getAll();
        $this->jsonResponse(true, '', $events);
    }

    private function sanitizeInput($input, $requiredFields = []) {
        $data = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Le champ $field est requis");
            }
            
            $value = is_array($input[$field]) ? $input[$field] : trim($input[$field]);
            
            if (empty($value) && $value !== '0' && $field !== 'description') {
                throw new Exception("Le champ $field ne peut pas être vide");
            }
            
            switch ($field) {
                case 'id':
                    $data[$field] = (int)$value;
                    break;
                case 'date':
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        throw new Exception("Format de date invalide (YYYY-MM-DD attendu)");
                    }
                    $data[$field] = $value;
                    break;
                case 'heure':
                    if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
                        throw new Exception("Format d'heure invalide (HH:MM attendu)");
                    }
                    $data[$field] = $value;
                    break;
                case 'description':
                    $data[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    break;
                default:
                    $data[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $data;
    }

    private function uploadImage() {
        $targetDir = __DIR__ . '/../uploads/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            throw new Exception("Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.");
        }
        
        if ($_FILES['image']['size'] > 2000000) {
            throw new Exception("La taille du fichier ne doit pas dépasser 2MB.");
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            return 'uploads/' . $fileName;
        } else {
            throw new Exception("Erreur lors de l'upload de l'image.");
        }
    }
    
    private function deleteImage($imagePath) {
        $fullPath = __DIR__ . '/../' . $imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private function jsonResponse($success, $message = '', $data = []) {
        header('Content-Type: application/json');
        http_response_code($success ? 200 : 400);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new EventController();
    $controller->handleRequest();
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>