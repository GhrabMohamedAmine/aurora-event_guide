<?php
require_once __DIR__.'/../reserve.php';

class ReservationController {
    private $model;

    public function __construct() {
        $this->model = new Reservation();
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        try {
            switch ($action) {
                case 'add':
                    $this->addReservation();
                    break;
                case 'edit':
                    $this->editReservation();
                    break;
                case 'delete':
                    $this->deleteReservation();
                    break;
                case 'get':
                    $this->getReservation();
                    break;
                case 'list':
                default:
                    $this->listReservations();
                    break;
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, $e->getMessage());
        }
    }

    private function addReservation() {
        $required = ['id_event', 'nom', 'telephone', 'nombre_places', 'categorie', 'mode_paiement'];
        $data = $this->sanitizeInput($_POST, $required);
        
        $reservation = new Reservation();
        $reservation->hydrate($data);
        
        $success = $reservation->create();
        $this->jsonResponse($success, $success ? 'Réservation ajoutée avec succès' : 'Erreur lors de l\'ajout');
    }

    private function editReservation() {
        $required = ['id_reservation', 'id_event', 'nom', 'telephone', 'nombre_places', 'categorie', 'mode_paiement'];
        $data = $this->sanitizeInput($_POST, $required);
        
        $reservation = new Reservation();
        $reservation->hydrate($data);
        
        $success = $reservation->update();
        $this->jsonResponse($success, $success ? 'Réservation modifiée avec succès' : 'Erreur lors de la modification');
    }

    private function deleteReservation() {
        if (!isset($_POST['id_reservation'])) {
            $this->jsonResponse(false, 'ID manquant');
            return;
        }
    
        $id = (int)$_POST['id_reservation'];
        $reservation = new Reservation();
        $reservation->setIdReservation($id);
    
        try {
            $success = $reservation->delete();
            $this->jsonResponse($success, $success ? 'Supprimé' : 'Échec');
        } catch (Exception $e) {
            $this->jsonResponse(false, 'Erreur: ' . $e->getMessage());
        }
    }

    private function getReservation() {
        if (!isset($_GET['id_reservation'])) {
            throw new Exception('ID manquant');
        }

        $id = (int)$_GET['id_reservation'];
        $reservation = $this->model->getById($id);

        if (!$reservation) {
            throw new Exception('Réservation non trouvée');
        }

        $this->jsonResponse(true, '', $reservation);
    }

    private function listReservations() {
        // Si on veut filtrer par événement
        if (isset($_GET['id_event'])) {
            $id_event = (int)$_GET['id_event'];
            $reservations = $this->model->getByEventId($id_event);
        } else {
            $reservations = $this->model->getAll();
        }
        
        $this->jsonResponse(true, '', $reservations);
    }

    private function sanitizeInput($input, $requiredFields = []) {
        $data = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Le champ $field est requis");
            }
            
            $value = is_array($input[$field]) ? $input[$field] : trim($input[$field]);
            
            if (empty($value) && $value !== '0') {
                throw new Exception("Le champ $field ne peut pas être vide");
            }
            
            switch ($field) {
                case 'id_reservation':
                case 'id_event':
                case 'nombre_places':
                    $data[$field] = (int)$value;
                    break;
                case 'telephone':
                    if (!preg_match('/^[0-9]{10,15}$/', $value)) {
                        throw new Exception("Format de téléphone invalide");
                    }
                    $data[$field] = $value;
                    break;
                default:
                    $data[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $data;
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
    $controller = new ReservationController();
    $controller->handleRequest();
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}