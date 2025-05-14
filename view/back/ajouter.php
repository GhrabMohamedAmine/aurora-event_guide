<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../model/Event.php';

session_start();

// Vérifier si l'utilisateur est connecté et est un organisateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organisateur') {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'organisateur pour ajouter un événement.";
    header("Location: events.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $artiste = $_POST['artiste'] ?? '';
    $date = $_POST['date'] ?? '';
    $heure = $_POST['heure'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $description = $_POST['description'] ?? '';
    $prix = $_POST['prix'] ?? '';

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__.'/../../Uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = 'Uploads/' . $filename;
        } else {
            $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
        }
    }

    // Validation côté serveur
    if (empty($titre) || empty($artiste) || empty($date) || empty($heure) || empty($lieu) || empty($prix)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!is_numeric($prix) || $prix < 0) {
        $_SESSION['error'] = "Le prix doit être un nombre positif.";
    } else {
        $event = new Event();
        $event->setTitre($titre)
              ->setArtiste($artiste)
              ->setDate($date)
              ->setHeure($heure)
              ->setLieu($lieu)
              ->setDescription($description)
              ->setImage($image)
              ->setPrix($prix)
              ->setIdUser($_SESSION['user_id']); // Ajouter id_user de l'organisateur connecté

        if ($event->create()) {
            $_SESSION['success'] = "Événement ajouté avec succès!";
            header("Location: ajouter.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'événement.";
        }
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

        .event-form {
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="file"] {
            padding: 6px;
            border: 1px dashed #ddd;
            background-color: #f9f9f9;
        }

        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
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

        .btn-add {
            background-color: #28a745;
            color: white;
        }

        .btn-add:hover {
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

        .error-field {
            border-color: #dc3545 !important;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

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
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.php" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li></li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li></li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <span>Events</span>
                </a>
            </li>
            <li></li>
            <li>
                <i class="fas fa-box"></i>
                <a href="Products.php" style="color: inherit; text-decoration: none;">
                    <span>Products</span>
                </a>
            </li>
            <li></li>
            <li>
                <i class="fas fa-book"></i>
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
                    <span>Publications</span>
                </a>
            </li>
            <li></li>
            <li>
                <i class="fas fa-exclamation-circle"></i>
                <a href="sponsoring.php" style="color: inherit; text-decoration: none;"> <!-- Correction de l'URL -->
                    <span>Sponsoring</span>
                </a>
            </li>
            <li></li>
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
                <h2 style="font-size: 18px; color: #381d51;">Ajouter un nouvel événement</h2>
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

        <div class="event-form">
            <h3 style="font-size: 16px; color: #381d51; margin-bottom: 20px;">
                <span id="form-title">Nouvel Événement</span>
            </h3>
            <form id="eventForm" method="post" action="ajouter.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titre">Titre*</label>
                    <input type="text" id="titre" name="titre" maxlength="255" required>
                    <div id="titre-error" class="error-message">Le titre doit contenir au moins 3 caractères</div>
                </div>
                
                <div class="form-group">
                    <label for="artiste">Artiste*</label>
                    <input type="text" id="artiste" name="artiste" maxlength="255" required>
                    <div id="artiste-error" class="error-message">L'artiste doit contenir au moins 3 caractères</div>
                </div>
                
                <div class="form-group">
                    <label for="date">Date*</label>
                    <input type="date" id="date" name="date" required>
                    <div id="date-error" class="error-message">La date ne peut pas être dans le passé</div>
                </div>
                
                <div class="form-group">
                    <label for="heure">Heure*</label>
                    <input type="time" id="heure" name="heure" required>
                    <div id="heure-error" class="error-message">Veuillez sélectionner une heure valide</div>
                </div>
                
                <div class="form-group">
                    <label for="lieu">Lieu*</label>
                    <input type="text" id="lieu" name="lieu" maxlength="255" required>
                    <div id="lieu-error" class="error-message">Le lieu doit contenir au moins 3 caractères</div>
                </div>
                
                <div class="form-group">
                    <label for="prix">Prix (TND)*</label>
                    <input type="number" id="prix" name="prix" step="0.01" min="0" required>
                    <div id="prix-error" class="error-message">Le prix doit être un nombre positif</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="image-error" class="error-message">Veuillez sélectionner une image valide (JPEG, PNG, GIF)</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-add">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <a href="afficher.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir la liste
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
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

            const form = document.getElementById('eventForm');
            const titreInput = document.getElementById('titre');
            const artisteInput = document.getElementById('artiste');
            const dateInput = document.getElementById('date');
            const heureInput = document.getElementById('heure');
            const lieuInput = document.getElementById('lieu');
            const prixInput = document.getElementById('prix');
            const imageInput = document.getElementById('image');

            function showError(input, errorId, message) {
                input.classList.add('error-field');
                const errorElement = document.getElementById(errorId);
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }

            function hideError(input, errorId) {
                input.classList.remove('error-field');
                document.getElementById(errorId).style.display = 'none';
            }

            titreInput.addEventListener('input', function() {
                if (this.value.length < 3 && this.value.length > 0) {
                    showError(this, 'titre-error', 'Le titre doit contenir au moins 3 caractères');
                } else {
                    hideError(this, 'titre-error');
                }
            });

            artisteInput.addEventListener('input', function() {
                if (this.value.length < 3 && this.value.length > 0) {
                    showError(this, 'artiste-error', 'L\'artiste doit contenir au moins 3 caractères');
                } else {
                    hideError(this, 'artiste-error');
                }
            });

            dateInput.addEventListener('change', function() {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(this.value);
                
                if (selectedDate < today) {
                    showError(this, 'date-error', 'La date ne peut pas être dans le passé');
                } else {
                    hideError(this, 'date-error');
                }
            });

            heureInput.addEventListener('change', function() {
                if (!this.value) {
                    showError(this, 'heure-error', 'Veuillez sélectionner une heure valide');
                } else {
                    hideError(this, 'heure-error');
                }
            });

            lieuInput.addEventListener('input', function() {
                if (this.value.length < 3 && this.value.length > 0) {
                    showError(this, 'lieu-error', 'Le lieu doit contenir au moins 3 caractères');
                } else {
                    hideError(this, 'lieu-error');
                }
            });

            prixInput.addEventListener('input', function() {
                if (this.value < 0 || !this.value) {
                    showError(this, 'prix-error', 'Le prix doit être un nombre positif');
                } else {
                    hideError(this, 'prix-error');
                }
            });

            imageInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        showError(this, 'image-error', 'Veuillez sélectionner une image valide (JPEG, PNG, GIF)');
                    } else {
                        hideError(this, 'image-error');
                    }
                }
            });

            form.addEventListener('submit', function(event) {
                let isValid = true;

                if (titreInput.value.length < 3) {
                    showError(titreInput, 'titre-error', 'Le titre doit contenir au moins 3 caractères');
                    isValid = false;
                }

                if (artisteInput.value.length < 3) {
                    showError(artisteInput, 'artiste-error', 'L\'artiste doit contenir au moins 3 caractères');
                    isValid = false;
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(dateInput.value);
                if (!dateInput.value || selectedDate < today) {
                    showError(dateInput, 'date-error', 'La date ne peut pas être dans le passé');
                    isValid = false;
                }

                if (!heureInput.value) {
                    showError(heureInput, 'heure-error', 'Veuillez sélectionner une heure valide');
                    isValid = false;
                }

                if (lieuInput.value.length < 3) {
                    showError(lieuInput, 'lieu-error', 'Le lieu doit contenir au moins 3 caractères');
                    isValid = false;
                }

                if (prixInput.value < 0 || !prixInput.value) {
                    showError(prixInput, 'prix-error', 'Le prix doit être un nombre positif');
                    isValid = false;
                }

                if (imageInput.files.length > 0) {
                    const file = imageInput.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        showError(imageInput, 'image-error', 'Veuillez sélectionner une image valide (JPEG, PNG, GIF)');
                        isValid = false;
                    }
                }

                if (!isValid) {
                    event.preventDefault();
                    const firstError = document.querySelector('.error-field');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        });
    </script>
</body>
</html>