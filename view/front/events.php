<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/user_controller.php';
require_once __DIR__ . '/../../controller/reserveC.php';

// Start session for flash messages and user authentication
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $db = getDB();
    $userController = new UserController($db);
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $result = $userController->login($email, $password);
    
    if ($result === false) {
        $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
        header('Location: events.php');
        exit();
    }
    
    // Get user details
    $stmt = $db->prepare("SELECT id_user, type, prenom, nom, email FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Erreur de base de données lors de la récupération des informations utilisateur.";
        header('Location: events.php');
        exit();
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur non trouvé.";
        header('Location: events.php');
        exit();
    }
    
    // Store user info in session
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['user_type'] = $user['type'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = (!empty($user['prenom']) && !empty($user['nom'])) 
        ? $user['prenom'] . ' ' . $user['nom'] 
        : $email;
    
    session_regenerate_id(true);
    
    switch ($user['type']) {
        case 'admin':
            header('Location: ../back/user_back.php?user_id=' . $user['id_user']);
            break;
        case 'organisateur':
            header('Location: ../front/user_front.php?user_id=' . $user['id_user'] . '&type=organisateur');
            break;
        case 'participant':
            header('Location: ../front/user_front.php?user_id=' . $user['id_user'] . '&type=participant');
            break;
        default:
            header('Location: ../front/user_front.php?user_id=' . $user['id_user']);
    }
    exit();
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $db = getDB();
    $userController = new UserController($db);

    $required_fields = ['cin', 'nom', 'prenom', 'genre', 'telephone', 'date_naissance', 'email', 'type', 'mot_de_pass'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires";
            header('Location: events.php');
            exit();
        }
    }

    $userData = [
        'cin' => htmlspecialchars(trim($_POST['cin'])),
        'nom' => htmlspecialchars(trim($_POST['nom'])),
        'prenom' => htmlspecialchars(trim($_POST['prenom'])),
        'genre' => htmlspecialchars(trim($_POST['genre'])),
        'telephone' => htmlspecialchars(trim($_POST['telephone'])),
        'date_naissance' => htmlspecialchars(trim($_POST['date_naissance'])),
        'email' => filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
        'type' => htmlspecialchars(trim($_POST['type'])),
        'mot_de_pass' => $_POST['mot_de_pass']
    ];

    $result = $userController->createUser($userData);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
        header('Location: events.php');
        exit();
    } else {
        $_SESSION['error_message'] = $result['message'];
        header('Location: events.php');
        exit();
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: events.php');
    exit();
}

// Handle reservation cancellation
if (isset($_GET['action']) && $_GET['action'] === 'cancel_reservation' && isset($_GET['id_reservation']) && $isLoggedIn) {
    $reservationController = new ReservationC();
    $id_reservation = filter_var($_GET['id_reservation'], FILTER_VALIDATE_INT);
    if ($id_reservation === false) {
        $_SESSION['error_message'] = "ID de réservation invalide.";
        header('Location: my_reservations.php#reservations');
        exit();
    }
    // Verify the reservation belongs to the user
    $reservation = $reservationController->getReservationById($id_reservation);
    if ($reservation && $reservation[0]['id_user'] == $_SESSION['user_id']) {
        $result = $reservationController->supprimerReservation($id_reservation);
        if ($result) {
            $_SESSION['success_message'] = "Réservation annulée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'annulation de la réservation.";
        }
    } else {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à annuler cette réservation.";
    }
    header('Location: my_reservations.php#reservations');
    exit();
}

// Handle event deletion (for organizers)
if (isset($_GET['action']) && $_GET['action'] === 'delete_event' && isset($_GET['id_event']) && $isLoggedIn && $userType === 'organisateur') {
    $id_event = filter_var($_GET['id_event'], FILTER_VALIDATE_INT);
    if ($id_event === false) {
        $_SESSION['error_message'] = "ID d'événement invalide.";
        header('Location: events.php#my-events');
        exit();
    }
    // Verify the event belongs to the organizer
    $event = Event::getById($id_event); // Assuming Event class has a getById method
    if ($event && $event->getIdUser() == $_SESSION['user_id']) { // Assuming Event has getIdUser method
        // Check if there are any reservations for this event
        $reservationController = new ReservationC();
        $reservations = $reservationController->afficherReservationsParEvenement($id_event);
        if (!empty($reservations)) {
            $_SESSION['error_message'] = "Impossible de supprimer l'événement : il existe des réservations associées.";
            header('Location: events.php#my-events');
            exit();
        }
        // Delete the event
        $result = $event->delete(); // Assuming Event class has a delete method
        if ($result) {
            $_SESSION['success_message'] = "Événement supprimé avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression de l'événement.";
        }
    } else {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer cet événement.";
    }
    header('Location: events.php#my-events');
    exit();
}

