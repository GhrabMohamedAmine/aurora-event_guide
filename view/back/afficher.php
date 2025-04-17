<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../model/Event.php';
require_once __DIR__.'/../../model/reserve.php';

// Démarrer la session pour les messages flash
session_start();

// Récupérer tous les événements et réservations
$events = Event::getAll();
$reservations = Reservation::getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event Dashboard</title>
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

        .sidebar-header h2 {
            font-size: 14px;
            color: #bdc3c7;
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

        .btn-add {
            background-color: #28a745;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .btn-add:hover {
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

        /* Styles pour les nouveaux éléments */
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
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            .sidebar-header h1, .sidebar-header h2, .sidebar-menu li span {
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
        <li class="active">
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.html" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                
            </li>
            <li>
    <i class="fas fa-user"></i>
    <a href="User.php" style="color: inherit; text-decoration: none;">
        <span>Users</span>
    </a>
      </li>
      <li>
                
            </li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <span>Events</span>
                </a>
            </li>
            <li>
                
            </li>
            <li>
                <i class="fas fa-box"></i>
                <a href="Products.php" style="color: inherit; text-decoration: none;">
                    <span>Products</span>
                </a>
            </li>
            <li>
                
            </li>
            <li>
                <i class="fas fa-book"></i>
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
                    <span>Publications</span>
                </a>
            </li>
            <li>
                
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

        <!-- Events Section -->
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 16px; color: #381d51;">Liste des Événements</h3>
                <a href="ajouter.php" class="btn btn-add">
                    <i class="fas fa-plus"></i> Ajouter un événement
                </a>
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
                            <th>Description</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event->getId()) ?></td>
                                <td><?= htmlspecialchars($event->getTitre()) ?></td>
                                <td><?= htmlspecialchars($event->getArtiste()) ?></td>
                                <td><?= htmlspecialchars($event->getDate()) ?></td>
                                <td><?= htmlspecialchars($event->getHeure()) ?></td>
                                <td><?= htmlspecialchars($event->getLieu()) ?></td>
                                <td class="description-cell" title="<?= htmlspecialchars($event->getDescription()) ?>">
                                    <?= htmlspecialchars($event->getDescription()) ?>
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
                                    <a href="modifier.php?id=<?= $event->getId() ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="supprimer.php?id=<?= $event->getId() ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement?')">
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
                <a href="reserve.php" class="btn btn-add">
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
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Places</th>
                            <th>Catégorie</th>
                            <th>Paiement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation->getIdReservation()) ?></td>
                                <td><?= htmlspecialchars($reservation->getIdEvent()) ?></td>
                                <td><?= htmlspecialchars($reservation->getNom()) ?></td>
                                <td><?= htmlspecialchars($reservation->getTelephone()) ?></td>
                                <td><?= htmlspecialchars($reservation->getNombrePlaces()) ?></td>
                                <td><?= htmlspecialchars($reservation->getCategorie()) ?></td>
                                <td><?= htmlspecialchars($reservation->getModePaiement()) ?></td>
                                <td class="action-buttons">
                                    <a href="modifiy.php?id=<?= $reservation->getIdReservation() ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="delete.php?id=<?= $reservation->getIdReservation() ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation?')">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour confirmer la suppression
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir supprimer cet élément?');
        }
        
        // Fonction pour filtrer les tableaux
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-bar input');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const tables = document.querySelectorAll('.table');
                
                tables.forEach(table => {
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        let rowText = '';
                        const cells = row.querySelectorAll('td');
                        cells.forEach((cell, index) => {
                            // Skip the last cell (actions column)
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
        });
    </script>
</body>
</html>