<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

// Démarrer la session pour les messages flash
session_start();

// Récupérer tous les événements
$events = Event::getAll();

// Utiliser ReservationC pour récupérer les réservations avec les détails de l'utilisateur
$reservationController = new ReservationC();
$reservations = $reservationController->afficherReservations();

// Calcul des statistiques
$totalEvents = count($events);
$totalReservations = count($reservations);

// Calculer le revenu total (somme des totaux de toutes les réservations)
$totalRevenue = 0;
foreach ($reservations as $reservation) {
    $totalRevenue += (float)($reservation['total'] ?? 0);
}

// Calculer le prix moyen des événements
$averageEventPrice = $totalEvents > 0 ? array_sum(array_map(function($event) {
    return (float)$event->getPrix();
}, $events)) / $totalEvents : 0;

// Trouver l'événement le plus réservé
$eventReservationCounts = [];
foreach ($reservations as $reservation) {
    $eventId = $reservation['id_event'];
    $eventReservationCounts[$eventId] = ($eventReservationCounts[$eventId] ?? 0) + 1;
}
// Trouver l'ID de l'événement avec le plus de réservations
$mostReservedEventId = !empty($eventReservationCounts) ? array_keys($eventReservationCounts, max($eventReservationCounts))[0] : null;
$mostReservedEvent = null;
$mostReservedCount = 0;
if ($mostReservedEventId) {
    $mostReservedCount = $eventReservationCounts[$mostReservedEventId];
    foreach ($events as $event) {
        if ($event->getIdEvent() == $mostReservedEventId) {
            $mostReservedEvent = $event;
            break;
        }
    }
}

