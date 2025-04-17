<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../model/reserve.php';

// Start the session for flash messages
session_start();

// Check if the ID is present in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID invalide";
    header("Location: afficher.php");
    exit;
}

$id = (int)$_GET['id'];

// Retrieve the reservation to edit
$reservation = Reservation::getById($id);

if (!$reservation) {
    $_SESSION['error'] = "Réservation introuvable";
    header("Location: afficher.php");
    exit;
}

// Process the edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event = $_POST['id_event'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $nombre_places = $_POST['nombre_places'] ?? '';
    $categorie = $_POST['categorie'] ?? '';
    $mode_paiement = $_POST['mode_paiement'] ?? '';

    if ($id_event && $nom && $telephone && $nombre_places && $categorie && $mode_paiement) {
        $reservation->setIdEvent($id_event);
        $reservation->setNom($nom);
        $reservation->setTelephone($telephone);
        $reservation->setNombrePlaces($nombre_places);
        $reservation->setCategorie($categorie);
        $reservation->setModePaiement($mode_paiement);

        if ($reservation->update()) {
            $_SESSION['success'] = "Réservation modifiée avec succès";
            header("Location: afficher.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
    } else {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event Dashboard</title>
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

        /* Form Styles */
        .reservation-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
            font-size: 13px;
        }

        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus, 
        .form-group select:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            border: none;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-save {
            background-color: #28a745;
            color: white;
        }

        .btn-save:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
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
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
            .form-actions {
                flex-wrap: wrap;
            }
            .btn {
                flex: 1 0 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
            <h2>Dashboard</h2>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="index.html" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Réservations</span>
                </a>
            </li>
            <li>
                <a href="ajouter.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-plus-circle"></i>
                    <span>Ajouter Réservation</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Modifier la réservation #<?= htmlspecialchars($reservation->getIdReservation()) ?></h2>
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

        <!-- Edit Reservation Form -->
        <div class="reservation-form">
            <h3 style="font-size: 16px; color: #381d51; margin-bottom: 20px;">
                <span id="form-title">Modifier la Réservation</span>
            </h3>
            <form method="post" action="modifier.php?id=<?= $reservation->getIdReservation() ?>">
                <input type="hidden" name="id" value="<?= $reservation->getIdReservation() ?>">
                
                <div class="form-group">
                    <label for="id_event">ID Événement</label>
                    <input type="number" id="id_event" name="id_event" value="<?= htmlspecialchars($reservation->getIdEvent()) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($reservation->getNom()) ?>" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($reservation->getTelephone()) ?>" required maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="nombre_places">Nombre de places</label>
                    <input type="number" id="nombre_places" name="nombre_places" value="<?= htmlspecialchars($reservation->getNombrePlaces()) ?>" required min="1">
                </div>
                
                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie" required>
                        <option value="standard" <?= $reservation->getCategorie() === 'standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="vip" <?= $reservation->getCategorie() === 'vip' ? 'selected' : '' ?>>VIP</option>
                        <option value="premium" <?= $reservation->getCategorie() === 'premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mode_paiement">Mode de paiement</label>
                    <select id="mode_paiement" name="mode_paiement" required>
                        <option value="carte" <?= $reservation->getModePaiement() === 'carte' ? 'selected' : '' ?>>Carte bancaire</option>
                        <option value="espece" <?= $reservation->getModePaiement() === 'espece' ? 'selected' : '' ?>>Espèces</option>
                        <option value="virement" <?= $reservation->getModePaiement() === 'virement' ? 'selected' : '' ?>>Virement</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="afficher.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir la liste
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Hide success/error messages after 5 seconds
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                var successMessage = document.querySelector(".message.success");
                if (successMessage) {
                    successMessage.style.display = "none";
                }
                
                var errorMessage = document.querySelector(".message.error");
                if (errorMessage) {
                    errorMessage.style.display = "none";
                }
            }, 5000);
        });
    </script>
</body>
</html> change la modification par id de reservation 