<?php
require_once '../../config.php';
require_once '../../controller/user_controller.php';

<<<<<<< HEAD
$db = getDB();
=======
$db = config::getConnexion();
>>>>>>> user
$userController = new UserController($db);
$userId = isset($_GET['id']) ? $_GET['id'] : null;

// Get user data if ID is provided
if ($userId) {
    $user = $userController->getUser($userId);
    if (!$user) {
        header('Location: user_back.php?message=User not found&type=error');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_user'];
    $result = $userController->updateUser($id, $_POST);
    if ($result['success']) {
<<<<<<< HEAD
        $_SESSION['success'] = "Utilisateur modifié avec succès";
        header('Location: user_back.php');
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour";
=======
        header('Location: user_back.php?message=User updated successfully');
        exit();
>>>>>>> user
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur - Aurora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<<<<<<< HEAD
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

        .sidebar-header img {
            height: 40px;
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
=======
    <link rel="stylesheet" href="../assets/css/style_back.css">
>>>>>>> user
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
<<<<<<< HEAD
                <a href="index.html" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-tachometer-alt"></i>
=======
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.html" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="active">
<<<<<<< HEAD
                <a href="user_back.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-users"></i>
=======
                <i class="fas fa-users"></i>
                <a href="user_back.php" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Users</span>
                </a>
            </li>
            <li>
<<<<<<< HEAD
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-calendar-alt"></i>
=======
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Events</span>
                </a>
            </li>
            <li>
<<<<<<< HEAD
                <a href="Products.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-box"></i>
=======
                <i class="fas fa-box"></i>
                <a href="Products.php" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Products</span>
                </a>
            </li>
            <li>
<<<<<<< HEAD
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-book"></i>
=======
                <i class="fas fa-book"></i>
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Publications</span>
                </a>
            </li>
            <li>
<<<<<<< HEAD
                <a href="Reclamations.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Sponsoring</span>
                </a>
            </li>
            <li>
                <a href="logout.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i>
=======
                <i class="fas fa-exclamation-circle"></i>
                <a href="Reclamations.php" style="color: inherit; text-decoration: none;">
                    <span>sponsoring</span>
                </a>
            </li>
            <li>
                <i class="fas fa-sign-out-alt"></i>
                <a href="logout.php" style="color: inherit; text-decoration: none;">
>>>>>>> user
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-nav">
            <div class="search-container">
<<<<<<< HEAD
                <h2 style="font-size: 18px; color: #381d51;">Modifier l'utilisateur #<?php echo htmlspecialchars($userId); ?></h2>
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
                Modifier l'Utilisateur
            </h3>
            <form method="POST" action="" id="edit-user-form" enctype="multipart/form-data">
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($userId); ?>">
                
                <div class="form-group">
                    <label for="cin">CIN</label>
                    <input type="text" id="cin" name="cin" value="<?php echo isset($user) ? htmlspecialchars($user->getCin()) : ''; ?>" required>
                    <div class="error-message" id="cin-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo isset($user) ? htmlspecialchars($user->getNom()) : ''; ?>" required>
                    <div class="error-message" id="nom-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo isset($user) ? htmlspecialchars($user->getPrenom()) : ''; ?>" required>
                    <div class="error-message" id="prenom-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" required>
=======
                <h2 style="font-size: 18px; color: #381d51;">Modifier un utilisateur</h2>
            </div>
        </div>

        <div class="content-section">
            <div id="flash-message-container"></div>
            
            <form method="POST" action="" id="edit-user-form" onsubmit="return validateForm(this)">
                <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($userId); ?>">
                
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" value="<?php echo isset($user) ? htmlspecialchars($user->getCin()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?php echo isset($user) ? htmlspecialchars($user->getNom()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?php echo isset($user) ? htmlspecialchars($user->getPrenom()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre">
>>>>>>> user
                        <option value="">Sélectionnez un genre</option>
                        <option value="homme" <?php echo (isset($user) && $user->getGenre() === 'homme') ? 'selected' : ''; ?>>Homme</option>
                        <option value="femme" <?php echo (isset($user) && $user->getGenre() === 'femme') ? 'selected' : ''; ?>>Femme</option>
                        <option value="autre" <?php echo (isset($user) && $user->getGenre() === 'autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
<<<<<<< HEAD
                    <div class="error-message" id="genre-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="date_naissance">Date de Naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?php echo isset($user) ? htmlspecialchars($user->getDateNaissance()) : ''; ?>" required>
                    <div class="error-message" id="date_naissance-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type" required>
=======
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Date de Naissance</label>
                    <input type="date" name="date_naissance" value="<?php echo isset($user) ? htmlspecialchars($user->getDateNaissance()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
>>>>>>> user
                        <option value="">Sélectionnez un type</option>
                        <option value="admin" <?php echo (isset($user) && $user->getType() === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="organisateur" <?php echo (isset($user) && $user->getType() === 'organisateur') ? 'selected' : ''; ?>>Organisateur</option>
                        <option value="participant" <?php echo (isset($user) && $user->getType() === 'participant') ? 'selected' : ''; ?>>Participant</option>
                    </select>
<<<<<<< HEAD
                    <div class="error-message" id="type-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" value="<?php echo isset($user) ? htmlspecialchars($user->getTelephone()) : ''; ?>" required>
                    <div class="error-message" id="telephone-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($user) ? htmlspecialchars($user->getEmail()) : ''; ?>" required>
                    <div class="error-message" id="email-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_pass">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                    <input type="password" id="mot_de_pass" name="mot_de_pass">
                    <div class="error-message" id="mot_de_pass-error"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="user_back.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir la liste
                    </a>
=======
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?php echo isset($user) ? htmlspecialchars($user->getTelephone()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" value="<?php echo isset($user) ? htmlspecialchars($user->getEmail()) : ''; ?>">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                    <input type="password" name="mot_de_pass">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="submit-btn">Enregistrer les modifications</button>
>>>>>>> user
                </div>
            </form>
        </div>
    </main>

<<<<<<< HEAD
    <script>
        function showError(inputElement, errorId, message) {
            const errorElement = document.getElementById(errorId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            inputElement.style.borderColor = '#dc3545';
        }

        function hideError(inputElement, errorId) {
            const errorElement = document.getElementById(errorId);
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            inputElement.style.borderColor = '#ddd';
        }

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('edit-user-form');
            const cinInput = document.getElementById('cin');
            const nomInput = document.getElementById('nom');
            const prenomInput = document.getElementById('prenom');
            const genreInput = document.getElementById('genre');
            const dateNaissanceInput = document.getElementById('date_naissance');
            const typeInput = document.getElementById('type');
            const telephoneInput = document.getElementById('telephone');
            const emailInput = document.getElementById('email');
            const motDePassInput = document.getElementById('mot_de_pass');

            cinInput.addEventListener('input', function() {
                if (this.value.length < 8 && this.value.length > 0) {
                    showError(this, 'cin-error', 'Le CIN doit contenir au moins 8 caractères');
                } else {
                    hideError(this, 'cin-error');
                }
            });

            nomInput.addEventListener('input', function() {
                if (this.value.length < 2 && this.value.length > 0) {
                    showError(this, 'nom-error', 'Le nom doit contenir au moins 2 caractères');
                } else {
                    hideError(this, 'nom-error');
                }
            });

            prenomInput.addEventListener('input', function() {
                if (this.value.length < 2 && this.value.length > 0) {
                    showError(this, 'prenom-error', 'Le prénom doit contenir au moins 2 caractères');
                } else {
                    hideError(this, 'prenom-error');
                }
            });

            genreInput.addEventListener('change', function() {
                if (!this.value) {
                    showError(this, 'genre-error', 'Veuillez sélectionner un genre');
                } else {
                    hideError(this, 'genre-error');
                }
            });

            dateNaissanceInput.addEventListener('change', function() {
                const today = new Date();
                const selectedDate = new Date(this.value);
                if (!this.value || selectedDate > today) {
                    showError(this, 'date_naissance-error', 'Veuillez sélectionner une date de naissance valide');
                } else {
                    hideError(this, 'date_naissance-error');
                }
            });

            typeInput.addEventListener('change', function() {
                if (!this.value) {
                    showError(this, 'type-error', 'Veuillez sélectionner un type');
                } else {
                    hideError(this, 'type-error');
                }
            });

            telephoneInput.addEventListener('input', function() {
                const phonePattern = /^[0-9]{8,}$/;
                if (!phonePattern.test(this.value) && this.value.length > 0) {
                    showError(this, 'telephone-error', 'Veuillez entrer un numéro de téléphone valide (au moins 8 chiffres)');
                } else {
                    hideError(this, 'telephone-error');
                }
            });

            emailInput.addEventListener('input', function() {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(this.value) && this.value.length > 0) {
                    showError(this, 'email-error', 'Veuillez entrer une adresse email valide');
                } else {
                    hideError(this, 'email-error');
                }
            });

            motDePassInput.addEventListener('input', function() {
                if (this.value.length < 6 && this.value.length > 0) {
                    showError(this, 'mot_de_pass-error', 'Le mot de passe doit contenir au moins 6 caractères');
                } else {
                    hideError(this, 'mot_de_pass-error');
                }
            });

            form.addEventListener('submit', function(event) {
                let isValid = true;

                if (cinInput.value.length < 8) {
                    showError(cinInput, 'cin-error', 'Le CIN doit contenir au moins 8 caractères');
                    isValid = false;
                }

                if (nomInput.value.length < 2) {
                    showError(nomInput, 'nom-error', 'Le nom doit contenir au moins 2 caractères');
                    isValid = false;
                }

                if (prenomInput.value.length < 2) {
                    showError(prenomInput, 'prenom-error', 'Le prénom doit contenir au moins 2 caractères');
                    isValid = false;
                }

                if (!genreInput.value) {
                    showError(genreInput, 'genre-error', 'Veuillez sélectionner un genre');
                    isValid = false;
                }

                const today = new Date();
                const selectedDate = new Date(dateNaissanceInput.value);
                if (!dateNaissanceInput.value || selectedDate > today) {
                    showError(dateNaissanceInput, 'date_naissance-error', 'Veuillez sélectionner une date de naissance valide');
                    isValid = false;
                }

                if (!typeInput.value) {
                    showError(typeInput, 'type-error', 'Veuillez sélectionner un type');
                    isValid = false;
                }

                const phonePattern = /^[0-9]{8,}$/;
                if (!phonePattern.test(telephoneInput.value)) {
                    showError(telephoneInput, 'telephone-error', 'Veuillez entrer un numéro de téléphone valide (au moins 8 chiffres)');
                    isValid = false;
                }

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) {
                    showError(emailInput, 'email-error', 'Veuillez entrer une adresse email valide');
                    isValid = false;
                }

                if (motDePassInput.value.length > 0 && motDePassInput.value.length < 6) {
                    showError(motDePassInput, 'mot_de_pass-error', 'Le mot de passe doit contenir au moins 6 caractères');
                    isValid = false;
                }

                if (!isValid) {
                    event.preventDefault();
                }
            });

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
=======
    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. All rights reserved.</p>
    </footer>

    <script src="../assets/js/script_back.js"></script>
>>>>>>> user
</body>
</html>