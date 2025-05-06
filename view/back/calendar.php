<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

session_start();

// Récupérer tous les événements
$events = Event::getAll();

// Récupérer toutes les réservations avec les détails de l'utilisateur et de l'événement
$reservationController = new ReservationC();
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT r.*, u.email, u.nom, e.titre, e.date
        FROM reservation r
        JOIN user u ON r.id_user = u.id_user
        JOIN evenement e ON r.id_event = e.id_event
    ");
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des réservations : " . $e->getMessage());
    $reservations = [];
}

// Grouper les réservations par ID d'événement
$eventReservations = [];
foreach ($reservations as $reservation) {
    $eventId = $reservation['id_event'];
    if (!isset($eventReservations[$eventId])) {
        $eventReservations[$eventId] = [];
    }
    $eventReservations[$eventId][] = [
        'email' => $reservation['email'],
        'nom' => $reservation['nom'] ?? $reservation['email'], // Utiliser le nom ou l'email
        'nombre_places' => $reservation['nombre_places'],
        'date' => $reservation['date'],
    ];
}

// Préparer les données pour le calendrier
$calendarEvents = [];
foreach ($events as $event) {
    $eventId = $event->getIdEvent();
    $date = DateTime::createFromFormat('d/m/Y', $event->getDate());
    $formattedDate = $date ? $date->format('Y-m-d') : $event->getDate();

    // Construire les détails des réservations pour cet événement
    $reservationDetails = '';
    if (isset($eventReservations[$eventId])) {
        $reservationDetails = "Réservations pour le {$event->getDate()}:\n";
        foreach ($eventReservations[$eventId] as $res) {
            $reservationDetails .= "- {$res['nom']} ({$res['email']}, {$res['nombre_places']} places)\n";
        }
    } else {
        $reservationDetails = "Aucune réservation pour le {$event->getDate()}.";
    }

    $calendarEvents[] = [
        'id' => $eventId,
        'title' => $event->getTitre() . ' - ' . $event->getArtiste(),
        'start' => $formattedDate,
        'description' => $event->getDescription() . "\n\n" . $reservationDetails,
        'location' => $event->getLieu(),
        'color' => '#602299',
        'textColor' => '#ffffff',
        'url' => 'modifier.php?id_event=' . $eventId
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Événements - Aurora Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #301934;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #4a2d6b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .sidebar-menu li:hover {
            background-color: #4a2d6b;
        }

        .sidebar-menu li.active {
            background-color: #602299;
        }

        .sidebar-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: #34495e;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: #381d51;
            background-color: #f0f7ff;
        }

        .calendar-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #calendar {
            margin-top: 20px;
        }

        .fc-event {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            .sidebar-header h1, .sidebar-menu li span {
                display: none;
            }
            .sidebar-menu li {
                text-align: center;
                padding: 12px 5px;
            }
            .sidebar-menu li i {
                margin-right: 0;
                font-size: 18px;
            }
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
        </div>
        <ul class="sidebar-menu">
            <li>
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.html" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li class="active">
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit coleta; text-decoration: none;">
                    <span>Events</span>
                </a>
            </li>
            <li>
                <i class="fas fa-box"></i>
                <a href="Products.php" style="color: inherit; text-decoration: none;">
                    <span>Products</span>
                </a>
            </li>
            <li>
                <i class="fas fa-book"></i>
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
                    <span>Publications</span>
                </a>
            </li>
            <li>
                <i class="fas fa-exclamation-circle"></i>
                <a href="sponsoring.php" style="color: inherit; text-decoration: none;">
                    <span>Sponsoring</span>
                </a>
            </li>
            <li>
                <i class="fas fa-ticket-alt"></i>
                <a href="afficher.php#reservations" style="color: inherit; text-decoration: none;">
                    <span>Reservations</span>
                </a>
            </li>
            <li>
                <i class="fas fa-sign-out-alt"></i>
                <a href="logout.php" style="color: inherit; text-decoration: none;">
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Calendrier des Événements</h2>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profil</a>
                <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
            </div>
        </div>

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listMonth'
                },
                defaultView: 'month',
                defaultDate: new Date(),
                locale: 'fr',
                navLinks: true,
                editable: false,
                eventLimit: true,
                events: <?php echo json_encode($calendarEvents); ?>,
                eventRender: function(event, element) {
                    element.find('.fc-title').append('<br/><small>' + event.location + '</small>');
                    var description = event.description.replace(/\n/g, '<br/>');
                    element.attr('title', description);
                    element.tooltip({
                        container: 'body',
                        placement: 'top',
                        trigger: 'hover',
                        html: true
                    });
                },
                eventClick: function(event) {
                    if (event.url) {
                        window.location.href = event.url;
                        return false;
                    }
                },
                views: {
                    listMonth: {
                        type: 'list',
                        duration: { months: 1 },
                        titleFormat: 'MMMM YYYY',
                        listDayFormat: 'dddd D',
                        noEventsMessage: 'Aucun événement ce mois-ci'
                    }
                }
            });
        });
    </script>
</body>
</html>