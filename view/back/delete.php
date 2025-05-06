<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

session_start();

// Optional: Check if user is authenticated (adjust based on your auth system)
// Example: Assumes a user_id in session; modify to match your authentication setup, e.g., $_SESSION['user'] or role-based check
if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
    $_SESSION['error'] = 'Vous devez être connecté pour supprimer une réservation.';
    header('Location: login.php');
    exit;
}

// Get the reservation ID from the URL as a string to avoid integer overflow
$reservation_id_input = filter_input(INPUT_GET, 'id_reservation', FILTER_SANITIZE_STRING);

// Validate the reservation ID as a string
if ($reservation_id_input === null || !ctype_digit($reservation_id_input) || $reservation_id_input === '0' || ltrim($reservation_id_input, '0') === '') {
    error_log("Invalid id_reservation received: " . ($reservation_id_input ?? 'null'));
    $_SESSION['error'] = 'ID de réservation invalide. Veuillez sélectionner une réservation valide.';
    header('Location: afficher.php');
    exit;
}

// Keep as string to avoid overflow
$reservation_id = $reservation_id_input;

// Check if the reservation exists
$reservation = Reservation::getById($reservation_id);

if (!$reservation) {
    error_log("No reservation found for id_reservation: " . $reservation_id);
    $_SESSION['error'] = "Aucune réservation trouvée avec l'ID $reservation_id.";
    header('Location: afficher.php');
    exit;
}

// Attempt to delete the reservation
try {
    if ($reservation->delete()) {
        $_SESSION['success'] = 'Réservation supprimée avec succès !';
    } else {
        $_SESSION['error'] = 'Échec de la suppression de la réservation. Veuillez réessayer.';
    }
} catch (Exception $e) {
    error_log("Error deleting reservation ID $reservation_id: " . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la suppression : ' . htmlspecialchars($e->getMessage());
}

header('Location: afficher.php');
exit;
?>