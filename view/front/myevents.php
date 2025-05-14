<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../controller/reserveC.php';

// Start session for user authentication
session_start();

// Check if user is logged in and is an organizer
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : null;

if (!$isLoggedIn || $userType !== 'organisateur') {
    $_SESSION['error_message'] = "Vous devez être connecté en tant qu'organisateur pour accéder à cette page.";
    header('Location: events.php');
    exit();
}

// Handle event deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_event' && isset($_GET['id_event'])) {
    $id_event = filter_var($_GET['id_event'], FILTER_VALIDATE_INT);
    if ($id_event === false) {
        $_SESSION['error_message'] = "ID d'événement invalide.";
        header('Location: myevents.php');
        exit();
    }
    // Verify the event belongs to the organizer
    $event = Event::getById($id_event);
    if ($event && $event->getIdUser() == $_SESSION['user_id']) {
        // Check for reservations
        $reservationController = new ReservationC();
        $reservations = $reservationController->afficherReservationsParEvenement($id_event);
        if (!empty($reservations)) {
            $_SESSION['error_message'] = "Impossible de supprimer l'événement : il existe des réservations associées.";
            header('Location: myevents.php');
            exit();
        }
        // Delete the event
        $result = $event->delete();
        if ($result) {
            $_SESSION['success_message'] = "Événement supprimé avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression de l'événement.";
        }
    } else {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer cet événement.";
    }
    header('Location: myevents.php');
    exit();
}

