<?php
require_once '../../config.php';
require_once '../../controller/user_controller.php';

// Initialize the database connection
<<<<<<< HEAD
$db = getDB();
$userController = new UserController($db);

// Get sorting parameters from GET
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id_user';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Fetch all users with sorting
$users = $userController->getAllUsers($sortBy, $order);
=======
$db = config::getConnexion();
$userController = new UserController($db);

// Fetch all users
$users = $userController->getAllUsers();
>>>>>>> user

// Calculate statistics
$totalUsers = count($users);
$participantUsers = 0;
$adminUsers = 0;
$organisateurUsers = 0;

// Calculate gender distribution
$maleUsers = 0;
$femaleUsers = 0;
$otherUsers = 0;

// Get user registration trends (last 6 months)
$currentMonth = date('n');
$currentYear = date('Y');
$monthlyRegistrations = array_fill(0, 6, 0);
$monthLabels = [];

// Generate month labels for the last 6 months
for ($i = 5; $i >= 0; $i--) {
    $month = ($currentMonth - $i) <= 0 ? ($currentMonth - $i + 12) : ($currentMonth - $i);
    $year = ($currentMonth - $i) <= 0 ? ($currentYear - 1) : $currentYear;
    $monthLabels[] = date('M', mktime(0, 0, 0, $month, 1, $year));
}

// Calculate active users per month (based on registration date)
foreach ($users as $user) {
    // Count user types
    switch($user->getType()) {
        case 'admin':
            $adminUsers++;
            break;
        case 'participant':
            $participantUsers++;
            break;
        case 'organisateur':
            $organisateurUsers++;
            break;
    }
    
    // Count gender distribution
    switch($user->getGenre()) {
        case 'homme':
            $maleUsers++;
            break;
        case 'femme':
            $femaleUsers++;
            break;
        default:
            $otherUsers++;
            break;
    }
    
    // Calculate monthly registration statistics
    $registrationDate = strtotime($user->getDateNaissance()); // Using date of birth as a proxy for registration date
    if ($registrationDate) {
        $regMonth = date('n', $registrationDate);
        $regYear = date('Y', $registrationDate);
        
        for ($i = 0; $i < 6; $i++) {
            $month = ($currentMonth - $i) <= 0 ? ($currentMonth - $i + 12) : ($currentMonth - $i);
            $year = ($currentMonth - $i) <= 0 ? ($currentYear - 1) : $currentYear;
            
            if ($regMonth == $month && $regYear == $year) {
                $monthlyRegistrations[5 - $i]++;
                break;
            }
        }
    }
}

// Calculate user type percentages for donut chart
$userTypeData = [
    'participant' => $participantUsers,
    'admin' => $adminUsers,
    'organisateur' => $organisateurUsers
];

// Calculate gender percentages for donut chart
$genderData = [
    'homme' => $maleUsers,
    'femme' => $femaleUsers,
    'autre' => $otherUsers
];

// JSON encode data for JavaScript
$userTypeJSON = json_encode($userTypeData);
$genderJSON = json_encode($genderData);
$monthlyRegistrationsJSON = json_encode($monthlyRegistrations);
$monthLabelsJSON = json_encode($monthLabels);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<<<<<<< HEAD
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
=======
    <link rel="stylesheet" href="../assets/css/style_back.css">
>>>>>>> user
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
<<<<<<< HEAD
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

        .btn-add, .btn-refresh {
            background-color: #28a745;
            padding: 10px 15px;
        }

        .btn-add:hover, .btn-refresh:hover {
            background-color: #218838;
        }

        .btn-export-pdf {
            background-color: #dc3545;
            padding: 10px 15px;
        }

        .btn-export-pdf:hover {
            background-color: #c82333;
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
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: #301934;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            color: white;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-card h4 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #e1bee7;
        }

        .stat-card p {
            font-size: 20px;
            font-weight: bold;
            color: white;
            margin: 0;
        }

        .chart-container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Charts */
