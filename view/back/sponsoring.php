<?php
// Fix: Use __DIR__ for reliable path resolution
require_once __DIR__ . '/../../controller/SponsorController.php';

// Basic error handling for file inclusion
if (!class_exists('SponsorController')) {
    die('Error: SponsorController class not found. Please check the file path and class definition.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new SponsorController();
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $controller->deleteSponsor($_POST['id']);
        header('Location: sponsoring.php');
        exit();
    }
}

// Get all sponsors for display
$controller = new SponsorController();
$sponsors = $controller->getSponsors();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event - Gestion des Sponsors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-title {
            font-size: 16px;
            color: #381d51;
            margin-bottom: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .data-table th {
            background-color: #381d51;
            color: white;
            font-weight: 500;
        }

        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .sponsor-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .no-photo {
            width: 60px;
            height: 60px;
            background-color: #f0f0f0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 20px;
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
            background-color: #ffc107; /* Yellow for Modifier */
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #dc3545; /* Red for Supprimer */
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
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
            <li class="active">
                <i class="fas fa-handshake"></i>
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Gestion des Sponsors</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher des sponsors...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profil</a>
                <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
            </div>
        </div>

        <!-- Sponsors Section -->
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="table-title">Liste des Sponsors</h3>
                <div style="display: flex; gap: 10px;">
                    <a href="offre.php" class="btn" style="background-color: #17a2b8;">
                        <i class="fas fa-handshake"></i> Demandes de Sponsoring
                    </a>
                    <a href="add_sponsor.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Nouveau Sponsor
                    </a>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>nom_sponsor</th>
                        <th>Entreprise</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sponsor): ?>
                    <tr>
                        <td><?= htmlspecialchars($sponsor['nom_sponsor']) ?></td>
                        <td><?= htmlspecialchars($sponsor['entreprise']) ?></td>
                        <td><?= htmlspecialchars($sponsor['mail']) ?></td>
                        <td><?= htmlspecialchars($sponsor['telephone']) ?></td>
                        <td>
                            <?php if (!empty($sponsor['photo'])): ?>
                                <img src="../../<?= htmlspecialchars($sponsor['photo']) ?>" alt="Photo" class="sponsor-photo">
                            <?php else: ?>
                                <div class="no-photo">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="modifier_sponsor.php?id=<?= $sponsor['id_sponsor'] ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <button onclick="confirmDelete(<?= $sponsor['id_sponsor'] ?>)" class="btn btn-delete">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce sponsor ?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-bar input');
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();
                
                tableRows.forEach(row => {
                    let rowVisible = false;
                    const cells = row.querySelectorAll('td');
                    
                    cells.forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchTerm)) {
                            rowVisible = true;
                        }
                    });
                    
                    if (rowVisible) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>