// Préparer les données pour le graphique (réservations par événement)
$chartLabels = [];
$chartData = [];
foreach ($events as $event) {
    $eventId = $event->getIdEvent();
    $chartLabels[] = htmlspecialchars($event->getTitre());
    $chartData[] = $eventReservationCounts[$eventId] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclure Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #602299;
            min-height: 100vh;
        }

        /* Sidebar Styles */
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

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
        }

        .search-bar {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-bar input {
            padding: 8px 12px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 18px;
            font-size: 13px;
            width: 100%;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 14px;
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

        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        .table th {
            background-color: #381d51;
            color: white;
            font-size: 13px;
        }

        .table th, .table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #ffc107;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-add, .btn-calendar {
            background-color: #28a745;
            padding: 10px 15px;
        }

        .btn-add:hover, .btn-calendar:hover {
            background-color: #218838;
        }

        /* Message Styles */
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

        /* Event Image */
        .event-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Style pour la combobox de tri */
        .sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 10px;
        }

        .sort-label {
            font-size: 14px;
            color: #381d51;
        }

        .sort-select {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
            background-color: white;
        }

        /* Statistics Section */
        .statistics-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color:   #301934;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-card h4 {
            font-size: 14px;
            color:rgb(249, 249, 249);
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 20px;
            font-weight: bold;
            color:rgb(255, 255, 255);
            margin: 0;
        }

        .chart-container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
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
            .action-buttons {
                flex-direction: column;
            }
            .sort-container {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .statistics-grid {
                grid-template-columns: 1fr;
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
                <a href="index.php" style="color: inherit; text-decoration: none;">
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
                <i class="fas fa-ticket-alt"></i>
                <a href="#reservations" style="color: inherit; text-decoration: none;">
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Gestion des Événements et Réservations</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profil</a>
                <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
            </div>
        </div>

        <!-- Message Container -->
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

        <!-- Statistics Section -->
        <div class="statistics-container">
            <h3 style="font-size: 16px; color: #381d51; margin-bottom: 20px;">Statistiques</h3>
            <div class="statistics-grid">
                <div class="stat-card">
                    <h4>Total Événements</h4>
                    <p><?= htmlspecialchars($totalEvents) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Total Réservations</h4>
                    <p><?= htmlspecialchars($totalReservations) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Revenu Total</h4>
                    <p><?= htmlspecialchars(number_format($totalRevenue, 2)) ?> TND</p>
                </div>
                <div class="stat-card">
                    <h4>Prix Moyen Événement</h4>
                    <p><?= htmlspecialchars(number_format($averageEventPrice, 2)) ?> TND</p>
                </div>
                <div class="stat-card">
                    <h4>Événement le Plus Réservé</h4>
                    <p>
                        <?= $mostReservedEvent ? htmlspecialchars($mostReservedEvent->getTitre()) . " ($mostReservedCount rés.)" : 'Aucun' ?>
                    </p>
                </div>
            </div>
            <!-- Chart -->
            <div class="chart-container">
                <canvas id="reservationsChart"></canvas>
            </div>
        </div>

        <!-- Events Section -->
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 16px; color: #381d51;">Liste des Événements</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="sort-container">
                        <span class="sort-label">Trier par:</span>
                        <select id="sortEvents" class="sort-select">
                            <option value="default">Ordre par défaut</option>
                            <option value="date_asc">Date (croissant)</option>
                            <option value="date_desc">Date (décroissant)</option>
                        </select>
                    </div>
                    <a href="ajouter.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                    <a href="index.php" class="btn btn-calendar">
                        <i class="fas fa-calendar"></i> Calendrier
                    </a>
                </div>
            </div>

            <?php if (empty($events)): ?>
                <p>Aucun événement à afficher.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Artiste</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Lieu</th>
                            <th>Prix</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="eventsTableBody">
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event->getIdEvent()) ?></td>
                                <td><?= htmlspecialchars($event->getTitre()) ?></td>
                                <td><?= htmlspecialchars($event->getArtiste()) ?></td>
                                <td data-sort="<?= htmlspecialchars($event->getDate()) ?>"><?= htmlspecialchars($event->getDate()) ?></td>
                                <td><?= htmlspecialchars($event->getHeure() ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($event->getLieu()) ?></td>
                                <td><?= htmlspecialchars($event->getPrix() ?? 'N/A') ?> TND</td>
                                <td class="description-cell" title="<?= htmlspecialchars($event->getDescription() ?? 'N/A') ?>">
                                    <?= htmlspecialchars($event->getDescription() ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?php if ($event->getImage()): ?>
                                        <?php $imagePath = htmlspecialchars($event->getImage()); ?>
                                        <img src="../../<?= $imagePath ?>" alt="Image de l'événement" class="event-image">
                                    <?php else: ?>
                                        <span>Aucune image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="modifier.php?id_event=<?= $event->getIdEvent() ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="supprimer.php?id_event=<?= $event->getIdEvent() ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Reservations Section -->
        <div class="table-container" id="reservations">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 16px; color: #381d51;">Liste des Réservations</h3>
                <a href="../../view/front/reserve.php" class="btn btn-add">
                    <i class="fas fa-plus"></i> Ajouter une réservation
                </a>
            </div>

            <?php if (empty($reservations)): ?>
                <p>Aucune réservation à afficher.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Événement</th>
                            <th>ID Utilisateur</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Téléphone</th>
                            <th>Places</th>
                            <th>Catégorie</th>
                            <th>Paiement</th>
                            <th>Total (TND)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['id_reservation']) ?></td>
                                <td><?= htmlspecialchars($reservation['id_event']) ?></td>
                                <td><?= htmlspecialchars($reservation['id_user']) ?></td>
                                <td><?= htmlspecialchars($reservation['nom'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($reservation['prenom'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($reservation['telephone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($reservation['nombre_places'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($reservation['categorie'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($reservation['mode_paiement'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(isset($reservation['total']) ? number_format($reservation['total'], 2) : 'N/A') ?></td>
                                <td class="action-buttons">
                                    <a href="modifiy.php?id_reservation=<?= $reservation['id_reservation'] ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="delete.php?id_reservation=<?= $reservation['id_reservation'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction de confirmation de suppression
            function confirmDelete() {
                return confirm('Êtes-vous sûr de vouloir supprimer cet élément?');
            }

            // Fonction de recherche
            const searchInput = document.querySelector('.search-bar input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tables = document.querySelectorAll('.table');

                    tables.forEach(table => {
                        const rows = table.querySelectorAll('tbody tr');

                        rows.forEach(row => {
                            let rowText = '';
                            const cells = row.querySelectorAll('td');
                            cells.forEach((cell, index) => {
                                if (index < cells.length - 1) {
                                    rowText += cell.textContent.toLowerCase() + ' ';
                                }
                            });

                            if (rowText.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                });
            }

            // Fonction de tri des événements
            const sortSelect = document.getElementById('sortEvents');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    const tbody = document.getElementById('eventsTableBody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    rows.sort((a, b) => {
                        const dateA = new Date(a.querySelector('td[data-sort]').getAttribute('data-sort'));
                        const dateB = new Date(b.querySelector('td[data-sort]').getAttribute('data-sort'));

                        if (sortValue === 'date_asc') {
                            return dateA - dateB;
                        } else if (sortValue === 'date_desc') {
                            return dateB - dateA;
                        } else {
                            // Ordre par défaut (par ID)
                            const idA = parseInt(a.cells[0].textContent);
                            const idB = parseInt(b.cells[0].textContent);
                            return idA - idB;
                        }
                    });

                    // Réinsérer les lignes triées
                    tbody.innerHTML = '';
                    rows.forEach(row => tbody.appendChild(row));
                });
            }

            // Créer le graphique avec Chart.js
            const ctx = document.getElementById('reservationsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Nombre de Réservations par Événement',
                        data: <?= json_encode($chartData) ?>,
                        backgroundColor: 'rgba(56, 29, 81, 0.6)',
                        borderColor: 'rgba(56, 29, 81, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de Réservations'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Événements'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>