=======
        /* Enhanced UI Styles */
        :root {
            --primary-color: #6A1B9A;
            --secondary-color: #9C27B0;
            --accent-color: #E1BEE7;
            --dark-bg: #1E1E2F;
            --card-bg: #27293D;
            --text-color: #FFFFFF;
            --card-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
        }
        
        body {
            background: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stats-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.4);
        }
        
        .stats-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
            color: var(--text-color);
        }
        
        .stats-card .label {
            color: var(--accent-color);
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stats-card img {
            display: none; /* Hide old images */
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        /* Charts section */
>>>>>>> user
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
<<<<<<< HEAD

        .chart-card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .chart-card h3 {
            margin-top: 0;
            color: #381d51;
=======
        
        .chart-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.4);
        }
        
        .chart-card h3 {
            margin-top: 0;
            color: var(--accent-color);
>>>>>>> user
            font-size: 1.2rem;
            margin-bottom: 20px;
            text-align: center;
        }
<<<<<<< HEAD

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
            .charts-container {
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
=======
        
        .chart-container {
            position: relative;
            height: 250px;
            margin: auto;
        }
        
        /* Table enhancements */
        .content-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 15px;
            overflow: hidden;
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }
        
        .content-table thead tr {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            text-align: left;
            font-weight: bold;
        }
        
        .content-table th,
        .content-table td {
            padding: 12px 15px;
        }
        
        .content-table tbody tr {
            border-bottom: 1px solid #27293D;
            transition: all 0.3s ease;
        }
        
        .content-table tbody tr:last-of-type {
            border-bottom: none;
        }
        
        .content-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.005);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .content-table tbody tr td:first-child {
            border-left: none;
        }
        
        .content-table tbody tr:hover td:first-child {
            border-left: none;
        }
        
        .content-table td {
            text-decoration: none;
            border-bottom: none;
        }
        
        .content-table tbody tr {
            background-color: #1a1b2e;
        }
        
        .content-table tbody tr td {
            color: #d8d8e8;
            font-weight: 500;
            text-decoration: none;
            border-top: none;
            border-bottom: none;
        }
        
        .content-table tbody tr:nth-child(even) {
            background-color: #1e1f35;
        }
        
        .content-table tbody tr:hover td {
            color: #ffffff;
            text-shadow: 0 0 8px rgba(225, 190, 231, 0.4);
            text-decoration: none;
        }
        
        /* Enhancement for the table header */
        .content-table th {
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            padding: 15px 15px;
            color: white;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
        }
        
        /* Better action buttons */
        .edit-btn, .delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .edit-btn {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }
        
        .delete-btn {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: none;
            cursor: pointer;
        }
        
        .edit-btn:hover {
            background: #3498db;
            color: white;
        }
        
        .delete-btn:hover {
            background: #e74c3c;
            color: white;
        }
        
        /* Top stats */
        .top-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            flex: 1;
            min-width: 200px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            padding: 15px;
            margin: 0 10px 10px 0;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .stat-info h4 {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .stat-info p {
            margin: 5px 0 0;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        /* Futuristic glow and animations */
        @keyframes glow {
            0% { box-shadow: 0 0 5px var(--accent-color); }
            50% { box-shadow: 0 0 20px var(--accent-color); }
            100% { box-shadow: 0 0 5px var(--accent-color); }
        }
        
        .charts-container .chart-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 2px;
            background: var(--accent-color);
            border-radius: 2px;
            animation: glow 3s infinite;
        }
        
        .add-btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50px;
            padding: 8px 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-left: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .add-btn i {
            margin-right: 5px;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }
        
        .export-pdf-btn {
            background: linear-gradient(to right, #f44336, #e91e63);
            color: white;
            border-radius: 50px;
            padding: 8px 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .export-pdf-btn i {
            margin-right: 5px;
        }
        
        .export-pdf-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
>>>>>>> user
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
<<<<<<< HEAD
            <img src="../assets/images/logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
=======
            <img src="../assets/images/logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
            </div>
>>>>>>> user
        </div>
        <ul class="sidebar-menu">
            <li>
                <i class="fas fa-tachometer-alt"></i>
<<<<<<< HEAD
                <a href="index.php" style="color: inherit; text-decoration: none;">
=======
                <a href="index.html" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="active">
                <i class="fas fa-users"></i>
                <a href="user_back.php" style="color: inherit; text-decoration: none;">
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
<<<<<<< HEAD
                <a href="sponsoring.php" style="color: inherit; text-decoration: none;">
                    <span>Sponsoring</span>
=======
                <a href="Reclamations.php" style="color: inherit; text-decoration: none;">
                    <span>sponsoring</span>
>>>>>>> user
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
<<<<<<< HEAD
                <h2 style="font-size: 18px; color: #381d51;">Gestion des Utilisateurs</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Rechercher des utilisateurs...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profil</a>
                <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="statistics-container">
            <h3 style="font-size: 16px; color: #381d51; margin-bottom: 20px;">Statistiques</h3>
            <div class="statistics-grid">
                <div class="stat-card">
                    <h4>Total Utilisateurs</h4>
                    <p><?= htmlspecialchars($totalUsers) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Participants</h4>
                    <p><?= htmlspecialchars($participantUsers) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Organisateurs</h4>
                    <p><?= htmlspecialchars($organisateurUsers) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Admins</h4>
                    <p><?= htmlspecialchars($adminUsers) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Hommes</h4>
                    <p><?= htmlspecialchars($maleUsers) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Femmes</h4>
                    <p><?= htmlspecialchars($femaleUsers) ?></p>
                </div>
            </div>
            <!-- Charts Container -->
            <div class="charts-container">
                <!-- User Type Distribution Chart -->
                <div class="chart-card">
                    <h3>User Type Distribution</h3>
                    <div class="chart-container">
                        <canvas id="userTypeChart"></canvas>
                    </div>
                </div>
                
                <!-- Gender Distribution Chart -->
                <div class="chart-card">
                    <h3>Gender Distribution</h3>
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
                
                <!-- User Growth Chart -->
                <div class="chart-card">
                    <h3>User Growth (6 Months)</h3>
                    <div class="chart-container">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
=======
                <h2 style="font-size: 18px; color: #e1bee7;">Welcome to Aurora User Management</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search users...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>

        <!-- Top Stats -->
        <div class="top-stats">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h4>Total Users</h4>
                    <p id="total-users-stat"><?php echo $totalUsers; ?></p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h4>Participants</h4>
                    <p id="participants-stat"><?php echo $participantUsers; ?></p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h4>Organisateurs</h4>
                    <p id="organisateurs-stat"><?php echo $organisateurUsers; ?></p>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h4>Admins</h4>
                    <p id="admins-stat"><?php echo $adminUsers; ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Container -->
        <div class="charts-container">
            <!-- User Type Distribution Chart -->
            <div class="chart-card">
                <h3>User Type Distribution</h3>
                <div class="chart-container">
                    <canvas id="userTypeChart"></canvas>
                </div>
            </div>
            
            <!-- Gender Distribution Chart -->
            <div class="chart-card">
                <h3>Gender Distribution</h3>
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
            
            <!-- User Growth Chart -->
            <div class="chart-card">
                <h3>User Growth (6 Months)</h3>
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
>>>>>>> user
                </div>
            </div>
        </div>

        <!-- Users Section -->
<<<<<<< HEAD
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 16px; color: #381d51;">Liste des Utilisateurs</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="sort-container">
                        <span class="sort-label">Trier par:</span>
                        <select id="sortUsers" class="sort-select">
                            <option value="default">Ordre par défaut</option>
                            <option value="id_asc">ID (croissant)</option>
                            <option value="id_desc">ID (décroissant)</option>
                            <option value="nom_asc">Nom (A-Z)</option>
                            <option value="nom_desc">Nom (Z-A)</option>
                        </select>
                    </div>
                    <a href="#" class="btn btn-export-pdf" id="export-pdf-btn">
                        <i class="fas fa-file-pdf"></i> Exporter PDF
                    </a>
                    <a href="#" class="btn btn-refresh" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Rafraîchir
                    </a>
                    <a href="ajout_back.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
            </div>

            <table class="table">
=======
        <div class="content-section">
            <div class="section-header">
                <h2>User Management</h2>
                <div>
                    <a href="#" class="export-pdf-btn" id="export-pdf-btn">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="#" class="refresh-btn" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                    <a href="ajout_back.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                </div>
            </div>
            
            <div id="flash-message-container"></div>
            
            <table class="content-table">
>>>>>>> user
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CIN</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Genre</th>
                        <th>Date Naissance</th>
                        <th>Type</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <?php foreach ($users as $user): ?>
                        <tr>
<<<<<<< HEAD
                            <td data-sort-id="<?= htmlspecialchars($user->getIdUser()) ?>"><?= htmlspecialchars($user->getIdUser()) ?></td>
                            <td><?= htmlspecialchars($user->getCin()) ?></td>
                            <td data-sort-nom="<?= htmlspecialchars($user->getNom()) ?>"><?= htmlspecialchars($user->getNom()) ?></td>
                            <td><?= htmlspecialchars($user->getPrenom()) ?></td>
                            <td><?= htmlspecialchars($user->getGenre()) ?></td>
                            <td><?= htmlspecialchars($user->getDateNaissance()) ?></td>
                            <td><?= htmlspecialchars($user->getType()) ?></td>
                            <td><?= htmlspecialchars($user->getTelephone()) ?></td>
                            <td><?= htmlspecialchars($user->getEmail()) ?></td>
                            <td class="action-buttons">
                                <a href="modifier_back.php?id=<?= $user->getIdUser() ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="delete_user.php?id=<?= $user->getIdUser() ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
=======
                            <td><?php echo $user->getIdUser(); ?></td>
                            <td><?php echo $user->getCin(); ?></td>
                            <td><?php echo $user->getNom(); ?></td>
                            <td><?php echo $user->getPrenom(); ?></td>
                            <td><?php echo $user->getGenre(); ?></td>
                            <td><?php echo $user->getDateNaissance(); ?></td>
                            <td><?php echo $user->getType(); ?></td>
                            <td><?php echo $user->getTelephone(); ?></td>
                            <td><?php echo $user->getEmail(); ?></td>
                            <td>
                                <a href="modifier_back.php?id=<?php echo $user->getIdUser(); ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="delete_user.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $user->getIdUser(); ?>">
                                    <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                                </form>
>>>>>>> user
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

<<<<<<< HEAD
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
=======
    <!-- Footer -->
    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. All rights reserved.</p>
    </footer>

    <script src="../assets/js/script_back.js"></script>
    <script>
        // Chart.js Configuration
>>>>>>> user
        document.addEventListener('DOMContentLoaded', function() {
            // PDF Export functionality
            document.getElementById('export-pdf-btn').addEventListener('click', function(e) {
                e.preventDefault();
                generatePDF();
            });
<<<<<<< HEAD

            function generatePDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');

                // Add header with title
                doc.setFontSize(22);
                doc.setTextColor(56, 29, 81);
                doc.text("Aurora Event Management", 14, 22);

                doc.setFontSize(16);
                doc.setTextColor(50, 50, 50);
                doc.text("User Management Report", 14, 32);

=======
            
            function generatePDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                
                // Add header with logo and title
                doc.setFontSize(22);
                doc.setTextColor(106, 27, 154); // Purple color
                doc.text("Aurora Event Management", 14, 22);
                
                doc.setFontSize(16);
                doc.setTextColor(50, 50, 50);
                doc.text("User Management Report", 14, 32);
                
>>>>>>> user
                // Add date
                const today = new Date();
                const dateStr = today.toLocaleDateString('fr-FR');
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Generated on: ${dateStr}`, 14, 38);
<<<<<<< HEAD

                // Add statistics summary
                doc.setFontSize(14);
                doc.setTextColor(56, 29, 81);
                doc.text("User Statistics", 14, 48);

                doc.setFontSize(10);
                doc.setTextColor(50, 50, 50);
                doc.text(`Total Users: ${<?php echo $totalUsers; ?>}`, 14, 54);
                doc.text(`Participants: ${<?php echo $participantUsers; ?>}`, 14, 59);
                doc.text(`Organisateurs: ${<?php echo $organisateurUsers; ?>}`, 64, 54);
                doc.text(`Admins: ${<?php echo $adminUsers; ?>}`, 64, 59);

                // Draw a separator line
                doc.setDrawColor(56, 29, 81);
                doc.setLineWidth(0.5);
                doc.line(14, 64, 280, 64);

                // Get data from table
                const tableHeaders = [];
                document.querySelectorAll('.table thead th').forEach(th => {
                    tableHeaders.push({ title: th.textContent, dataKey: th.textContent.toLowerCase() });
                });

                const tableData = [];
                document.querySelectorAll('.table tbody tr').forEach(tr => {
=======
                
                // Add statistics summary
                doc.setFontSize(14);
                doc.setTextColor(76, 27, 124);
                doc.text("User Statistics", 14, 48);
                
                doc.setFontSize(10);
                doc.setTextColor(50, 50, 50);
                doc.text(`Total Users: ${document.getElementById('total-users-stat').textContent}`, 14, 54);
                doc.text(`Participants: ${document.getElementById('participants-stat').textContent}`, 14, 59);
                doc.text(`Organisateurs: ${document.getElementById('organisateurs-stat').textContent}`, 64, 54);
                doc.text(`Admins: ${document.getElementById('admins-stat').textContent}`, 64, 59);
                
                // Draw a separator line
                doc.setDrawColor(156, 39, 176); // Light purple
                doc.setLineWidth(0.5);
                doc.line(14, 64, 280, 64);
                
                // Get data from table
                const tableHeaders = [];
                document.querySelectorAll('.content-table thead th').forEach(th => {
                    tableHeaders.push({ title: th.textContent, dataKey: th.textContent.toLowerCase() });
                });
                
                const tableData = [];
                document.querySelectorAll('.content-table tbody tr').forEach(tr => {
>>>>>>> user
                    const row = {};
                    tr.querySelectorAll('td').forEach((td, index) => {
                        if (index < tableHeaders.length - 1) { // Skip actions column
                            row[tableHeaders[index].dataKey] = td.textContent.trim();
                        }
                    });
                    tableData.push(row);
                });
<<<<<<< HEAD

                // Add table
                doc.autoTable({
                    startY: 70,
                    head: [tableHeaders.slice(0, -1).map(header => header.title)],
=======
                
                // Add table
                doc.autoTable({
                    startY: 70,
                    head: [tableHeaders.slice(0, -1).map(header => header.title)], // Remove Actions column
>>>>>>> user
                    body: tableData.map(row => {
                        return tableHeaders.slice(0, -1).map(header => row[header.dataKey]);
                    }),
                    headStyles: {
<<<<<<< HEAD
                        fillColor: [56, 29, 81],
                        textColor: [255, 255, 255],
                        lineWidth: 0,
                        lineColor: [56, 29, 81]
=======
                        fillColor: [106, 27, 154],
                        textColor: [255, 255, 255],
                        lineWidth: 0,
                        lineColor: [106, 27, 154]
>>>>>>> user
                    },
                    alternateRowStyles: {
                        fillColor: [245, 245, 255]
                    },
                    rowPageBreak: 'auto',
                    bodyStyles: {
                        lineWidth: 0.2,
                        lineColor: [220, 220, 250]
                    },
                    styles: {
                        font: 'helvetica',
                        fontSize: 8
                    },
                    margin: { top: 70 }
                });
<<<<<<< HEAD

=======
                
>>>>>>> user
                // Add footer
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
<<<<<<< HEAD

                    // Footer line
                    doc.setDrawColor(56, 29, 81);
                    doc.setLineWidth(0.5);
                    doc.line(14, doc.internal.pageSize.height - 20, 280, doc.internal.pageSize.height - 20);

=======
                    
                    // Footer line
                    doc.setDrawColor(156, 39, 176);
                    doc.setLineWidth(0.5);
                    doc.line(14, doc.internal.pageSize.height - 20, 280, doc.internal.pageSize.height - 20);
                    
>>>>>>> user
                    // Footer text
                    doc.setFontSize(8);
                    doc.setTextColor(100, 100, 100);
                    doc.text('Aurora Event Management System - Confidential', 14, doc.internal.pageSize.height - 15);
<<<<<<< HEAD

                    // Page number
                    doc.text(`Page ${i} of ${pageCount}`, 280, doc.internal.pageSize.height - 15, { align: 'right' });
                }

                // Save PDF
                doc.save(`aurora_users_report_${dateStr.replace(/\//g, '-')}.pdf`);
            }

            // Search functionality
            document.getElementById('search-input').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#users-table-body tr');

                tableRows.forEach(row => {
                    let found = false;
                    const cells = row.querySelectorAll('td');

                    cells.forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchValue)) {
                            found = true;
                        }
                    });

                    if (found) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Refresh button
            document.getElementById('refresh-btn').addEventListener('click', function(e) {
                e.preventDefault();
                location.reload();
            });

            // Sorting functionality
            const sortSelect = document.getElementById('sortUsers');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    const tbody = document.getElementById('users-table-body');
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    rows.sort((a, b) => {
                        if (sortValue === 'id_asc') {
                            const idA = parseInt(a.querySelector('td[data-sort-id]').getAttribute('data-sort-id'));
                            const idB = parseInt(b.querySelector('td[data-sort-id]').getAttribute('data-sort-id'));
                            return idA - idB;
                        } else if (sortValue === 'id_desc') {
                            const idA = parseInt(a.querySelector('td[data-sort-id]').getAttribute('data-sort-id'));
                            const idB = parseInt(b.querySelector('td[data-sort-id]').getAttribute('data-sort-id'));
                            return idB - idA;
                        } else if (sortValue === 'nom_asc') {
                            const nomA = a.querySelector('td[data-sort-nom]').getAttribute('data-sort-nom').toLowerCase();
                            const nomB = b.querySelector('td[data-sort-nom]').getAttribute('data-sort-nom').toLowerCase();
                            return nomA.localeCompare(nomB);
                        } else if (sortValue === 'nom_desc') {
                            const nomA = a.querySelector('td[data-sort-nom]').getAttribute('data-sort-nom').toLowerCase();
                            const nomB = b.querySelector('td[data-sort-nom]').getAttribute('data-sort-nom').toLowerCase();
                            return nomB.localeCompare(nomA);
                        } else {
                            // Default order by ID
                            const idA = parseInt(a.cells[0].textContent);
                            const idB = parseInt(b.cells[0].textContent);
                            return idA - idB;
                        }
                    });

                    // Reinsert sorted rows
                    tbody.innerHTML = '';
                    rows.forEach(row => tbody.appendChild(row));
                });
            }

            // Chart.js Configuration
=======
                    
                    // Page number
                    doc.text(`Page ${i} of ${pageCount}`, 280, doc.internal.pageSize.height - 15, { align: 'right' });
                }
                
                // Save PDF
                doc.save(`aurora_users_report_${dateStr.replace(/\//g, '-')}.pdf`);
            }
            
            // Existing Chart.js Configuration
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFFFFF',
                            font: {
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            };
            
            // Custom color schemes
            const userTypeColors = [
                'rgba(106, 27, 154, 0.8)', // Purple (Primary)
                'rgba(156, 39, 176, 0.8)', // Light Purple (Secondary)
                'rgba(225, 190, 231, 0.8)' // Very Light Purple (Accent)
            ];
            
            const genderColors = [
                'rgba(33, 150, 243, 0.8)', // Blue
                'rgba(233, 30, 99, 0.8)', // Pink
                'rgba(255, 193, 7, 0.8)' // Amber
            ];
            
            const growthColors = {
                line: 'rgba(156, 39, 176, 0.8)',
                point: 'rgba(225, 190, 231, 1)',
                background: 'rgba(106, 27, 154, 0.2)'
            };
            
            // User Type Distribution Chart
            const userTypeData = <?php echo $userTypeJSON; ?>;
