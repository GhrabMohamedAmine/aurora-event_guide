<?php
// Include configuration file for database connection and other settings
require_once __DIR__ . '/../../config.php';
// Include the reservation model to interact with reservation data
require_once __DIR__ . '/../../model/reserve.php';
// Include the reservation controller for business logic
require_once __DIR__ . '/../../controller/reserveC.php';

// Start session to manage user authentication
session_start();

// Check if the user is logged in by verifying the existence of 'user_id' in session
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    // If not logged in, redirect to events.php and stop execution
    header('Location: events.php');
    exit();
}

// Handle reservation cancellation if the action is requested
if (isset($_GET['action']) && $_GET['action'] === 'cancel_reservation' && isset($_GET['id_reservation']) && $isLoggedIn) {
    // Instantiate the reservation controller
    $reservationController = new ReservationC();
    // Validate the reservation ID to ensure it's an integer
    $id_reservation = filter_var($_GET['id_reservation'], FILTER_VALIDATE_INT);
    if ($id_reservation === false) {
        // If the ID is invalid, set an error message and redirect
        $_SESSION['error_message'] = "ID de réservation invalide.";
        header('Location: my_reservations.php#reservations');
        exit();
    }
    // Retrieve the reservation by ID
    $reservation = $reservationController->getReservationById($id_reservation);
    // Check if the reservation exists and belongs to the logged-in user
    if ($reservation && $reservation[0]['id_user'] == $_SESSION['user_id']) {
        // Attempt to delete the reservation
        $result = $reservationController->supprimerReservation($id_reservation);
        if ($result) {
            // If successful, set a success message
            $_SESSION['success_message'] = "Réservation annulée avec succès.";
        } else {
            // If deletion fails, set an error message
            $_SESSION['error_message'] = "Erreur lors de l'annulation de la réservation.";
        }
    } else {
        // If the user is not authorized to cancel this reservation, set an error message
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à annuler cette réservation.";
    }
    // Redirect to the reservations section
    header('Location: my_reservations.php#reservations');
    exit();
}

// Retrieve all reservations for the logged-in user
$reservationController = new ReservationC();
$userReservations = $reservationController->getReservationsByUserId($_SESSION['user_id']);

// Prepare reservation data with additional calculations (e.g., days remaining until event)
$today = new DateTime();
$today->setTime(0, 0, 0); // Set time to midnight for consistent date comparisons

$reservationsData = [];
foreach ($userReservations as $reservation) {
    // Extract the event date (first 10 characters for YYYY-MM-DD format)
    $eventDateStr = isset($reservation['date']) ? substr($reservation['date'], 0, 10) : null;
    // Create a DateTime object for the event date, or null if not available
    $eventDate = $eventDateStr ? new DateTime($eventDateStr) : null;

    // Calculate the number of days remaining until the event
    $daysRemaining = $eventDate ? $today->diff($eventDate)->days : null;
    if ($eventDate && $today > $eventDate) {
        // If the event is in the past, make daysRemaining negative
        $daysRemaining = -$daysRemaining;
    }

    // Calculate the difference between reservation date and event date
    $reservationDateDifference = null;
    if (isset($reservation['date_reservation']) && $eventDate) {
        $reservationDateStr = substr($reservation['date_reservation'], 0, 10);
        $reservationDate = new DateTime($reservationDateStr);
        $interval = $reservationDate->diff($eventDate);
        $reservationDateDifference = $interval->days;
        if ($reservationDate > $eventDate) {
            // If reservation was made after the event, make it negative
            $reservationDateDifference = -$reservationDateDifference;
        }
    }

    // Store the processed reservation data in an array
    $reservationsData[] = [
        'reservation' => $reservation,
        'daysRemaining' => $daysRemaining,
        'reservationDateDifference' => $reservationDateDifference,
        'eventDate' => $eventDateStr
    ];
}

