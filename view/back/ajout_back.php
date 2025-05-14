<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/user_controller.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $userController = new UserController($db);
    
    $result = $userController->createUser($_POST);
    
    if ($result['success']) {
        header('Location: user_back.php?message=User added successfully&type=success');
        exit();
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Utilisateur - Aurora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_back.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
                body {
            display: flex;
            background-color: #602299;
            min-height: 100vh;
        }
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.html" style="color: inherit; text-decoration: none;">
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
                <a href="Reclamations.php" style="color: inherit; text-decoration: none;">
                    <span>sponsoring</span>
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
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Ajouter un nouvel utilisateur</h2>
            </div>
        </div>

        <div class="content-section">
            <div id="flash-message-container">
                <?php if (!empty($error_message)): ?>
                    <div class="flash-message error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="" id="add-user-form" onsubmit="return validateForm(this)">
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre">
                        <option value="">Sélectionnez un genre</option>
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                        <option value="autre">Autre</option>
                    </select>
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Date de Naissance</label>
                    <input type="date" name="date_naissance">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="">Sélectionnez un type</option>
                        <option value="admin">Admin</option>
                        <option value="organisateur">Organisateur</option>
                        <option value="participant">Participant</option>
                    </select>
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_pass">
                    <span class="error-message"></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="submit-btn">Ajouter l'utilisateur</button>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. All rights reserved.</p>
    </footer>

    <script src="../assets/js/script_back.js"></script>
</body>
</html>