>>>>>>> user
            const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
            new Chart(userTypeCtx, {
                type: 'doughnut',
                data: {
<<<<<<< HEAD
                    labels: Object.keys(<?php echo $userTypeJSON; ?>).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                    datasets: [{
                        data: Object.values(<?php echo $userTypeJSON; ?>),
                        backgroundColor: ['rgba(106, 27, 154, 0.8)', 'rgba(156, 39, 176, 0.8)', 'rgba(225, 190, 231, 0.8)'],
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#381d51',
                                font: { size: 12 },
                                padding: 20
                            }
                        }
                    }
                }
            });

=======
                    labels: Object.keys(userTypeData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                    datasets: [{
                        data: Object.values(userTypeData),
                        backgroundColor: userTypeColors,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 2,
                        hoverBackgroundColor: userTypeColors.map(color => color.replace('0.8', '1')),
                        hoverBorderColor: 'rgba(0, 0, 0, 0.2)',
                        hoverBorderWidth: 3
                    }]
                },
                options: chartOptions
            });
            
            // Gender Distribution Chart
            const genderData = <?php echo $genderJSON; ?>;
>>>>>>> user
            const genderCtx = document.getElementById('genderChart').getContext('2d');
            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
<<<<<<< HEAD
                    labels: Object.keys(<?php echo $genderJSON; ?>).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                    datasets: [{
                        data: Object.values(<?php echo $genderJSON; ?>),
                        backgroundColor: ['rgba(33, 150, 243, 0.8)', 'rgba(233, 30, 99, 0.8)', 'rgba(255, 193, 7, 0.8)'],
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#381d51',
                                font: { size: 12 },
                                padding: 20
                            }
                        }
                    }
                }
            });

            const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: <?php echo $monthLabelsJSON; ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo $monthlyRegistrationsJSON; ?>,
                        borderColor: 'rgba(106, 27, 154, 0.8)',
                        backgroundColor: 'rgba(106, 27, 154, 0.2)',
                        pointBackgroundColor: 'rgba(106, 27, 154, 1)',
                        pointBorderColor: '#fff',
                        pointRadius: 5,
