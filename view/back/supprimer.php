<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../model/Event.php';

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: afficher.php?error=ID invalide");
    exit;
}

$id = (int)$_GET['id'];

try {
    // Récupérer l'événement avant suppression pour gérer l'image
    $event = Event::getById($id);
    
    if (!$event) {
        header("Location: afficher.php?error=Événement introuvable");
        exit;
    }

    // Supprimer l'image associée si elle existe
    $imagePath = __DIR__.'/../../uploads/'.$event->getImage();
    if ($event->getImage() && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Supprimer l'événement de la base de données
    if ($event->delete()) {
        header("Location: afficher.php?success=Événement supprimé avec succès");
    } else {
        header("Location: afficher.php?error=Échec de la suppression");
    }
} catch (Exception $e) {
    header("Location: afficher.php?error=" . urlencode($e->getMessage()));
}
exit;