// Get all events
$events = Event::getAll();

// Get organizer's events if user is an organizer
$organizerEvents = [];
if ($isLoggedIn && $userType === 'organisateur') {
    // Assuming a method to fetch events by organizer exists
    // If not, we can query directly
    $db = getDB();
    $sql = "SELECT * FROM evenement WHERE id_user = :id_user ORDER BY date DESC";
    try {
        $query = $db->prepare($sql);
        $query->execute(['id_user' => $_SESSION['user_id']]);
        $organizerEvents = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la récupération des événements : " . $e->getMessage();
        $organizerEvents = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Browse our upcoming events">
    <meta name="author" content="">
    <title>Events - Aurora Event</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .navbar {
            background-color:#301934;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar .nav-link {
            color: #602299;
        }

        .navbar .nav-link.active {
            color: #602299;
        }

        .navbar .nav-link:hover {
            color: #4a1a7a;
        }

        .events-section {
            background-color:#301934;
            padding: 30px;
            border-radius: 15px;
            margin-top: 20px;
        }

        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background-color: #fff;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .events-horizontal-scroll {
            display: flex;
            overflow-x: auto;
            gap: 30px;
            padding: 20px 0;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .events-horizontal-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-thumb {
            background: #381d51;
            border-radius: 10px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-thumb:hover {
            background: #381d51;
        }

        .event-card-horizontal {
            flex: 0 0 400px;
            scroll-snap-align: start;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .event-card-horizontal:hover {
            transform: translateY(-10px);
        }

        .event-card-horizontal img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-card-content-horizontal {
            padding: 20px;
        }

        .event-card-content-horizontal h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #381d51;
        }

        .event-card-content-horizontal p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 8px;
        }

        .event-time {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #381d51;
            font-weight: 600;
            margin: 10px 0;
        }

        .event-time i {
            font-size: 1.2rem;
        }

        .event-price {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #381d51;
            font-weight: 600;
            margin: 10px 0;
        }

        .event-price i {
            font-size: 1.2rem;
        }

        .btn-reserve, .btn-location {
            background-color: #381d51;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .btn-reserve:hover, .btn-location:hover {
            background-color: #381d51;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }

        .event-card-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .scroll-arrows {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .scroll-arrow {
            background-color: #381d51;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .scroll-arrow:hover {
            background-color: #381d51;
            transform: scale(1.1);
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #e0e0e0;
        }

        /* Modal Styles */
        .aurora-modal {
            background: linear-gradient(135deg, #1e1e2f 0%, #2a2a4a 100%);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .aurora-modal-header {
            background: transparent;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }

        .aurora-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .aurora-close {
            filter: invert(1);
        }

        .aurora-modal-body {
            padding: 2rem;
            color: #ffffff;
        }

        .aurora-label {
            color: #d1d1d1;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .aurora-input {
            background-color: #2a2a4a;
            border: 1px solid #3a3a5a;
            color: #ffffff;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .aurora-input:focus {
            background-color: #35355a;
            border-color: #602299;
            box-shadow: 0 0 8px rgba(96, 34, 153, 0.3);
            color: #ffffff;
            outline: none;
        }

        .aurora-btn {
            background: linear-gradient(135deg, #602299 0%, #8a2be2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            color: #ffffff;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .aurora-btn:hover {
            background: linear-gradient(135deg, #8a2be2 0%, #602299 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(96, 34, 153, 0.4);
        }

        .aurora-link {
            color: #b19cd9;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .aurora-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .aurora-subtitle {
            color: #d1d1d1;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .steps {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #3a3a5a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .step.active {
            background: #602299;
        }

        .step.completed {
            background: #4BB543;
        }

        .hidden {
            display: none;
        }

        /* Events Sections */
        .events-section {
            background-color:#301934;
            padding: 30px;
            border-radius: 15px;
            margin-top: 20px;
        }

        @media (max-width: 1000px) {
            .event-card-horizontal {
                flex: 0 0 300px;
            }
        }

        @media (max-width: 1000px) {
            .event-card-horizontal {
                flex: 0 0 280px;
            }
        }

        @media (max-width: 600px) {
            .event-card-horizontal {
                flex: 0 0 85%;
            }
        }
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
                                <?php echo $isLoggedIn ? "Bienvenue, " . htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') : "Welcome TO Aurora Event"; ?>
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
                            <a class="nav-link" href="produits.php">produits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="events.php">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="front.php">sponsoring</a>
                        </li>
                       
                        <li class="nav-item">
                            <a class="nav-link" href="pub.php">publicité</a>
                        </li>

                        <?php if ($isLoggedIn && $userType === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../back/user_back.php?user_id=<?php echo $_SESSION['user_id']; ?>">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="btn custom-btn d-lg-block d-none dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                Mon Compte
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userMenu">
                                <li><a class="dropdown-item" href="../front/user_front.php?user_id=<?php echo $_SESSION['user_id']; ?>&type=<?php echo $userType; ?>">Profil</a></li>
                                <li><a class="dropdown-item" href="my_reservations.php">Mes Réservations</a></li>
                                <?php if ($userType === 'organisateur'): ?>
                                    <li><a class="dropdown-item" href="myevents.php">Mes Événements</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="events.php?action=logout">Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="#" class="btn custom-btn d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#loginModal">Se connecter</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Display Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <section class="section-padding">
            <div class="container">
                <div class="events-section">
                    <div class="section-title">
                        <h2>Événements à venir</h2>
                    </div>

                   

                    <div class="events-grid-container">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="message success">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="message error">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if (empty($events)): ?>
                            <p style="color: #fff;">No events to display.</p>
                        <?php else: ?>
                            <div class="events-horizontal-scroll" id="eventsScroll">
                                <?php foreach ($events as $event): ?>
                                    <div class="event-card-horizontal">
                                        <?php if ($event->getImage()): ?>
                                            <?php $imagePath = htmlspecialchars($event->getImage()); ?>
                                            <img src="../../<?= $imagePath ?>" alt="Event Image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/350x250?text=No+Image" alt="Default Image">
                                        <?php endif; ?>
                                        <div class="event-card-content-horizontal">
                                            <h4><?= htmlspecialchars($event->getTitre()) ?></h4>
                                            <p><strong>Artist:</strong> <?= htmlspecialchars($event->getArtiste()) ?></p>
                                            <div class="event-time">
                                                <i class="far fa-clock"></i>
                                                <span><?= htmlspecialchars($event->getHeure() ?? 'Time not specified') ?></span>
                                            </div>
                                            <p><strong>Date:</strong> <?= htmlspecialchars($event->getDate()) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($event->getLieu()) ?></p>
                                            <div class="event-price">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span><?= htmlspecialchars(number_format($event->getPrix(), 2, '.', '')) ?> TND</span>
                                            </div>
                                            <p><?= htmlspecialchars($event->getDescription() ?? 'No description available.') ?></p>
                                            <div class="event-card-actions">
                                                <a href="reserve.php?id_event=<?= $event->getIdEvent() ?>" class="btn btn-reserve">
                                                    <i class="fas fa-ticket-alt"></i> Reserve Now
                                                </a>
                                                <a href="map.php?id_event=<?= $event->getIdEvent() ?>" class="btn btn-location">
                                                    <i class="fas fa-map-marker-alt"></i> Location
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="scroll-arrows">
                                <div class="scroll-arrow" onclick="scrollEvents(-350)">
                                    <i class="fas fa-chevron-left"></i>
                                </div>
                                <div class="scroll-arrow" onclick="scrollEvents(350)">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($isLoggedIn): ?>
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="my_reservations.php" class="btn aurora-btn">
                                <i class="fas fa-ticket-alt"></i> Voir Mes Réservations
                            </a>
                        </div>
                    <?php endif; ?>
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
                            <a class="nav-link" href="produits.php">produits</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link active" href="events.php">Events</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="sponsoring.php">spondoring</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="pub.php">publicité</a>
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

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title" id="loginModalLabel">
                        <i class="bi bi-stars me-2"></i>Connexion Aurora
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <form method="POST" action="events.php" id="loginForm" onsubmit="return validateLoginForm(event)">
                        <div class="form-group">
                            <label class="form-label aurora-label">
                                <i class="bi bi-envelope-fill me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control aurora-input" name="email" id="loginEmail" placeholder="votre@email.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label aurora-label">
                                <i class="bi bi-lock-fill me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control aurora-input" name="password" id="loginPassword" placeholder="••••••••">
                        </div>
                        <input type="hidden" name="action" value="login">
                        <button type="submit" class="btn aurora-btn w-100 py-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <a href="#" class="aurora-link" id="showResetPassword">Mot de passe oublié ?</a>
                    </div>
                    <div class="text-center mt-3">
                        <h6 class="aurora-subtitle">Nouveau sur Aurora Event ?</h6>
                        <a href="#" id="showSignupBtn" class="aurora-link">Inscrivez-vous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title" id="signupModalLabel">
                        <i class="bi bi-stars me-2"></i>Inscription Aurora
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <form method="POST" action="events.php" id="add-user-form" onsubmit="return validateForm(this)">
                        <div class="form-group">
                            <label class="form-label aurora-label">
                                <i class="bi bi-person-vcard me-2"></i>CIN
                            </label>
                            <input type="text" class="form-control aurora-input" name="cin" id="signupCin" placeholder="Votre CIN" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-person-fill me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control aurora-input" name="nom" id="signupLastName" placeholder="Votre nom" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-person-fill me-2"></i>Prénom
                                </label>
                                <input type="text" class="form-control aurora-input" name="prenom" id="signupFirstName" placeholder="Votre prénom" required>
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="signupGender" class="form-label aurora-label">
                                <i class="bi bi-gender-ambiguous me-2"></i>Genre
                            </label>
                            <select class="form-select aurora-input" name="genre" id="signupGender" required>
                                <option value="">Sélectionnez un genre</option>
                                <option value="homme">Homme</option>
                                <option value="femme">Femme</option>
                                <option value="autre">Autre</option>
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupPhone" class="form-label aurora-label">
                                <i class="bi bi-telephone-fill me-2"></i>Téléphone
                            </label>
                            <input type="tel" class="form-control aurora-input" name="telephone" id="signupPhone" placeholder="Votre numéro de téléphone" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="birthDate" class="form-label aurora-label">
                                <i class="bi bi-calendar-date me-2"></i>Date de naissance
                            </label>
                            <input type="date" class="form-control aurora-input" name="date_naissance" id="birthDate" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupEmail" class="form-label aurora-label">
                                <i class="bi bi-envelope-fill me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control aurora-input" name="email" id="signupEmail" placeholder="votre@email.com" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupType" class="form-label aurora-label">
                                <i class="bi bi-person-badge me-2"></i>Type
                            </label>
                            <select class="form-select aurora-input" name="type" id="signupType" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="participant">Participant</option>
                                <option value="organisateur">Organisateur</option>
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupPassword" class="form-label aurora-label">
                                <i class="bi bi-lock-fill me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control aurora-input" name="mot_de_pass" id="signupPassword" placeholder="••••••••" required>
                            <div class="error-message"></div>
                        </div>
                        <input type="hidden" name="signup" value="1">
                        <button type="submit" class="btn aurora-btn w-100 py-3">
                            <i class="bi bi-person-plus-fill me-2"></i>S'inscrire
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <h6 class="aurora-subtitle">Déjà un compte ?</h6>
                        <a href="#" id="showLoginBtn" class="aurora-link">Connectez-vous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title">
                        <i class="bi bi-key-fill me-2"></i>Réinitialisation du mot de passe
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <!-- Step 1: Request Reset -->
                    <div id="reset-step1">
                        <div class="steps mb-4">
                            <div class="step active">1</div>
                            <div class="step">2</div>
                            <div class="step">3</div>
                        </div>
                        <div class="form-group">
                            <label for="reset-email" class="form-label aurora-label">
                                <i class="bi bi-envelope-fill me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control aurora-input" id="reset-email" placeholder="votre@email.com">
                            <div id="reset-email-error" class="error-message hidden"></div>
                        </div>
                        <button id="reset-submit-email" class="btn aurora-btn w-100 py-3 mt-3">
                            <i class="bi bi-arrow-right me-2"></i>Continuer
                        </button>
                    </div>
                    <!-- Step 2: Verify Code -->
                    <div id="reset-step2" class="hidden">
                        <div class="steps mb-4">
                            <div class="step completed">1</div>
                            <div class="step active">2</div>
                            <div class="step">3</div>
                        </div>
                        <p class="text-center text-white mb-4">Nous avons envoyé un code de vérification à <strong id="reset-user-email"></strong></p>
                        <div class="form-group">
                            <label for="reset-code" class="form-label aurora-label">
                                <i class="bi bi-shield-lock-fill me-2"></i>Code de vérification
                            </label>
                            <input type="text" class="form-control aurora-input" id="reset-code" placeholder="Entrez le code à 6 chiffres" maxlength="6">
                            <div id="reset-code-error" class="error-message hidden"></div>
                        </div>
                        <button id="reset-submit-code" class="btn aurora-btn w-100 py-3 mt-3">
                            <i class="bi bi-check-circle me-2"></i>Vérifier le code
                        </button>
                        <button id="reset-resend-code" class="btn btn-secondary w-100 py-3 mt-2">
                            <i class="bi bi-arrow-repeat me-2"></i>Renvoyer le code
                        </button>
                    </div>
                    <!-- Step 3: New Password -->
                    <div id="reset-step3" class="hidden">
                        <div class="steps mb-4">
                            <div class="step completed">1</div>
                            <div class="step completed">2</div>
                            <div class="step active">3</div>
                        </div>
                        <div class="form-group">
                            <label for="new-password" class="form-label aurora-label">
                                <i class="bi bi-lock-fill me-2"></i>Nouveau mot de passe
                            </label>
                            <input type="password" class="form-control aurora-input" id="new-password" placeholder="Au moins 8 caractères">
                            <div id="new-password-error" class="error-message hidden"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm-new-password" class="form-label aurora-label">
                                <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
                            </label>
                            <input type="password" class="form-control aurora-input" id="confirm-new-password" placeholder="Retapez votre mot de passe">
                        </div>
                        <button id="reset-submit-password" class="btn aurora-btn w-100 py-3 mt-3">
                            <i class="bi bi-check-circle me-2"></i>Réinitialiser le mot de passe
                        </button>
                    </div>
                    <!-- Final Confirmation -->
                    <div id="reset-confirmation" class="hidden text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #4BB543;"></i>
                        </div>
                        <h5 class="aurora-title mb-3">Mot de passe réinitialisé!</h5>
                        <p class="text-white">Votre mot de passe a été modifié avec succès.</p>
                        <button class="btn aurora-btn mt-3" data-bs-dismiss="modal">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Retour à la connexion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/click-scroll.js"></script>
    <script src="js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function scrollEvents(offset) {
            const container = document.getElementById('eventsScroll');
            container.scrollBy({
                left: offset,
                behavior: 'smooth'
            });
        }

        document.addEventListener('keydown', function(e) {
            const container = document.getElementById('eventsScroll');
            if (e.key === 'ArrowLeft') {
                scrollEvents(-350);
            } else if (e.key === 'ArrowRight') {
                scrollEvents(350);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        window.scrollTo({
                            top: target.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Login form validation
            function validateLoginForm(event) {
                event.preventDefault();
                const email = document.getElementById('loginEmail');
                const password = document.getElementById('loginPassword');
                let isValid = true;

                clearErrors();

                if (!email.value.trim()) {
                    showError(email, 'L\'email est requis');
                    isValid = false;
                } else if (!isValidEmail(email.value.trim())) {
                    showError(email, 'Veuillez entrer un email valide');
                    isValid = false;
                }

                if (!password.value.trim()) {
                    showError(password, 'Le mot de passe est requis');
                    isValid = false;
                } else if (password.value.length < 6) {
                    showError(password, 'Le mot de passe doit contenir au moins 6 caractères');
                    isValid = false;
                }

                if (isValid) {
                    document.getElementById('loginForm').submit();
                }
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(input, message) {
                const formGroup = input.closest('.form-group');
                const error = document.createElement('div');
                error.className = 'error-message';
                error.innerText = message;
                formGroup.appendChild(error);
                input.classList.add('is-invalid');
            }

            function clearErrors() {
                const errorMessages = document.querySelectorAll('.error-message');
                const invalidInputs = document.querySelectorAll('.is-invalid');
                errorMessages.forEach(error => error.remove());
                invalidInputs.forEach(input => input.classList.remove('is-invalid'));
            }

            // Signup form validation
            function validateForm(form) {
                let isValid = true;
                clearErrors();

                const inputs = form.querySelectorAll('input[required], select[required]');
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        showError(input, 'Ce champ est requis');
                        isValid = false;
                    }
                });

                const email = form.querySelector('#signupEmail');
                if (email && !isValidEmail(email.value.trim())) {
                    showError(email, 'Veuillez entrer un email valide');
                    isValid = false;
                }

                const password = form.querySelector('#signupPassword');
                if (password && password.value.length < 6) {
                    showError(password, 'Le mot de passe doit contenir au moins 6 caractères');
                    isValid = false;
                }

                return isValid;
            }

            // Modal navigation
            document.getElementById('showSignupBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                loginModal.hide();
                var signupModal = new bootstrap.Modal(document.getElementById('signupModal'));
                signupModal.show();
            });

            document.getElementById('showLoginBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var signupModal = bootstrap.Modal.getInstance(document.getElementById('signupModal'));
                signupModal.hide();
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            });

            document.getElementById('showResetPassword').addEventListener('click', function(e) {
                e.preventDefault();
                var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                loginModal.hide();
                var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                resetModal.show();
            });

            // Placeholder for password reset
            document.getElementById('reset-submit-email').addEventListener('click', function() {
                alert('Password reset email sending is not implemented.');
            });
        });
    </script>
</body>
</html>