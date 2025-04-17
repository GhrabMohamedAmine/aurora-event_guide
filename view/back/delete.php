<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../model/reserve.php';

session_start();

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de réservation invalide";
    header("Location: afficher.php");
    exit;
}

$id_reservation = (int)$_GET['id'];

try {
    // Récupérer la réservation avant suppression
    $reservation = Reservation::getById($id_reservation);
    
    if (!$reservation) {
        $_SESSION['error'] = "Réservation introuvable";
        header("Location: afficher.php");
        exit;
    }

    // Supprimer la réservation de la base de données
    if ($reservation->delete()) {
        $_SESSION['success'] = "Réservation #".$reservation->getIdReservation()." supprimée avec succès";
    } else {
        $_SESSION['error'] = "Échec de la suppression de la réservation";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
}

header("Location: afficher.php");
exit;
?>