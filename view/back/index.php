<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event</title>
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
            margin-bottom: 20px;
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
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #34495e;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .nav-links a:hover {
            color: #381d51;
            background-color: #f0f7ff;
        }

        .nav-links img {
            width: 24px;
            height: 24px;
            margin-right: 5px;
            border-radius: 50%;
        }

        /* Larger "Gestion" Title */
        .gestion-title {
            font-size: 28px; /* Increased font size for larger appearance */
            color: #301934; /* Changed to dark purple as requested */
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 8px;
        }

        /* Content Styles */
        .content {
            margin-left: 250px;
            padding: 40px;
            background-color: #602299;
            min-height: 100vh;
        }

        .module-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 40px;
        }

        .module-card {
            background-color: #301934;
            box-shadow: 30px 30px 30px rgba(0,0,0,0.2);
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgb(255, 255, 255);
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 8px 8px 12px rgba(0, 0, 0, 0.3);
        }

        /* Footer Styles */
        .site-footer {
            background-color: white;
            padding: 10px;
            margin-left: 250px;
            text-align: center;
            box-shadow: 0 -1px 3px rgba(0,0,0,0.1);
            color: #381d51;
            position: fixed;
            bottom: 0;
            width: calc(100% - 250px);
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .social-links a {
            color: #381d51;
            font-size: 20px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            color: #602299;
            transform: translateY(-3px);
        }

        .footer-text {
            color: #666;
            font-size: 14px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content, .site-footer, .content {
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
            .main-content, .site-footer, .content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </li>
            <li>
                <i class="fas fa-user"></i>
                Users
            </li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                Events
            </li>
            <li>
                <i class="fas fa-box"></i>
                Products
            </li>
            <li>
                <i class="fas fa-file-alt"></i>
                Publications
            </li>
            <li>
                <i class="fas fa-exclamation-circle"></i>
                Sponsoring
            </li>
            <li>
                <i class="fas fa-ticket-alt"></i>
                Reservations
            </li>
            <li>
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </li>
        </ul>
    </div>
    <div class="main-content">
        <div class="top-nav">
            <div class="search-container">
                <h2 class="gestion-title">Welcome to Aurora Event Dashboard</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher des événements...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><img src="logo.png" alt="Profile Picture"> Profil</a>
                <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
            </div>
        </div>
        <div class="content">
            <div class="module-wrapper">
                <a href="afficher.php" class="module-card">Gestion Événements</a>
                <a href="user_back.php" class="module-card">Gestion Utilisateurs</a>
                <a href="delivery.php" class="module-card">Gestion Produits</a>
                <a href="stock.php" class="module-card">Gestion Sponsoring</a>
                <a href="supplier.php" class="module-card">Gestion Publications</a>
            </div>
        </div>
    </div>
    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. Tous droits réservés.</p>
    </footer>
</body>
</html>