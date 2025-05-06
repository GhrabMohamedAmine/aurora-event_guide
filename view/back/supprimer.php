<?php
// C:\xampp1\htdocs\evenment\view\back\supprimer.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

// Optional: Check for autoloader (only if required by config.php or Event.php)
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // If autoloader is not needed, proceed; otherwise, show error
    // Comment out the die() if autoloader is not required
    // die("Error: Autoloader not found at: $autoloadPath. Please run 'composer install' in C:\\xampp1\\htdocs\\evenment.");
}

// Verify if ID is present in the URL
if (!isset($_GET['id_event']) || !is_numeric($_GET['id_event'])) {
    header("Location: afficher.php?error=ID invalide");
    exit;
}

$id_event = (int)$_GET['id_event'];

try {
    // Retrieve the event before deletion
    $event = Event::getById($id_event);
    
    if (!$event) {
        header("Location: afficher.php?error=Événement introuvable");
        exit;
    }

    // Delete the associated image if it exists
    $imagePath = __DIR__ . '/../../Uploads/' . $event->getImage();
    if ($event->getImage() && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete the event from the database
    if ($event->delete()) {
        header("Location: afficher.php?success=Événement supprimé avec succès");
    } else {
        header("Location: afficher.php?error=Échec de la suppression");
    }
} catch (Exception $e) {
    header("Location: afficher.php?error=" . urlencode($e->getMessage()));
}
exit;
?>