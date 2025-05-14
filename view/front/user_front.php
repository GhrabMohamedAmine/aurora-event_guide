<?php
require_once '../../config.php';
require_once '../../controller/user_controller.php';

// Démarrage de la session
session_start();
<<<<<<< HEAD
$db = getDB();
=======
$db = config::getConnexion();
>>>>>>> user
$userController = new UserController($db);

// Get user_id from URL and verify it exists
if (!isset($_GET['user_id'])) {
    $_SESSION['error_message'] = "Aucun identifiant utilisateur fourni.";
    header('Location: ../accueille/accueilleinterface.php');
    exit();
}

$userId = $_GET['user_id'];
$user = $userController->getUser($userId);

if (!$user) {
    $_SESSION['error_message'] = "Utilisateur non trouvé.";
    header('Location: ../accueille/accueilleinterface.php');
    exit();
}

// Set up user data from the database
$userData = [
    'first_name' => $user->getPrenom(),
    'last_name' => $user->getNom(),
    'email' => $user->getEmail(),
    'phone' => $user->getTelephone(),
    'birthdate' => $user->getDateNaissance(),
<<<<<<< HEAD
=======

>>>>>>> user
];

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $updateData = [
        'prenom' => htmlspecialchars($_POST['first_name']),
        'nom' => htmlspecialchars($_POST['last_name']),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'telephone' => htmlspecialchars($_POST['phone']),
        'date_naissance' => htmlspecialchars($_POST['birthdate'])
    ];
    
    $result = $userController->updateUser($userId, $updateData);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "Vos informations ont été mises à jour avec succès.";
        // Refresh user data after update
        $user = $userController->getUser($userId);
        $userData = [
            'first_name' => $user->getPrenom(),
            'last_name' => $user->getNom(),
            'email' => $user->getEmail(),
            'phone' => $user->getTelephone(),
            'birthdate' => $user->getDateNaissance(),
        ];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Verify new password matches confirmation
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "Les nouveaux mots de passe ne correspondent pas.";
    } 
    else {
        $result = $userController->changePassword($userId, $newPassword);
        if ($result['success']) {
            $_SESSION['success_message'] = "Votre mot de passe a été modifié avec succès.";
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $password = $_POST['delete_password'];
    
    // Verify password first
    $stmt = $db->prepare("SELECT mot_de_pass FROM users WHERE id_user = :id");
    $stmt->execute([':id' => $userId]);
    $storedPassword = $stmt->fetchColumn();
    
    if ($storedPassword === $password) {
        $result = $userController->deleteUser($userId);
        if ($result['success']) {
            session_destroy();
            $_SESSION['success_message'] = "Votre compte a été supprimé avec succès.";
            header('Location: ../accueille/accueilleinterface.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression du compte : " . $result['message'];
        }
    } else {
        $_SESSION['error_message'] = "Mot de passe incorrect. Veuillez réessayer.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Aurora Event - Paramètres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<<<<<<< HEAD
    <style>
        /* General Body Styling */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #602299;
            color: #333;
        }

        /* Settings Header */
        .settings-header {
            background-color: #301934; /* Changed from assumed #602299 to #301934 */
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .settings-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .settings-header p.lead {
            font-size: 1.25rem;
            font-weight: 300;
        }

        /* Settings Card */
        .settings-card {
            background-color: white;
            border: 1px solid #301934; /* Changed from assumed #602299 to #301934 */
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .settings-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Navigation Tabs */
        .nav-pills .nav-link {
            color: #301934; /* Changed from assumed #602299 to #301934 */
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .nav-pills .nav-link.active {
            background-color: #301934; /* Changed from assumed #602299 to #301934 */
            color: white;
        }

        .nav-pills .nav-link:hover {
            background-color: #f1f1f1;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color:#602299 ; /* Changed from assumed #602299 to #301934 */
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #301934; /* Changed from assumed #602299 to #301934 */
            box-shadow: 0 0 5px rgba(48, 25, 52, 0.3);
        }

        /* Buttons */
        .btn-save, .btn-primary {
            background-color: #301934; /* Changed from assumed #381d51 to #301934 */
            border-color: #301934;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-save:hover, .btn-primary:hover {
            background-color: #3e2342; /* Slightly lighter shade for hover */
            border-color: #3e2342;
        }

        .btn-outline-primary {
            color: #301934; /* Changed from assumed #381d51 to #301934 */
            border-color: #301934;
        }

        .btn-outline-primary:hover {
            background-color: #301934;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        /* Switch Toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 20px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #301934; /* Changed from assumed #602299 to #301934 */
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        /* Security Alert */
        .security-alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffca2c;
            border-radius: 5px;
        }

        /* Danger Zone */
        .danger-zone {
            border: 1px solid #dc3545;
        }

        /* Theme Options */
        .theme-option {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .theme-option.active {
            border: 2px solid #301934; /* Changed from assumed #602299 to #301934 */
        }

        .theme-preview {
            width: 50px;
            height: 50px;
            margin: 0 auto 0.5rem;
            border-radius: 5px;
        }

        .light-theme {
            background-color: #ffffff;
            border: 1px solid #ced4da;
        }

        .dark-theme {
            background-color: #343a40;
            border: 1px solid #ced4da;
        }

        .aurora-theme {
            background: linear-gradient(135deg, #301934, #4a2c50); /* Changed from assumed #602299 gradient to #301934 */
            border: none;
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .settings-header h1 {
                font-size: 2rem;
            }

            .settings-header p.lead {
                font-size: 1rem;
            }

            .nav-pills .nav-link {
                padding: 0.5rem 1rem;
            }
        }
    </style>
=======
    <link rel="stylesheet" href="../assets/css/logout.css" />
>>>>>>> user
</head>
<body>
    <!-- Affichage des messages de succès/erreur -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="settings-header">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h1><i class="fas fa-cog"></i> Paramètres du Compte</h1>
                    <p class="lead">Personnalisez votre expérience Aurora Event</p>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-center">
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="settings-card">
                    <div class="nav flex-column nav-pills" id="settings-tab" role="tablist">
                        <button class="nav-link active" id="account-tab" data-bs-toggle="pill" data-bs-target="#account" type="button">
                            <i class="fas fa-user-cog me-2"></i> Compte
                        </button>
                        <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button">
                            <i class="fas fa-shield-alt me-2"></i> Sécurité
                        </button>
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </button>
                        <button class="nav-link" id="privacy-tab" data-bs-toggle="pill" data-bs-target="#privacy" type="button">
                            <i class="fas fa-lock me-2"></i> Confidentialité
                        </button>
                        <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button">
                            <i class="fas fa-credit-card me-2"></i> Paiements
                        </button>
                        <button class="nav-link" id="preferences-tab" data-bs-toggle="pill" data-bs-target="#preferences" type="button">
                            <i class="fas fa-sliders-h me-2"></i> Préférences
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content" id="settings-tabContent">
                    <!-- Onglet Compte -->
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-user-edit me-2"></i> Informations du compte</h3>

                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($userData['first_name']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($userData['last_name']) ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Adresse email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="birthdate" class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($userData['birthdate']) ?>">
                                </div>

                                <button type="submit" name="update_account" class="btn btn-save">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </form>
                        </div>

                        <div class="settings-card danger-zone">
                            <h3 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Zone de danger</h3>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-danger">Supprimer le compte</h5>
                                    <p class="text-muted">Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
                                </div>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash-alt"></i> Supprimer le compte
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Sécurité -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-shield-alt me-2"></i> Sécurité du compte</h3>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5>Mot de passe</h5>
                                    <small class="text-muted">Dernière modification: 15/03/2023</small>
                                </div>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fas fa-key"></i> Changer le mot de passe
                                </button>
                            </div>

                            <div class="mb-4">
                                <h5>Authentification à deux facteurs</h5>
                                <div class="d-flex align-items-center">
                                    <label class="switch me-3">
                                        <input type="checkbox" checked />
                                        <span class="slider"></span>
                                    </label>
                                    <span>Activée</span>
                                </div>
                                <small class="text-muted">Ajoutez une couche de sécurité supplémentaire à votre compte.</small>
                            </div>

                            <div class="security-alert p-3 mb-4">
                                <h5><i class="fas fa-exclamation-circle me-2"></i> Alertes de sécurité</h5>
                                <div class="d-flex align-items-center">
                                    <label class="switch me-3">
                                        <input type="checkbox" checked />
                                        <span class="slider"></span>
                                    </label>
                                    <span>Recevoir des alertes pour les connexions suspectes</span>
                                </div>
                            </div>

                            <h5 class="mt-4">Sessions actives</h5>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Paris, France</strong>
                                            <div class="text-muted">Chrome sur Windows - 15/06/2023 14:30</div>
                                        </div>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>

                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Lyon, France</strong>
                                            <div class="text-muted">Safari sur iPhone - 10/06/2023 09:15</div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger">Déconnecter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Notifications -->
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-bell me-2"></i> Préférences de notifications</h3>

                            <form method="POST" action="">
                                <h5 class="mt-4">Notifications par email</h5>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailEvents" name="email_events" checked>
                                    <label class="form-check-label" for="emailEvents">Nouveaux événements correspondant à mes intérêts</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="emailPromo" name="email_promo" checked>
                                    <label class="form-check-label" for="emailPromo">Offres promotionnelles</label>
                                </div>

                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="emailNews" name="email_news">
                                    <label class="form-check-label" for="emailNews">Newsletter Aurora Event</label>
                                </div>

                                <h5 class="mt-4">Notifications push</h5>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="pushReminders" name="push_reminders" checked>
                                    <label class="form-check-label" for="pushReminders">Rappels d'événements</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="pushMessages" name="push_messages">
                                    <label class="form-check-label" for="pushMessages">Messages de l'équipe</label>
                                </div>

                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="pushUpdates" name="push_updates" checked>
                                    <label class="form-check-label" for="pushUpdates">Mises à jour importantes</label>
                                </div>

                                <button type="submit" name="update_notifications" class="btn btn-save">
                                    <i class="fas fa-save"></i> Enregistrer les préférences
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Onglet Confidentialité -->
                    <div class="tab-pane fade" id="privacy" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-lock me-2"></i> Confidentialité et données</h3>

                            <form method="POST" action="">
                                <div class="mb-4">
                                    <h5>Visibilité du profil</h5>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="profile_visibility" id="visibilityPublic" value="public" checked>
                                        <label class="form-check-label" for="visibilityPublic">
                                            Profil public (visible par tous les utilisateurs)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="profile_visibility" id="visibilityPrivate" value="private">
                                        <label class="form-check-label" for="visibilityPrivate">
                                            Profil privé (visible par mes contacts seulement)
                                        </label>
                                    </div>
                                    <small class="text-muted">Contrôlez qui peut voir votre profil et vos activités</small>
                                </div>

                                <div class="mb-4">
                                    <h5>Partage de données</h5>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="dataAnalytics" name="data_analytics" checked>
                                        <label class="form-check-label" for="dataAnalytics">
                                            Partager des données anonymes pour améliorer le service
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="dataPartners" name="data_partners">
                                        <label class="form-check-label" for="dataPartners">
                                            Autoriser le partage de données avec nos partenaires
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h5>Historique de recherche</h5>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Effacer automatiquement l'historique après 30 jours</span>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="autoClearHistory" name="auto_clear_history" checked>
                                        </div>
                                    </div>
                                    <button type="submit" name="clear_history" class="btn btn-outline-secondary">
                                        <i class="fas fa-trash"></i> Effacer maintenant l'historique
                                    </button>
                                </div>

                                <div class="security-alert p-3">
                                    <h5><i class="fas fa-download me-2"></i> Export des données</h5>
                                    <p>Vous pouvez télécharger une copie de toutes vos données personnelles stockées sur Aurora Event.</p>
                                    <button type="submit" name="export_data" class="btn btn-outline-primary">
                                        <i class="fas fa-file-export"></i> Exporter mes données
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="settings-card">
                            <h3><i class="fas fa-user-friends me-2"></i> Gestion des contacts</h3>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <h5>Autorisations de contact</h5>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="allowFriendRequests" name="allow_friend_requests" checked>
                                        <label class="form-check-label" for="allowFriendRequests">
                                            Autoriser les demandes de contact
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="showMutualEvents" name="show_mutual_events">
                                        <label class="form-check-label" for="showMutualEvents">
                                            Afficher les événements en commun avec mes contacts
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h5>Liste de contacts bloqués</h5>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Rechercher un contact" name="search_blocked">
                                        <button class="btn btn-outline-secondary" type="submit" name="search_blocked_submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="list-group">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>Marie Durand</span>
                                            <button type="submit" name="unblock_user" value="1" class="btn btn-sm btn-outline-success">
                                                Débloquer
                                            </button>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>Paul Martin</span>
                                            <button type="submit" name="unblock_user" value="2" class="btn btn-sm btn-outline-success">
                                                Débloquer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Onglet Paiements -->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-credit-card me-2"></i> Méthodes de paiement</h3>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Cartes enregistrées</h5>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
                                        <i class="fas fa-plus"></i> Ajouter une carte
                                    </button>
                                </div>

                                <div class="list-group">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fab fa-cc-visa fa-2x me-3 text-primary"></i>
                                                <span>•••• •••• •••• 4242</span>
                                                <small class="text-muted ms-2">(Expire 04/25)</small>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-danger me-2">
                                                    Supprimer
                                                </button>
                                                <span class="badge bg-success">Par défaut</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fab fa-cc-mastercard fa-2x me-3 text-warning"></i>
                                                <span>•••• •••• •••• 5555</span>
                                                <small class="text-muted ms-2">(Expire 12/24)</small>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-danger me-2">
                                                    Supprimer
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary">
                                                    Définir par défaut
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5>Autres méthodes de paiement</h5>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="paypalOption" name="paypal_option">
                                    <label class="form-check-label" for="paypalOption">
                                        <i class="fab fa-paypal me-1 text-primary"></i> Activer PayPal
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="applePayOption" name="apple_pay_option">
                                    <label class="form-check-label" for="applePayOption">
                                        <i class="fab fa-apple-pay me-1 text-dark"></i> Activer Apple Pay
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="settings-card">
                            <h3><i class="fas fa-receipt me-2"></i> Historique des paiements</h3>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Événement</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>15/06/2023</td>
                                            <td>Festival Jazz</td>
                                            <td>45,00 €</td>
                                            <td><span class="badge bg-success">Payé</span></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">Facture</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>02/06/2023</td>
                                            <td>Exposition Art</td>
                                            <td>22,50 €</td>
                                            <td><span class="badge bg-success">Payé</span></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">Facture</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>20/05/2023</td>
                                            <td>Concert Classique</td>
                                            <td>60,00 €</td>
                                            <td><span class="badge bg-warning text-dark">Remboursé</span></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">Détails</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Précédent</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <!-- Onglet Préférences -->
                    <div class="tab-pane fade" id="preferences" role="tabpanel">
                        <div class="settings-card">
                            <h3><i class="fas fa-palette me-2"></i> Apparence</h3>

                            <form method="POST" action="">
                                <div class="mb-4">
                                    <h5>Thème</h5>
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="theme-option active" data-theme="light">
                                                <div class="theme-preview light-theme"></div>
                                                <span>Clair</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="theme-option" data-theme="dark">
                                                <div class="theme-preview dark-theme"></div>
                                                <span>Sombre</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="theme-option" data-theme="aurora">
                                                <div class="theme-preview aurora-theme"></div>
                                                <span>Aurora</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h5>Densité d'affichage</h5>
                                    <select class="form-select" id="densitySelect" name="density">
                                        <option value="comfortable">Confortable (par défaut)</option>
                                        <option value="compact">Compact</option>
                                        <option value="spacious">Spacieux</option>
                                    </select>
                                </div>

                                <button type="submit" name="update_appearance" class="btn btn-save mt-3">
                                    <i class="fas fa-save"></i> Enregistrer les préférences
                                </button>
                            </form>
                        </div>

                        <div class="settings-card">
                            <h3><i class="fas fa-language me-2"></i> Langue et région</h3>

                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="languageSelect" class="form-label">Langue</label>
                                        <select class="form-select" id="languageSelect" name="language">
                                            <option selected>Français</option>
                                            <option>English</option>
                                            <option>Español</option>
                                            <option>Deutsch</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="timezoneSelect" class="form-label">Fuseau horaire</label>
                                        <select class="form-select" id="timezoneSelect" name="timezone">
                                            <option selected>Europe/Paris (UTC+2)</option>
                                            <option>Europe/London (UTC+1)</option>
                                            <option>America/New_York (UTC-4)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="dateFormat" class="form-label">Format de date</label>
                                    <select class="form-select" id="dateFormat" name="date_format">
                                        <option selected>JJ/MM/AAAA (15/06/2023)</option>
                                        <option>MM/JJ/AAAA (06/15/2023)</option>
                                        <option>AAAA-MM-JJ (2023-06-15)</option>
                                    </select>
                                </div>

                                <button type="submit" name="update_language" class="btn btn-save">
                                    <i class="fas fa-save"></i> Enregistrer les préférences
                                </button>
                            </form>
                        </div>

                        <div class="settings-card">
                            <h3><i class="fas fa-sliders-h me-2"></i> Préférences avancées</h3>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <h5>Accessibilité</h5>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="highContrast" name="high_contrast">
                                        <label class="form-check-label" for="highContrast">
                                            Mode contraste élevé
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="largerText" name="larger_text">
                                        <label class="form-check-label" for="largerText">
                                            Texte plus grand
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="reduceAnimations" name="reduce_animations">
                                        <label class="form-check-label" for="reduceAnimations">
                                            Réduire les animations
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h5>Autres options</h5>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="saveHistory" name="save_history" checked>
                                        <label class="form-check-label" for="saveHistory">
                                            Enregistrer l'historique des recherches
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="showRecommendations" name="show_recommendations" checked>
                                        <label class="form-check-label" for="showRecommendations">
                                            Afficher les recommandations personnalisées
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="showTutorials" name="show_tutorials">
                                        <label class="form-check-label" for="showTutorials">
                                            Afficher les conseils et tutoriels
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" name="update_preferences" class="btn btn-save">
                                    <i class="fas fa-save"></i> Enregistrer les préférences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajout de carte -->
    <div class="modal fade" id="addCardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Ajouter une carte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Numéro de carte</label>
                            <input type="text" class="form-control" id="cardNumber" name="card_number" placeholder="1234 5678 9012 3456">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cardExpiry" class="form-label">Date d'expiration</label>
                                <input type="text" class="form-control" id="cardExpiry" name="card_expiry" placeholder="MM/AA">
                            </div>
                            <div class="col-md-6">
                                <label for="cardCvc" class="form-label">Code CVC</label>
                                <input type="text" class="form-control" id="cardCvc" name="card_cvc" placeholder="123">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cardName" class="form-label">Nom sur la carte</label>
                            <input type="text" class="form-control" id="cardName" name="card_name" placeholder="Jean Dupont">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="makeDefault" name="make_default" checked>
                            <label class="form-check-label" for="makeDefault">
                                Définir comme méthode de paiement par défaut
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="add_card" class="btn btn-primary">Enregistrer la carte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Changement de mot de passe -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Changer le mot de passe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="changePasswordForm" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password">
                            <div class="error-message"></div>
                        </div>

                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password">
                            <div class="form-text">Minimum 8 caractères avec chiffres, lettres et caractères spéciaux</div>
                            <div class="error-message"></div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                            <div class="error-message"></div>
                        </div>
                    </div>  
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="change_password" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Suppression de compte -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmation de suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.</p>
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Entrez votre mot de passe pour confirmer</label>
                            <input type="password" class="form-control" id="deletePassword" name="delete_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="delete_account" class="btn btn-danger">Supprimer définitivement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<<<<<<< HEAD
=======
    <script src="../assets/js/main.js"></script>
>>>>>>> user
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Account update form validation
        const updateForm = document.querySelector('form[name="update_account"]');
        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const birthdate = document.getElementById('birthdate');

        // Error message display function
        function showError(input, message) {
            // Remove any existing error message
            const existingError = input.parentElement.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }

            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = message;
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '0.875em';
            errorDiv.style.marginTop = '0.25rem';
            input.classList.add('is-invalid');
            input.parentElement.appendChild(errorDiv);
        }

        // Clear error message
        function clearError(input) {
            const errorDiv = input.parentElement.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }
            input.classList.remove('is-invalid');
        }

        // Validation functions
        function validateName(input) {
            const value = input.value.trim();
            if (value === '') {
                showError(input, 'Ce champ est requis');
                return false;
            }
            if (value.length < 2) {
                showError(input, 'Doit contenir au moins 2 caractères');
                return false;
            }
            if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(value)) {
                showError(input, 'Ne doit contenir que des lettres, espaces, tirets ou apostrophes');
                return false;
            }
            clearError(input);
            return true;
        }

        function validateEmail(input) {
            const value = input.value.trim();
            if (value === '') {
                showError(input, 'L\'email est requis');
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showError(input, 'Adresse email invalide');
                return false;
            }
            clearError(input);
            return true;
        }

        function validatePhone(input) {
            const value = input.value.trim();
            if (value === '') {
                showError(input, 'Le numéro de téléphone est requis');
                return false;
            }
            // Accept formats: +XX XX XXX XXX or 0X XX XX XX XX
            const phoneRegex = /^(?:\+\d{2}\s\d{2}\s\d{3}\s\d{3}|\d{2}\s\d{2}\s\d{2}\s\d{2}\s\d{2})$/;
            if (!phoneRegex.test(value)) {
                showError(input, 'Format invalide. Utilisez : +XX XX XXX XXX ou 0X XX XX XX XX');
                return false;
            }
            clearError(input);
            return true;
        }

        function validateBirthdate(input) {
            const value = input.value;
            if (value === '') {
                showError(input, 'La date de naissance est requise');
                return false;
            }
            const birthDate = new Date(value);
            const today = new Date();
            const minAge = 13; // Minimum age requirement
            const maxAge = 120; // Maximum reasonable age

            // Calculate age
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < minAge) {
                showError(input, 'Vous devez avoir au moins ' + minAge + ' ans');
                return false;
            }
            if (age > maxAge) {
                showError(input, 'Date de naissance invalide');
                return false;
            }
            clearError(input);
            return true;
        }

        // Real-time validation
        firstName.addEventListener('input', () => validateName(firstName));
        lastName.addEventListener('input', () => validateName(lastName));
        email.addEventListener('input', () => validateEmail(email));
        phone.addEventListener('input', () => validatePhone(phone));
        birthdate.addEventListener('input', () => validateBirthdate(birthdate));

        // Phone number formatting
        phone.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.startsWith('00')) {
                value = '+' + value.substring(2);
            }
            
            if (value.startsWith('+')) {
                // Format: +XX XX XXX XXX
                value = value.substring(0, 12); // Limit length
                let formatted = '';
                for (let i = 0; i < value.length; i++) {
                    if (i === 2 || i === 4 || i === 7) formatted += ' ';
                    formatted += value[i];
                }
                e.target.value = formatted;
            } else {
                // Format: 0X XX XX XX XX
                value = value.substring(0, 10); // Limit length
                let formatted = '';
                for (let i = 0; i < value.length; i++) {
                    if (i === 2 || i === 4 || i === 6 || i === 8) formatted += ' ';
                    formatted += value[i];
                }
                e.target.value = formatted;
            }
        });

        // Form submission
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Validate all fields
            if (!validateName(firstName)) isValid = false;
            if (!validateName(lastName)) isValid = false;
            if (!validateEmail(email)) isValid = false;
            if (!validatePhone(phone)) isValid = false;
            if (!validateBirthdate(birthdate)) isValid = false;

            // If form is valid, submit it
            if (isValid) {
                this.submit();
            } else {
                // Scroll to the first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Delete account form validation
        const deleteForm = document.querySelector('#deleteAccountModal form');
        const deletePassword = document.getElementById('deletePassword');

        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearError(deletePassword);

            if (deletePassword.value.trim() === '') {
                showError(deletePassword, 'Veuillez entrer votre mot de passe pour confirmer la suppression');
                return;
            }

            if (confirm('Êtes-vous vraiment sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
                this.submit();
            }
        });
    });
    </script>
</body>
</html>