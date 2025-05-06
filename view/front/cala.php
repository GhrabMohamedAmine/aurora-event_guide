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

// Get user reservations
$reservationController = new ReservationC();
$userReservations = $reservationController->getReservationsByUserId($_SESSION['user_id']);

// Prepare events data for FullCalendar
$events = [];
foreach ($userReservations as $reservation) {
    $event = [
        'title' => htmlspecialchars($reservation['event_title']),
        'start' => $reservation['event_date'], // Assuming 'event_date' is the column name for the event date
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
    <meta name="description" content="Aurora Event - Calendrier des Réservations">
    <meta name="author" content="">
    <title>Calendrier des Réservations - Aurora Event</title>

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
        .calendar-section {
            background-color: #602299;
            padding: 40px;
            border-radius: 15px;
            margin-top: 20px;
        }

        .section-title {
            position: relative;
            margin-bottom: 60px;
            text-align: center;
        }

        .section-title h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #fff;
            position: relative;
            display: inline-block;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #fff, #381d51);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        #calendar {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .fc-event {
            background-color: #381d51 !important;
            border-color: #381d51 !important;
            color: #fff !important;
            border-radius: 5px !important;
            padding: 5px !important;
            font-size: 14px !important;
        }

        .fc-daygrid-event:hover {
            background-color: #4a1a7a !important;
            cursor: pointer;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #e0e0e0;
        }

        .fc-button {
            background-color: #381d51 !important;
            border-color: #381d51 !important;
            color: #fff !important;
        }

        .fc-button:hover {
            background-color: #4a1a7a !important;
        }

        .fc-button.fc-button-active {
            background-color: #602299 !important;
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
                            <li><a class="dropdown-item" href="calendar.php">Calendrier</a></li>
                            <li><a class="dropdown-item" href="events.php?action=logout">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <section class="section-padding">
            <div class="container">
                <div class="calendar-section">
                    <div class="section-title">
                        <h2>Calendrier des Réservations</h2>
                    </div>
                    <a href="my_reservations.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à Mes Réservations</a>
                    <div id="calendar"></div>
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
        document.addEventListener('DOMContentLoaded', function() {
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