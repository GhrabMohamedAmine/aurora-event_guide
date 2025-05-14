<?php
// Inclusion des fichiers de configuration et du modèle Event pour accéder aux données
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

// Récupérer tous les événements depuis la base de données via la méthode statique getAll()
$events = Event::getAll();

// Initialisation d'un tableau pour stocker les données des événements au format compatible avec FullCalendar
$calendarEvents = [];
foreach ($events as $event) {
    // Conversion de la date de l'événement du format d/m/Y vers Y-m-d (requis par FullCalendar)
    $date = DateTime::createFromFormat('d/m/Y', $event->getDate());
    $formattedDate = $date ? $date->format('Y-m-d') : date('Y-m-d', strtotime($event->getDate()));
    
    // Ajout de chaque événement au tableau calendarEvents avec ses détails
    $calendarEvents[] = [
        'id' => $event->getIdEvent(), // ID unique de l'événement
        'title' => $event->getTitre() . ' - ' . $event->getArtiste(), // Titre combiné (ex. "Concert - DJ John")
        'start' => $formattedDate, // Date de début au format Y-m-d
        'description' => $event->getDescription(), // Description de l'événement
        'location' => $event->getLieu(), // Lieu de l'événement
        'color' => '#602299', // Couleur de fond de l'événement (violet)
        'textColor' => '#ffffff', // Couleur du texte (blanc)
        'url' => 'meteo.php?id_event=' . $event->getIdEvent(), // URL pour rediriger vers une page météo
        'type' => str_contains(strtolower($event->getArtiste()), 'dj') ? 'music' : 'generic' // Type d'événement (musique ou générique)
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Définition de l'encodage et de la responsivité -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre de la page -->
    <title>Aurora Event Dashboard</title>
    <!-- Inclusion des bibliothèques CSS externes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" /> <!-- Styles pour FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css"> <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" /> <!-- Animations -->
    <style>
        /* Réinitialisation des marges et paddings pour tous les éléments */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Style global du corps de la page */
        body {
            display: flex;
            flex-direction: column;
            background-color: #602299; /* Couleur de fond violet foncé */
            min-height: 100vh;
            color: white;
        }

        /* Styles de la barre latérale (sidebar) */
        .sidebar {
            width: 250px;
            background-color: #301934; /* Couleur de fond violet foncé */
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            padding: 20px 0;
        }

        /* En-tête de la sidebar avec le logo et le texte */
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #4a2d6b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Style de l'image du logo dans la sidebar */
        .sidebar-header img {
            height: 40px;
            width: auto;
        }

        /* Conteneur pour le texte de l'en-tête de la sidebar */
        .sidebar-header-text {
            display: flex;
            flex-direction: column;
        }

        /* Style du titre principal dans la sidebar */
        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #fff;
        }

        /* Style du sous-titre dans la sidebar */
        .sidebar-header h2 {
            font-size: 14px;
            color: #bdc3c7;
        }

        /* Style du menu de navigation dans la sidebar */
        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
        }

        /* Style des éléments du menu */
        .sidebar-menu li {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        /* Effet au survol des éléments du menu */
        .sidebar-menu li:hover {
            background-color: #4a2d6b;
        }

        /* Style pour l'élément actif du menu */
        .sidebar-menu li.active {
            background-color: #602299;
        }

        /* Style des icônes dans le menu */
        .sidebar-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Style du contenu principal */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #602299;
        }

        /* Grille pour les statistiques du tableau de bord */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* Style des cartes du tableau de bord */
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            color: #333;
        }

        /* Titre des cartes du tableau de bord */
        .dashboard-card h3 {
            margin-bottom: 15px;
            color: #381d51;
            font-size: 16px;
        }

        /* Style des cartes de statistiques */
        .stats-card {
            background-color: #301934;
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 200px;
            position: relative;
            overflow: hidden;
        }

        /* Style des nombres dans les cartes de stats */
        .stats-card .number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
            z-index: 2;
        }

        /* Style des étiquettes dans les cartes de stats */
        .stats-card .label {
            font-size: 14px;
            opacity: 0.8;
            z-index: 2;
        }

        /* Style des images de fond dans les cartes de stats */
        .stats-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0.3;
            transition: opacity 0.3s ease;
        }

        /* Effet au survol des images de fond */
        .stats-card:hover img {
            opacity: 0.5;
        }

        /* Style de la navigation supérieure */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            color: #381d51;
        }

        /* Conteneur pour la recherche et les liens de navigation */
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
        }

        /* Style de la barre de recherche */
        .search-bar {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        /* Style de l'input de recherche */
        .search-bar input {
            padding: 8px 12px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 18px;
            font-size: 13px;
            width: 100%;
            transition: all 0.3s;
        }

        /* Effet au focus de l'input de recherche */
        .search-bar input:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        /* Style de l'icône de recherche */
        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Style des liens de navigation */
        .nav-links {
            display: flex;
            gap: 15px;
        }

        /* Style des liens individuels */
        .nav-links a {
            text-decoration: none;
            color: #34495e;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Effet au survol des liens de navigation */
        .nav-links a:hover {
            color: #381d51;
            background-color: #f0f7ff;
        }

        /* Style de la navigation principale */
        .main-nav {
            display: flex;
            gap: 20px;
            margin-left: 20px;
        }

        /* Style des liens de la navigation principale */
        .main-nav a {
            text-decoration: none;
            color: #381d51;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        /* Effet au survol des liens de la navigation principale */
        .main-nav a:hover {
            background-color: #f0e6ff;
        }

        /* Style des sections de contenu */
        .content-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: #333;
        }

        /* Style de l'en-tête des sections */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Style du titre des sections */
        .section-header h2 {
            color: #381d51;
            font-size: 20px;
        }

        /* Style du bouton d'ajout d'événement */
        .add-btn {
            background-color: #602299;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        /* Effet au survol du bouton d'ajout */
        .add-btn:hover {
            background-color: #4a1a7a;
        }

        /* Style du conteneur du calendrier */
        .calendar-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 500px;
            overflow: auto;
        }

        /* Style du calendrier FullCalendar */
        #calendar {
            margin-top: 20px;
            height: 100%;
            width: 100%;
        }

        /* Style des événements dans le calendrier */
        .fc-event {
            cursor: pointer;
        }

        /* Style du pied de page */
        .site-footer {
            background-color: white;
            padding: 20px;
            margin-left: 250px;
            text-align: center;
            box-shadow: 0 -1px 3px rgba(0,0,0,0.1);
            color: #381d51;
        }

        /* Style des liens sociaux dans le pied de page */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        /* Style des icônes des liens sociaux */
        .social-links a {
            color: #381d51;
            font-size: 20px;
            transition: all 0.3s;
        }

        /* Effet au survol des icônes sociales */
        .social-links a:hover {
            color: #602299;
            transform: translateY(-3px);
        }

        /* Style du texte du pied de page */
        .footer-text {
            color: #666;
            font-size: 14px;
        }

        /* Styles personnalisés pour SweetAlert2 */
        .custom-swal-popup {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #301934, #602299);
            border-radius: 50%; /* Forme circulaire */
            width: 350px; /* Largeur fixe pour la forme circulaire */
            height: 350px; /* Hauteur fixe pour la forme circulaire */
            padding: 20px;
            position: relative;
            overflow: hidden;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Style du titre dans SweetAlert2 */
        .custom-swal-title {
            color: #ffffff !important;
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Style du contenu HTML dans SweetAlert2 */
        .custom-swal-html-container {
            color: #e0e0e0;
            font-size: 14px;
            text-align: center;
            line-height: 1.5;
            max-height: 150px;
            overflow-y: auto;
            padding: 0 10px;
        }

        /* Style du bouton de confirmation dans SweetAlert2 */
        .custom-swal-confirm {
            background-color: #00bcd4 !important;
            border: none !important;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            transition: transform 0.3s;
            margin: 5px;
        }

        /* Effet au survol du bouton de confirmation */
        .custom-swal-confirm:hover {
            transform: scale(1.1);
        }

        /* Style du bouton d'annulation dans SweetAlert2 */
        .custom-swal-cancel {
            background-color: #d33 !important;
            border: none !important;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            transition: transform 0.3s;
            margin: 5px;
        }

        /* Effet au survol du bouton d'annulation */
        .custom-swal-cancel:hover {
            transform: scale(1.1);
        }

        /* Style du bouton de rappel dans SweetAlert2 */
        .custom-swal-deny {
            background-color: #ff9800 !important;
            border: none !important;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            transition: transform 0.3s;
            margin: 5px;
        }

        /* Effet au survol du bouton de rappel */
        .custom-swal-deny:hover {
            transform: scale(1.1);
        }

        /* Style du compte à rebours dans les notifications */
        .countdown {
            color: #ff4444;
            font-weight: bold;
            margin-top: 5px;
            font-size: 12px;
        }

        /* Style du conteneur de la barre de progression */
        .progress-bar-container {
            margin-top: 5px;
            width: 80%;
            background-color: #444;
            border-radius: 5px;
            overflow: hidden;
        }

        /* Style de la barre de progression */
        .progress-bar {
            height: 8px;
            background-color: #00bcd4;
            transition: width 1s ease-in-out;
        }

        /* Style du conteneur des particules dans SweetAlert2 */
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            opacity: 0.5;
        }

        /* Contenu principal dans SweetAlert2 */
        .swal2-content {
            z-index: 1;
        }

        /* Style du bouton de fermeture dans SweetAlert2 */
        .swal2-close {
            background-color: #fff;
            border-radius: 50%;
            font-size: 18px;
            width: 24px;
            height: 24px;
            line-height: 24px;
            color: #333;
            top: 10px;
            right: 10px;
        }

        /* Ajustements responsifs pour écrans jusqu'à 768px */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content, .site-footer {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            .top-nav {
                flex-direction: column;
                gap: 15px;
            }
            .search-container {
                width: 100%;
            }
            .nav-links {
                width: 100%;
                justify-content: space-around;
            }
            .main-nav {
                margin-left: 0;
                justify-content: center;
                width: 100%;
                flex-wrap: wrap;
            }
            .calendar-container {
                height: 400px;
            }
        }

        /* Ajustements responsifs pour écrans jusqu'à 576px */
        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            .sidebar-header-text, .sidebar-menu li span {
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
            .main-content, .site-footer {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .calendar-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Barre latérale (sidebar) -->
    <aside class="sidebar">
        <!-- En-tête de la sidebar avec le logo -->
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
            </div>
        </div>
        <!-- Menu de navigation dans la sidebar -->
        <ul class="sidebar-menu">
            <li class="active">
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.php" style="color: inherit; text-decoration: none;">
                    <span>dashbord</span>
                </a>
            </li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User_back.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
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
                <i class="fas fa-sign-out-alt"></i>
                <a href="logout.php" style="color: inherit; text-decoration: none;">
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <!-- Navigation supérieure -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Welcome to Aurora Event Dashboard</h2>
                <div class="main-nav">
                    <a href="index.php"><i class="fas fa-home"></i> Home</a>
                    <a href="afficher.php"><i class="fas fa-calendar"></i> Events</a>
                    <a href="products.php"><i class="fas fa-box"></i> Products</a>
                    <a href="Publications.php"><i class="fas fa-book"></i> Publications</a>
                    <a href="sponsoring.php"><i class="fas fa-exclamation-circle"></i> Sponsoring</a>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>

        <!-- Section des statistiques -->
        <div class="dashboard-grid">
            <div class="stats-card">
                <img src="https://th.bing.com/th/id/OIF.78nnMmgdpe0cWMQwZuTQZg?rs=1&pid=ImgDetMain" alt="Events Background">
                <div class="number">24</div>
                <div class="label">Active Events</div>
            </div>
            <div class="stats-card">
                <img src="https://s3.amazonaws.com/eb-blog-wpmulti/wp-content/uploads/wpmulti/sites/3/2016/06/17101603/twenty20_e95b18fa-eec1-48f2-929e-957c1539f434-2.jpg" alt="Users Icon">
                <div class="number">1,245</div>
                <div class="label">Registered Users</div>
            </div>
            <div class="stats-card">
                <img src="https://th.bing.com/th/id/R.bc0a4d5e6cb1735cfc30f51437ab0395?rik=Bf1w2l5xiINafg&pid=ImgRaw&r=0" alt="Activities Icon">
                <div class="number">25</div>
                <div class="label">Reservation</div>
            </div>
        </div>

        <!-- Section du calendrier -->
        <div class="content-section">
            <div class="section-header">
                <h2>Event Calendar</h2>
                <a href="ajouter.php" class="add-btn">
                    <i class="fas fa-plus"></i> Add Event
                </a>
            </div>
            <div class="calendar-container">
                <?php if (empty($events)): ?>
                    <p>No events to display in the calendar.</p>
                <?php else: ?>
                    <div id="calendar"></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. All rights reserved.</p>
    </footer>

    <!-- Inclusion des scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> <!-- Moment.js pour la gestion des dates -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script> <!-- FullCalendar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script> <!-- Localisation française pour FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script> <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script> <!-- Particles.js pour les effets visuels -->
    <!-- Sons pour les notifications -->
    <audio id="musicNotificationSound" src="https://www.soundjay.com/buttons/beep-01a.mp3" preload="auto"></audio>
    <audio id="genericNotificationSound" src="https://www.soundjay.com/buttons/beep-02.mp3" preload="auto"></audio>
    <audio id="badgeSound" src="https://www.soundjay.com/buttons/tada.mp3" preload="auto"></audio>

    <script>
        // Exécution du code une fois que le DOM est chargé
        $(document).ready(function() {
            // Initialisation de FullCalendar pour afficher les événements
            $('#calendar').fullCalendar({
                header: { // Configuration de l'en-tête du calendrier
                    left: 'prev,next today', // Boutons de navigation (précédent, suivant, aujourd'hui)
                    center: 'title', // Titre du mois/année
                    right: 'month,agendaWeek,agendaDay,listMonth' // Vues disponibles
                },
                defaultView: 'month', // Vue par défaut : mensuelle
                defaultDate: new Date(), // Date par défaut : aujourd'hui
                locale: 'fr', // Localisation en français
                navLinks: true, // Permettre la navigation en cliquant sur les jours
                editable: false, // Désactiver l'édition des événements
                eventLimit: true, // Gérer les chevauchements d'événements
                height: 'auto', // Ajuster automatiquement la hauteur
                events: <?php echo json_encode($calendarEvents); ?>, // Charger les événements depuis PHP
                eventRender: function(event, element) { // Personnalisation du rendu des événements
                    element.find('.fc-title').append('<br/><small>' + event.location + '</small>'); // Ajouter le lieu sous le titre
                    element.attr('title', event.description); // Ajouter la description comme tooltip
                    element.tooltip({ // Activer le tooltip Bootstrap
                        container: 'body',
                        placement: 'top',
                        trigger: 'hover'
                    });
                },
                eventClick: function(event) { // Gestion du clic sur un événement
                    if (event.url) {
                        window.location.href = event.url; // Rediriger vers l'URL (meteo.php)
                        return false; // Empêcher le comportement par défaut
                    }
                },
                views: { // Configuration des vues personnalisées
                    listMonth: {
                        type: 'list',
                        duration: { months: 1 },
                        titleFormat: 'MMMM YYYY',
                        listDayFormat: 'dddd D',
                        noEventsMessage: 'Aucun événement ce mois-ci'
                    }
                }
            });

            // Gestion des badges pour les notifications
            let badgeCount = parseInt(localStorage.getItem('badgeCount') || '0'); // Compteur de badges
            function awardBadge() {
                badgeCount++; // Incrémenter le compteur
                localStorage.setItem('badgeCount', badgeCount); // Sauvegarder dans localStorage
                if (badgeCount === 3) { // Si 3 notifications ont été vues
                    const badgeAudio = document.getElementById('badgeSound');
                    badgeAudio.volume = 0.5;
                    badgeAudio.play(); // Jouer un son de célébration
                    Swal.fire({ // Afficher une alerte de badge
                        title: '🎉 Félicitations !',
                        text: 'Vous avez gagné le badge "Planificateur Pro" pour avoir consulté 3 notifications !',
                        icon: 'success',
                        confirmButtonText: 'Super !',
                        confirmButtonColor: '#602299',
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            confirmButton: 'custom-swal-confirm'
                        }
                    });
                }
            }

            // Fonction pour vérifier les événements à venir
            function checkUpcomingEvents() {
                const now = new Date(); // Date actuelle
                const events = <?php echo json_encode($calendarEvents); ?>; // Données des événements
                const notifiedEvents = JSON.parse(localStorage.getItem('notifiedEvents') || '[]'); // Événements déjà notifiés

                events.forEach(function(event) {
                    if (notifiedEvents.includes(event.id)) return; // Ignorer les événements déjà notifiés

                    const eventDate = new Date(event.start); // Date de l'événement
                    const timeDiff = eventDate - now; // Différence de temps en millisecondes
                    const hoursDiff = timeDiff / (1000 * 60 * 60); // Convertir en heures

                    // Si l'événement est dans les 72 heures
                    if (hoursDiff > 0 && hoursDiff <= 72) {
                        const countdown = moment(eventDate).fromNow(true); // Temps restant (ex. "dans 2 jours")
                        const totalHours = 72; // Fenêtre de notification
                        const remainingHours = Math.max(0, hoursDiff); // Heures restantes
                        const progress = (remainingHours / totalHours) * 100; // Pourcentage pour la barre de progression

                        // Jouer un son selon le type d'événement
                        const audio = event.type === 'music' ? 
                            document.getElementById('musicNotificationSound') : 
                            document.getElementById('genericNotificationSound');
                        audio.volume = 0.3;
                        audio.play();

                        // Icône selon le type d'événement
                        const eventIcon = event.type === 'music' ? '<i class="fas fa-music"></i>' : '<i class="fas fa-calendar-alt"></i>';

                        // Afficher une notification avec SweetAlert2
                        Swal.fire({
                            title: `${eventIcon} Événement à venir !`,
                            html: `
                                <div class="animate__animated animate__fadeIn">
                                    <strong>${event.title}</strong><br>
                                    <strong>Lieu :</strong> ${event.location} <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(event.location)}" target="_blank"><i class="fas fa-map-marker-alt"></i></a><br>
                                    <strong>Date :</strong> ${moment(event.start).format('DD/MM/YYYY')}<br>
                                    <strong>Description :</strong> ${event.description}<br>
                                    <div class="countdown">Temps restant : ${countdown}</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: ${progress}%"></div>
                                    </div>
                                </div>
                                <div id="particles-js"></div>
                            `,
                            icon: null,
                            showConfirmButton: true,
                            confirmButtonText: 'Voir détails',
                            confirmButtonColor: '#00bcd4',
                            showCancelButton: true,
                            cancelButtonText: 'Fermer',
                            cancelButtonColor: '#d33',
                            showDenyButton: true,
                            denyButtonText: 'Rappeler plus tard',
                            denyButtonColor: '#ff9800',
                            showCloseButton: true,
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                htmlContainer: 'custom-swal-html-container',
                                confirmButton: 'custom-swal-confirm',
                                cancelButton: 'custom-swal-cancel',
                                denyButton: 'custom-swal-deny'
                            },
                            didOpen: () => {
                                // Initialisation des particules pour les effets visuels
                                particlesJS('particles-js', {
                                    particles: {
                                        number: { value: 50, density: { enable: true, value_area: 800 } },
                                        color: { value: event.type === 'music' ? '#00bcd4' : '#ff9800' },
                                        shape: { type: 'circle' },
                                        opacity: { value: 0.5, random: true },
                                        size: { value: 3, random: true },
                                        line_linked: { enable: false },
                                        move: { enable: true, speed: 2, direction: 'none', random: true }
                                    },
                                    interactivity: {
                                        detect_on: 'canvas',
                                        events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' } },
                                        modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
                                    }
                                });

                                // Mise à jour dynamique du compte à rebours
                                const timer = setInterval(() => {
                                    const newCountdown = moment(eventDate).fromNow(true);
                                    const newHoursDiff = (eventDate - new Date()) / (1000 * 60 * 60);
                                    const newProgress = (Math.max(0, newHoursDiff) / totalHours) * 100;
                                    Swal.getHtmlContainer().querySelector('.countdown').textContent = `Temps restant : ${newCountdown}`;
                                    Swal.getHtmlContainer().querySelector('.progress-bar').style.width = `${newProgress}%`;
                                    if (moment().isAfter(eventDate)) clearInterval(timer);
                                }, 1000);
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Rediriger vers la page de détails (meteo.php)
                                window.location.href = event.url;
                            } else if (result.isDenied) {
                                setTimeout(checkUpcomingEvents, 3600000); // Rappel dans 1 heure
                            }
                            awardBadge(); // Attribuer un badge
                            notifiedEvents.push(event.id); // Ajouter l'événement à la liste des notifiés
                            localStorage.setItem('notifiedEvents', JSON.stringify(notifiedEvents)); // Sauvegarder
                        });
                    }
                });
            }

            // Vérifier les événements au chargement de la page
            checkUpcomingEvents();

            // Vérifier toutes les 10 minutes
            setInterval(checkUpcomingEvents, 10 * 60 * 1000);
        });
    </script>
</body>
</html>