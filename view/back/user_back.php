<?php
require_once '../../config.php';
require_once '../../controller/user_controller.php';

// Initialize the database connection
$db = getDB(); // Use the getDB() function from config.php
$userController = new UserController($db);

// Fetch all users
$users = $userController->getAllUsers();

// Calculate statistics
$totalUsers = count($users);
$participantUsers = 0;
$adminUsers = 0;
$organisateurUsers = 0;

foreach ($users as $user) {
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
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora User Management</title>
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
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Welcome to Aurora User Management</h2>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search users...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <div class="stats-card">
                <img src="../assets/images/users-bg.jpg" alt="Users Background">
                <div class="number" id="total-users"><?php echo $totalUsers; ?></div>
                <div class="label">Total Users</div>
            </div>
            
            <div class="stats-card">
                <img src="../assets/images/active-users-bg.jpg" alt="Active Icon">
                <div class="number" id="active-users"><?php echo $participantUsers; ?></div>
                <div class="label">Active Users</div>
            </div>
            
            <div class="stats-card">
                <img src="../assets/images/banned-users-bg.jpg" alt="Banned Icon">
                <div class="number" id="banned-users"><?php echo $organisateurUsers; ?></div>
                <div class="label">Banned Users</div>
            </div>
            
            <div class="stats-card">
                <img src="../assets/images/admin-users-bg.jpg" alt="Admin Icon">
                <div class="number" id="admin-users"><?php echo $adminUsers; ?></div>
                <div class="label">Admin Users</div>
            </div>
        </div>

        <!-- Users Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>User Management</h2>
                <div>
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

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
</body>
</html>