=======
                    labels: Object.keys(genderData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
                    datasets: [{
                        data: Object.values(genderData),
                        backgroundColor: genderColors,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 2,
                        hoverBackgroundColor: genderColors.map(color => color.replace('0.8', '1')),
                        hoverBorderColor: 'rgba(0, 0, 0, 0.2)',
                        hoverBorderWidth: 3
                    }]
                },
                options: chartOptions
            });
            
            // User Growth Chart
            const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
            const growthData = <?php echo $monthlyRegistrationsJSON; ?>;
            const monthLabels = <?php echo $monthLabelsJSON; ?>;
            
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'New Users',
                        data: growthData,
                        borderColor: growthColors.line,
                        backgroundColor: growthColors.background,
                        pointBackgroundColor: growthColors.point,
                        pointBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 8,
>>>>>>> user
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
<<<<<<< HEAD
                            grid: { color: '#e0e0e0' },
                            ticks: { color: '#381d51' }
                        },
                        x: {
                            grid: { color: '#e0e0e0' },
                            ticks: { color: '#381d51' }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
=======
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#FFFFFF'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 12
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
            
            // Search functionality
            document.getElementById('search-input').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#users-table-body tr');
                
                tableRows.forEach(row => {
                    let found = false;
                    const cells = row.querySelectorAll('td');
                    
                    cells.forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchValue)) {
                            found = true;
                        }
                    });
                    
                    if (found) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Refresh button
            document.getElementById('refresh-btn').addEventListener('click', function(e) {
                e.preventDefault();
                location.reload();
            });
>>>>>>> user
        });
    </script>
</body>
</html>