// Prepare events data for FullCalendar (JavaScript calendar library)
$events = [];
foreach ($reservationsData as $data) {
    $reservation = $data['reservation'];
    // Create an event object for FullCalendar
    $event = [
        'title' => htmlspecialchars($reservation['event_title']), // Event title, escaped for security
        'start' => $data['eventDate'] ?? date('Y-m-d'), // Event start date, fallback to today if null
        'extendedProps' => [ // Additional properties for the event
            'id_reservation' => $reservation['id_reservation'],
            'places' => $reservation['nombre_places'] ?? 'N/A',
            'category' => $reservation['categorie'] ?? 'N/A',
            'payment' => $reservation['mode_paiement'] ?? 'N/A',
            'total' => isset($reservation['total']) ? number_format($reservation['total'], 2) : 'N/A',
            'daysRemaining' => $data['daysRemaining'],
            'reservationDateDifference' => $data['reservationDateDifference']
        ]
    ];
    $events[] = $event;
}

// Collect upcoming events (events with daysRemaining >= 0) for display above the calendar
$upcomingEvents = [];
foreach ($reservationsData as $data) {
    if ($data['daysRemaining'] !== null && $data['daysRemaining'] >= 0) {
        $upcomingEvents[] = [
            'title' => htmlspecialchars($data['reservation']['event_title']),
            'daysRemaining' => $data['daysRemaining']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Define metadata for the page -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Mes Réservations et Calendrier">
    <meta name="author" content="">
    <title>Mes Réservations - Aurora Event</title>

    <!-- Load CSS stylesheets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="css/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap icons -->
    <link href="css/templatemo-festava-live.css" rel="stylesheet"> <!-- Custom template CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome icons -->

    <!-- Load FullCalendar CSS for the calendar -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <style>
        /* Custom styles for the page */
        body { background: #602299; color: #fff; } /* Set purple background and white text */
        .container-fluid { padding: 20px; min-height: calc(100vh - 200px); } /* Ensure content takes up most of the viewport */
        .dashboard { display: flex; gap: 30px; justify-content: space-between; margin-top: 20px; } /* Flexbox layout for reservations and calendar */
        .reservations-section, .calendar-section { flex: 1; background: #301934; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 48%; } /* Style for reservation and calendar sections */
        .section-title { text-align: center; margin-bottom: 20px; } /* Center section titles */
        .section-title h2 { font-size: 2rem; color: #fff; font-weight: 700; position: relative; display: inline-block; } /* Style for section title */
        .section-title h2::after { content: ''; position: absolute; width: 50px; height: 3px; background: #fff; bottom: -8px; left: 50%; transform: translateX(-50%); } /* Underline effect for titles */
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; font-weight: 500; margin-bottom: 15px; transition: color 0.3s; } /* Style for back link */
        .back-link:hover { color: #e0e0e0; } /* Hover effect for back link */
        .reservation-card { background: #fff; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); } /* Style for reservation cards */
        .reservation-card h4 { font-size: 1.5rem; color: #602299; margin-bottom: 10px; } /* Style for card titles */
        .reservation-card p { font-size: 1rem; color: #666; margin-bottom: 5px; } /* Style for card text */
        .reservation-detail { display: flex; align-items: center; gap: 8px; color: #602299; font-weight: 600; } /* Style for reservation details with icons */
        .reservation-detail i { font-size: 1.2rem; } /* Icon size for details */
        .btn { border: none; border-radius: 50px; padding: 10px 20px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; transition: all 0.3s; } /* General button style */
        .btn-modify, .btn-cancel { background: #602299; color: #fff; } /* Style for modify and cancel buttons */
        .btn-modify:hover, .btn-cancel:hover { background: #4a1a7a; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(96, 34, 153, 0.4); } /* Hover effects for buttons */
        .action-buttons { display: flex; gap: 10px; margin-top: 20px; } /* Layout for action buttons */
        .no-reservations { color: #ccc; text-align: center; font-size: 1.1rem; margin-top: 20px; font-style: italic; } /* Style for no-reservations message */
        #calendar { width: 100%; background: #fff; border-radius: 10px; } /* Style for calendar container */
        .fc-event { background: #602299 !important; border: none !important; color: #fff !important; border-radius: 5px !important; padding: 4px !important; font-size: 0.9rem !important; } /* Style for calendar events */
        .fc-daygrid-event:hover { background: #fff !important; color: #602299 !important; } /* Hover effect for events */
        .fc-button { background: #602299 !important; border: none !important; color: #fff !important; border-radius: 20px !important; padding: 6px 12px !important; } /* Style for calendar buttons */
        .fc-button:hover { background: #fff !important; color: #602299 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.2); } /* Hover effect for calendar buttons */
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; } /* Style for notification messages */
        .success { background-color: #d4edda; color: #155724; } /* Style for success messages */
        .error { background-color: #f8d7da; color: #721c24; } /* Style for error messages */
        .event-reminder { display: flex; align-items: center; gap: 8px; color: #fff; font-weight: 500; margin-bottom: 10px; } /* Style for event reminders */
        .event-reminder i { font-size: 1.2rem; } /* Icon size for reminders */
        @media (max-width: 768px) { 
            .dashboard { flex-direction: column; gap: 20px; } 
            .reservations-section, .calendar-section { max-width: 100%; } 
            .section-title h2 { font-size: 1.6rem; } 
        } /* Responsive design for smaller screens */
    </style>
</head>

<body>
    <main>
        <!-- Header section displaying a welcome message with the user's name -->
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

        <!-- Navigation bar with links to different sections and a user account dropdown -->
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
                        <li class="nav-item"><a class="nav-link" href="afficher.php#section_1">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="afficher.php#section_2">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                        <li class="nav-item"><a class="nav-link" href="afficher.php#section_4">Reviews</a></li>
                        <?php if ($_SESSION['user_type'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="../back/user_back.php?user_id=<?php echo $_SESSION['user_id']; ?>">Admin Dashboard</a></li>
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

        <!-- Display success or error messages from session -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Main content section with reservations and calendar -->
        <section class="section-padding">
            <div class="container-fluid">
                <div class="dashboard">
                    <!-- Reservations Section -->
                    <div class="reservations-section" id="reservations">
                        <div class="section-title"><h2>Mes Réservations</h2></div>
                        <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux Événements</a>
                        <?php if (empty($reservationsData)): ?>
                            <!-- Display message if no reservations exist -->
                            <p class="no-reservations">Aucune réservation à afficher.</p>
                        <?php else: ?>
                            <!-- Loop through reservations and display each in a card -->
                            <?php foreach ($reservationsData as $data): ?>
                                <?php $reservation = $data['reservation']; ?>
                                <div class="reservation-card">
                                    <h4><?= htmlspecialchars($reservation['event_title']) ?></h4>
                                    <p><strong>Artist:</strong> <?= htmlspecialchars($reservation['artiste'] ?? 'N/A') ?></p>
                                    <div class="reservation-detail"><i class="far fa-clock"></i> <span>Time not specified</span></div>
                                    <p><strong>Date:</strong> <?= htmlspecialchars($data['eventDate'] ?? 'N/A') ?></p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($reservation['lieu'] ?? 'N/A') ?></p>
                                    <div class="reservation-detail"><i class="fas fa-money-bill-wave"></i> <span><?= htmlspecialchars(number_format($reservation['total'], 2)) ?> TND</span></div>
                                    <p><?= htmlspecialchars($reservation['description'] ?? 'Description non disponible.') ?></p>
                                    <div class="action-buttons">
                                        <a href="reserve.php?id_reservation=<?= $reservation['id_reservation'] ?>&id_event=<?= $reservation['id_event'] ?>" class="btn btn-modify"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="my_reservations.php?action=cancel_reservation&id_reservation=<?= $reservation['id_reservation'] ?>" class="btn btn-cancel" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')"><i class="fas fa-trash"></i> Annuler</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Calendar Section -->
                    <div class="calendar-section">
                        <div class="section-title"><h2>Calendrier des Réservations</h2></div>
                        <!-- Display reminders for upcoming events -->
                        <?php if (!empty($upcomingEvents)): ?>
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="event-reminder">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>Il reste <?= $event['daysRemaining'] ?> jours pour l'événement "<?= $event['title'] ?>".</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="event-reminder">
                                <i class="far fa-calendar-alt"></i>
                                <span>Aucun événement à venir.</span>
                            </div>
                        <?php endif; ?>
                        <!-- Placeholder for FullCalendar -->
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer section with logo, social links, navigation, contact info, and map -->
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
                            <li class="social-icon-item"><a href="https://twitter.com/share?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer"><span class="bi-twitter"></span></a></li>
                            <li class="social-icon-item"><a href="https://www.facebook.com/sharer/sharer.php?u=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer"><span class="bi-facebook"></span></a></li>
                            <li class="social-icon-item"><a href="https://www.instagram.com" class="social-icon-link" target="_blank" rel="noopener noreferrer"><span class="bi-instagram"></span></a></li>
                            <li class="social-icon-item"><a href="https://www.youtube.com" class="social-icon-link" target="_blank" rel="noopener noreferrer"><span class="bi-youtube"></span></a></li>
                            <li class="social-icon-item"><a href="https://www.pinterest.com/pin/create/button/?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer"><span class="bi-pinterest"></span></a></li>
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
                        <li class="site-footer-link-item"><a href="afficher.php#section_1" class="site-footer-link">Home</a></li>
                        <li class="site-footer-link-item"><a href="afficher.php#section_2" class="site-footer-link">About</a></li>
                        <li class="site-footer-link-item"><a href="events.php" class="site-footer-link">Events</a></li>
                        <li class="site-footer-link-item"><a href="afficher.php#section_4" class="site-footer-link">Reviews</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Have a question?</h5>
                    <p class="text-white d-flex mb-1"><a href="tel: +216 94-166-711" class="site-footer-link">+216 94-166-711</a></p>
                    <p class="text-white d-flex"><a href="mailto:auroraevent@gmail.com" class="site-footer-link">auroraevent@gmail.com</a></p>
                </div>
                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Location</h5>
                    <p class="text-white d-flex mt-3 mb-2" style="font-size: 0.9rem; white-space: nowrap;">Av. Fethi Zouhir, Cebalat Ben Ammar 2083</p>
                    <a class="link-fx-1 color-contrast-higher mt-3" href="https://www.google.com/maps?q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" target="_blank"><span>Our Maps</span><svg class="icon" viewBox="0 0 32 32" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="16" cy="16" r="15.5"></circle><line x1="10" y1="18" x2="16" y2="12"></line><line x1="16" y1="12" x2="22" y2="18"></line></g></svg></a>
                    <iframe width="100%" height="300" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_API_KEY&q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        <div class="site-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mt-lg-5">
                        <ul class="site-footer-links">
                            <li class="site-footer-link-item"><a href="#" class="site-footer-link">Terms & Conditions</a></li>
                            <li class="site-footer-link-item"><a href="#" class="site-footer-link">Privacy Policy</a></li>
                            <li class="site-footer-link-item"><a href="#" class="site-footer-link">Your Feedback</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Load JavaScript libraries -->
    <script src="js/jquery.min.js"></script> <!-- jQuery -->
    <script src="js/bootstrap.min.js"></script> <!-- Bootstrap JS -->
    <script src="js/jquery.sticky.js"></script> <!-- Sticky navigation -->
    <script src="js/click-scroll.js"></script> <!-- Smooth scrolling -->
    <script src="js/custom.js"></script> <!-- Custom scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap bundle -->

    <!-- Load FullCalendar JavaScript -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

    <script>
        // Initialize FullCalendar when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Set default view to month grid
                events: <?php echo json_encode($events); ?>, // Load events from PHP
                eventClick: function(info) { // Handle click events on calendar events
                    var event = info.event;
                    var props = event.extendedProps;
                    // Determine the status message based on days remaining
                    var daysMessage = props.daysRemaining < 0 
                        ? 'Événement passé depuis ' + Math.abs(props.daysRemaining) + ' jours.' 
                        : (props.daysRemaining === 0 
                            ? 'L\'événement est aujourd\'hui !' 
                            : 'Il reste ' + props.daysRemaining + ' jours pour l\'événement.');
                    // Determine the reservation message
                    var reservationMessage = props.reservationDateDifference !== null 
                        ? 'Réservé il y a ' + Math.abs(props.reservationDateDifference) + ' jours avant l\'événement.' 
                        : 'Date de réservation non disponible.';
                    // Display event details in an alert
                    alert(
                        'Événement: ' + event.title + '\n' +
                        'Date: ' + event.start.toISOString().split('T')[0] + '\n' +
                        'ID Réservation: ' + props.id_reservation + '\n' +
                        'Places: ' + props.places + '\n' +
                        'Catégorie: ' + props.category + '\n' +
                        'Mode de Paiement: ' + props.payment + '\n' +
                        'Total: ' + props.total + ' TND\n' +
                        'Statut: ' + daysMessage + '\n' +
                        'Réservation: ' + reservationMessage
                    );
                },
                headerToolbar: { // Configure calendar navigation buttons
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                eventTimeFormat: { // Set time format for events
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                }
            });
            calendar.render(); // Render the calendar
        });
    </script>
</body>
</html>