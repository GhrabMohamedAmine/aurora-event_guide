<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

// Start session for user authentication
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
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

// Get user reservations
$reservationController = new ReservationC();
$userReservations = $reservationController->getReservationsByUserId($_SESSION['user_id']);

// Prepare events data for FullCalendar
$events = [];
foreach ($userReservations as $reservation) {
    $event = [
        'title' => htmlspecialchars($reservation['event_title']),
        'start' => isset($reservation['start_date']) ? $reservation['start_date'] : (isset($reservation['event_date']) ? $reservation['event_date'] : date('Y-m-d')),
        'extendedProps' => [
            'id_reservation' => $reservation['id_reservation'],
            'places' => $reservation['nombre_places'] ?? 'N/A',
            'category' => $reservation['categorie'] ?? 'N/A',
            'payment' => $reservation['mode_paiement'] ?? 'N/A',
            'total' => isset($reservation['total']) ? number_format($reservation['total'], 2) : 'N/A'
        ]
    ];
    $events[] = $event;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Mes Réservations et Calendrier">
    <meta name="author" content="">
    <title>Mes Réservations - Aurora Event</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <style>
        /* General Page Styles */
        body {
            background: #602299;
            color: #fff;
        }

        .container-fluid {
            padding: 20px;
            min-height: calc(100vh - 200px);
        }

        .dashboard {
            display: flex;
            gap: 30px;
            justify-content: space-between;
            margin-top: 20px;
        }

        /* Reservations Section */
        .reservations-section {
            flex: 1;
            background:  #301934;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 48%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .section-title h2 {
            font-size: 2rem;
            color: #fff;
            font-weight: 700;
            position: relative;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: #fff;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 15px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #e0e0e0;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            width: 2px;
            background: #fff;
            top: 0;
            bottom: 0;
            left: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 30px;
        }

        .timeline-marker {
            position: absolute;
            width: 12px;
            height: 12px;
            background: #fff;
            border: 3px solid #301934;
            border-radius: 50%;
            left: 4px;
            top: 5px;
            z-index: 1;
        }

        .reservation-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .reservation-card:hover {
            transform: translateY(-5px);
        }

        .reservation-card-content h4 {
            font-size: 1.4rem;
            color: #602299;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .reservation-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .reservation-detail i {
            color: #602299;
            font-size: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(90deg, #ffc107, #ffca2c);
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #ffca2c, #ffc107);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        .btn-delete {
            background: linear-gradient(90deg, #dc3545, #e4606d);
        }

        .btn-delete:hover {
            background: linear-gradient(90deg, #e4606d, #dc3545);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .no-reservations {
            color: #ccc;
            text-align: center;
            font-size: 1.1rem;
            margin-top: 20px;
            font-style: italic;
        }

        /* Calendar Section */
        .calendar-section {
            flex: 1;
            background:  #301934;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 48%;
        }

        #calendar {
            width: 100%;
            background: #fff;
            border-radius: 10px;
        }

        .fc-event {
            background: #602299 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 5px !important;
            padding: 4px !important;
            font-size: 0.9rem !important;
        }

        .fc-daygrid-event:hover {
            background: #fff !important;
            color: #602299 !important;
        }

        .fc-button {
            background: #602299 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 20px !important;
            padding: 6px 12px !important;
        }

        .fc-button:hover {
            background: #fff !important;
            color: #602299 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Messages */
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

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
                gap: 20px;
            }

            .reservations-section, .calendar-section {
                max-width: 100%;
            }

            .section-title h2 {
                font-size: 1.6rem;
            }

            #calendar {
                padding: 10px;
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
                                <?php echo "Bienvenue, " . htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?>
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
                            <a class="nav-link" href="afficher.php#section_1">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="afficher.php#section_2">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="events.php">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="afficher.php#section_4">Reviews</a>
                        </li>
                        <?php if ($_SESSION['user_type'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../back/user_back.php?user_id=<?php echo $_SESSION['user_id']; ?>">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="dropdown">
                        <a class="btn custom-btn d-lg-block d-none dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            Mon Compte
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="../front/user_front.php?user_id=<?php echo $_SESSION['user_id']; ?>&type=<?php echo $_SESSION['user_type']; ?>">Profil</a></li>
                            <li><a class="dropdown-item" href="my_reservations.php">Mes Réservations</a></li>
                            <?php if ($_SESSION['user_type'] === 'organisateur'): ?>
                                <li><a class="dropdown-item" href="events.php#my-events">Mes Événements</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="events.php?action=logout">Déconnexion</a></li>
                        </ul>
                    </div>
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

        <!-- Dashboard Layout -->
        <section class="section-padding">
            <div class="container-fluid">
                <div class="dashboard">
                    <!-- Reservations Section -->
                    <div class="reservations-section" id="reservations">
                        <div class="section-title">
                            <h2>Mes Réservations</h2>
                        </div>
                        <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux Événements</a>
                        <div class="timeline">
                            <?php if (empty($userReservations)): ?>
                                <p class="no-reservations">Aucune réservation à afficher.</p>
                            <?php else: ?>
                                <?php foreach ($userReservations as $index => $reservation): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="reservation-card">
                                            <div class="reservation-card-content">
                                                <h4><?= htmlspecialchars($reservation['event_title']) ?></h4>
                                                <div class="reservation-detail">
                                                    <i class="fas fa-ticket-alt"></i>
                                                    <span><strong>ID:</strong> <?= htmlspecialchars($reservation['id_reservation']) ?></span>
                                                </div>
                                                <div class="reservation-detail">
                                                    <i class="fas fa-users"></i>
                                                    <span><strong>Places:</strong> <?= htmlspecialchars($reservation['nombre_places'] ?? 'N/A') ?></span>
                                                </div>
                                                <div class="reservation-detail">
                                                    <i class="fas fa-layer-group"></i>
                                                    <span><strong>Catégorie:</strong> <?= htmlspecialchars($reservation['categorie'] ?? 'N/A') ?></span>
                                                </div>
                                                <div class="reservation-detail">
                                                    <i class="fas fa-credit-card"></i>
                                                    <span><strong>Paiement:</strong> <?= htmlspecialchars($reservation['mode_paiement'] ?? 'N/A') ?></span>
                                                </div>
                                                <div class="reservation-detail">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    <span><strong>Total:</strong> <?= htmlspecialchars(isset($reservation['total']) ? number_format($reservation['total'], 2) : 'N/A') ?> TND</span>
                                                </div>
                                                <div class="action-buttons">
                                                    <a href="reserve.php?id_reservation=<?= $reservation['id_reservation'] ?>&id_event=<?= $reservation['id_event'] ?>" class="btn btn-edit">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a>
                                                    <a href="my_reservations.php?action=cancel_reservation&id_reservation=<?= $reservation['id_reservation'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                        <i class="fas fa-trash"></i> Annuler
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Calendar Section -->
                    <div class="calendar-section">
                        <div class="section-title">
                            <h2>Calendrier des Réservations</h2>
                        </div>
                        <div id="calendar"></div>
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
                            <a href="afficher.php#section_1" class="site-footer-link">Home</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="afficher.php#section_2">About</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="events.php">Events</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="afficher.php#section_4">Reviews</a>
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

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

    <script>
        // Timeline Animation
        document.addEventListener('DOMContentLoaded', function() {
            const timelineItems = document.querySelectorAll('.timeline-item');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            timelineItems.forEach(item => {
                observer.observe(item);
            });

            // Initialize FullCalendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($events); ?>,
                eventClick: function(info) {
                    var event = info.event;
                    var props = event.extendedProps;
                    alert(
                        'Événement: ' + event.title + '\n' +
                        'Date: ' + event.start.toISOString().split('T')[0] + '\n' +
                        'ID Réservation: ' + props.id_reservation + '\n' +
                        'Places: ' + props.places + '\n' +
                        'Catégorie: ' + props.category + '\n' +
                        'Mode de Paiement: ' + props.payment + '\n' +
                        'Total: ' + props.total + ' TND'
                    );
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>