// Get organizer's events
$organizerEvents = [];
$db = getDB();
$sql = "SELECT * FROM evenement WHERE id_user = :id_user ORDER BY date DESC";
try {
    $query = $db->prepare($sql);
    $query->execute(['id_user' => $_SESSION['user_id']]);
    $organizerEvents = $query->fetchAll(PDO::FETCH_ASSOC);
    // Debugging: Log the query results
    error_log("User ID: " . $_SESSION['user_id'] . ", Events fetched: " . count($organizerEvents));
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des événements : " . $e->getMessage();
    error_log("Event fetch error: " . $e->getMessage());
    $organizerEvents = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Manage your events">
    <meta name="author" content="">
    <title>My Events - Aurora Event</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #301934; padding: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar .nav-link { color: #602299; }
        .navbar .nav-link.active { color: #602299; }
        .navbar .nav-link:hover { color: #4a1a7a; }
        .my-events-section { background-color: #602299; padding: 30px; border-radius: 15px; margin-top: 20px; }
        .section-title { position: relative; margin-bottom: 50px; text-align: center; }
        .section-title h2 { font-size: 2.5rem; font-weight: 700; color: #fff; position: relative; display: inline-block; }
        .section-title h2::after { content: ''; position: absolute; width: 50px; height: 3px; background-color: #fff; bottom: -10px; left: 50%; transform: translateX(-50%); }
        .table-container { background-color: #602299; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #ffffff; }
        .table th { background-color: #381d51; color: white; font-size: 13px; }
        .table th, .table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ddd; color: #333; }
        .table tr:hover { background-color: rgb(240, 240, 240); }
        .action-buttons { display: flex; gap: 8px; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: white; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; transition: background-color 0.3s; }
        .btn-add { background-color: #28a745; padding: 10px 15px; margin-bottom: 20px; }
        .btn-add:hover { background-color: #218838; }
        .btn-edit { background-color: #ffc107; }
        .btn-edit:hover { background-color: #e0a800; }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .aurora-btn { background: linear-gradient(135deg, #602299 0%, #8a2be2 100%); border: none; border-radius: 8px; padding: 0.75rem; color: #ffffff; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; justify-content: center; }
        .aurora-btn:hover { background: linear-gradient(135deg, #8a2be2 0%, #602299 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(96, 34, 153, 0.4); }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; font-weight: 500; margin-bottom: 1rem; transition: color 0.3s; }
        .back-link:hover { color: #e0e0e0; }
    </style>
</head>
<body>
    <main>
        <header class="site-header">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex flex-wrap">
                        <p class="d-flex me-4 mb-0">
                            <i class="bi-person custom-icon me-2"></i>
                            <strong class="text-dark">
                                Bienvenue, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Organisateur') ?>
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="afficher.php">
                    <img src="images/logo.png" alt="Logo d'Auroura Event" style="height: 50px; margin-right: 10px">
                    Aurora Event
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                        
                        <li class="nav-item">
                            <a class="nav-link" href="produits.php">Produits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="events.php">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sponsoring.php">Sponsoring</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pub.php">Publicité</a>
                        </li>
                    </ul>
                    <div class="dropdown">
                        <a class="btn custom-btn d-lg-block d-none dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            Mon Compte
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="user_front.php?user_id=<?= $_SESSION['user_id'] ?>&type=organisateur">Profil</a></li>
                            <li><a class="dropdown-item" href="my_reservations.php">Mes Réservations</a></li>
                            <li><a class="dropdown-item" href="myevents.php">Mes Événements</a></li>
                            <li><a class="dropdown-item" href="events.php?action=logout">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Display Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <section class="section-padding">
            <div class="container">
                <div class="my-events-section">
                    <div class="section-title">
                        <h2>Mes Événements</h2>
                    </div>
                    <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux Événements</a>
                    <div class="table-container">
                        <div style="text-align: right; margin-bottom: 15px;">
                            <a href="ajouter_front.php" class="btn btn-add">
                                <i class="fas fa-plus"></i> Ajouter un Événement
                            </a>
                        </div>
                        <?php if (empty($organizerEvents)): ?>
                            <p style="color: #fff;">Aucun événement à afficher.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Artiste</th>
                                        <th>Date</th>
                                        <th>Lieu</th>
                                        <th>Prix (TND)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($organizerEvents as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['id_event']) ?></td>
                                            <td><?= htmlspecialchars($event['titre']) ?></td>
                                            <td><?= htmlspecialchars($event['artiste'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($event['date']) ?></td>
                                            <td><?= htmlspecialchars($event['lieu']) ?></td>
                                            <td><?= htmlspecialchars(number_format($event['prix'], 2)) ?></td>
                                            <td class="action-buttons">
                                                <a href="../back/modifier.php?id_event=<?= $event['id_event'] ?>" class="btn btn-edit">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                <a href="myevents.php?action=delete_event&id_event=<?= $event['id_event'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="site-footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 d-flex align-items-center">
                        <img src="images/logo.png" alt="Aurora Event Logo" style="height: 50px; margin-right: 10px">
                        <h2 class="text-white mb-0">Aurora Event</h2>
                    </div>
                    <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center">
                        <ul class="social-icon d-flex justify-content-lg-end">
                            <li class="social-icon-item">
                                <a href="https://twitter.com/share?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-twitter"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-facebook"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.instagram.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-instagram"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.youtube.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-youtube"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.pinterest.com/pin/create/button/?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-pinterest"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-12 mb-4 pb-2">
                    <h5 class="site-footer-title mb-3">Links</h5>
                    <ul class="site-footer-links">
                       
                        <li class="site-footer-link-item">
                            <a href="produits.php" class="site-footer-link">Produits</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a href="events.php" class="site-footer-link">Events</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a href="sponsoring.php" class="site-footer-link">Sponsoring</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a href="pub.php" class="site-footer-link">Publicité</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Have a question?</h5>
                    <p class="text-white d-flex mb-1">
                        <a href="tel: +216 94-166-711" class="site-footer-link">
                            +216 94-166-711
                        </a>
                    </p>
                    <p class="text-white d-flex">
                        <a href="mailto:auroraevent@gmail.com" class="site-footer-link">
                            auroraevent@gmail.com
                        </a>
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Location</h5>
                    <p class="text-white d-flex mt-3 mb-2" style="font-size: 0.9rem; white-space: nowrap;">
                        Av. Fethi Zouhir, Cebalat Ben Ammar 2083
                    </p>
                    <a class="link-fx-1 color-contrast-higher mt-3" href="https://www.google.com/maps?q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" target="_blank">
                        <span>Our Maps</span>
                        <svg class="icon" viewBox="0 0 32 32" aria-hidden="true">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="16" cy="16" r="15.5"></circle>
                                <line x1="10" y1="18" x2="16" y2="12"></line>
                                <line x1="16" y1="12" x2="22" y2="18"></line>
                            </g>
                        </svg>
                    </a>
                    <iframe width="100%" height="300" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_API_KEY&q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        <div class="site-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mt-lg-5">
                        <ul class="site-footer-links">
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Terms & Conditions</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Privacy Policy</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Your Feedback</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/click-scroll.js"></script>
    <script src="js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>