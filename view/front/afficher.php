<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../controller/user_controller.php';

// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $db = getDB();
    $userController = new UserController($db);
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
        header('Location: afficher.php');
        exit();
    }
    
    $result = $userController->login($email, $password);
    
    if ($result === false) {
        $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
        header('Location: afficher.php?error=invalid');
        exit();
    }
    
    $stmt = $db->prepare("SELECT id_user, type, prenom, nom FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur non trouvé.";
        header('Location: afficher.php');
        exit();
    }
    
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['user_type'] = $user['type'];
    $_SESSION['user_name'] = (!empty($user['prenom']) && !empty($user['nom'])) 
        ? $user['prenom'] . ' ' . $user['nom'] 
        : $email;
    
    session_regenerate_id(true);
    
    switch ($user['type']) {
        case 'admin':
            header('Location: ../back/user_back.php?user_id=' . $user['id_user']);
            break;
        case 'organisateur':
            header('Location: ../front/events.php?user_id=' . $user['id_user'] . '&type=organisateur');
            break;
        case 'participant':
            header('Location: ../front/events.php?user_id=' . $user['id_user'] . '&type=participant');
            break;
        default:
            header('Location: ../front/user_front.php?user_id=' . $user['id_user']);
    }
    exit();
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $db = getDB();
    $userController = new UserController($db);

    $required_fields = ['cin', 'nom', 'prenom', 'genre', 'telephone', 'date_naissance', 'email', 'type', 'mot_de_pass'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires";
            header('Location: afficher.php');
            exit();
        }
    }

    $userData = [
        'cin' => htmlspecialchars(trim($_POST['cin'])),
        'nom' => htmlspecialchars(trim($_POST['nom'])),
        'prenom' => htmlspecialchars(trim($_POST['prenom'])),
        'genre' => htmlspecialchars(trim($_POST['genre'])),
        'telephone' => htmlspecialchars(trim($_POST['telephone'])),
        'date_naissance' => htmlspecialchars(trim($_POST['date_naissance'])),
        'email' => filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
        'type' => htmlspecialchars(trim($_POST['type'])),
        'mot_de_pass' => $_POST['mot_de_pass']
    ];

    $result = $userController->createUser($userData);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
        header('Location: afficher.php');
        exit();
    } else {
        $_SESSION['error_message'] = $result['message'];
        header('Location: afficher.php');
        exit();
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: afficher.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Plateforme de gestion d'événements">
    <meta name="author" content="Aurora Event Team">
    <title>Aurora Event - Accueil</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
        }
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #381d51;
            position: relative;
            display: inline-block;
        }
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background-color: #381d51;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
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
        .btn-explore-events {
            background-color: #381d51;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        .btn-explore-events:hover {
            background-color: #4a2a6b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }
        /* Modal Styles */
        .aurora-modal {
            background: linear-gradient(135deg, #1e1e2f 0%, #2a2a4a 100%);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: none;
        }
        .aurora-modal-header {
            background: transparent;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }
        .aurora-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .aurora-close {
            filter: invert(1);
        }
        .aurora-modal-body {
            padding: 2rem;
            color: #ffffff;
        }
        .aurora-label {
            color: #d1d1d1;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        .aurora-input {
            background-color: #ffffff;
            border: 1px solid #cccccc;
            color: #000000;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .aurora-input:focus {
            background-color: #f9f9f9;
            border-color: #602299;
            box-shadow: 0 0 8px rgba(96, 34, 153, 0.3);
            color: #000000;
            outline: none;
        }
        .aurora-btn {
            background: linear-gradient(135deg, #602299 0%, #8a2be2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            color: #ffffff;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .aurora-btn:hover {
            background: linear-gradient(135deg, #8a2be2 0%, #602299 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(96, 34, 153, 0.4);
        }
        .aurora-link {
            color: #b19cd9;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .aurora-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .aurora-subtitle {
            color: #d1d1d1;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .error-message {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            position: relative;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #444;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 30px;
            position: relative;
            z-index: 2;
            font-weight: bold;
            border: 2px solid #444;
            transition: all 0.3s ease;
        }
        .steps:before {
            content: '';
            position: absolute;
            top: 50%;
            left: calc(50% - 80px);
            transform: translateY(-50%);
            width: 160px;
            height: 3px;
            background-color: #444;
            z-index: 1;
        }
        .step.active {
            background-color: #6A1B9A;
            border-color: #6A1B9A;
            box-shadow: 0 0 10px rgba(106, 27, 154, 0.5);
            transform: scale(1.1);
        }
        .step.completed {
            background-color: #4BB543;
            border-color: #4BB543;
        }
        /* Chatbot Styles */
        .chat-message {
            margin-bottom: 15px;
            display: flex;
        }
        .user-message {
            justify-content: flex-end;
        }
        .bot-message {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 80%;
            padding: 15px;
            border-radius: 20px;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .user-message .message-bubble {
            background-color: #ffffff;
            color: #000000;
            border-bottom-right-radius: 4px;
            margin-left: auto;
        }
        .bot-message .message-bubble {
            background-color: #6A1B9A;
            color: white;
            border-bottom-left-radius: 4px;
        }
        .typing-indicator {
            display: flex;
            align-items: center;
        }
        .typing-indicator span {
            height: 7px;
            width: 7px;
            margin: 0 1px;
            background-color: rgba(255, 255, 255, 0.7);
            display: block;
            border-radius: 50%;
            opacity: 0.4;
            animation: typing 1s infinite;
        }
        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.3s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.6s; }
        @keyframes typing {
            0% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
            100% { opacity: 0.4; transform: scale(1); }
        }
        .chatbot-input {
            background-color: rgb(255, 255, 255);
            border: 1px solid #444;
            color: white;
            border-radius: 30px;
            padding: 12px 20px;
        }
        .chatbot-input::placeholder {
            color: white;
        }
        .send-btn {
            background-color: #6A1B9A;
            border-color: #6A1B9A;
            color: white;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
        .send-btn:hover {
            background-color: #8E24AA;
            border-color: #8E24AA;
        }
        /* Captcha Styles */
        .captcha-challenge {
            position: relative;
            height: 80px;
            width: 100%;
            background: linear-gradient(145deg, #3a0b58, #6A1B9A);
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .captcha-text {
            font-family: 'Courier New', monospace;
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 6px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            user-select: none;
        }
        .captcha-line {
            position: absolute;
            height: 2px;
            background-color: rgba(255, 255, 255, 0.5);
            width: 100%;
            transform: rotate(var(--angle));
            opacity: 0.7;
        }
        .captcha-dot {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <main>
        <header class="site-header">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex flex-wrap">
                        <p class="d-flex me-4 mb-0">
                            <i class="bi-person custom-icon me-2"></i>
                            <strong class="text-dark">Bienvenue sur Aurora Event</strong>
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="afficher.php">
                    <img src="../assets/images/logo.png" alt="Logo d'Aurora Event" style="height: 50px; margin-right: 10px">
                    Aurora Event
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_1">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_2">À propos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_3">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_4">Avis</a>
                        </li>
                    </ul>
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="btn custom-btn d-lg-block d-none dropdown-toggle" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                Mon Compte
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userMenu">
                                <li><a class="dropdown-item" href="../front/user_front.php?user_id=<?php echo $_SESSION['user_id']; ?>&type=<?php echo $userType; ?>">Profil</a></li>
                                <li><a class="dropdown-item" href="my_reservations.php">Mes Réservations</a></li>
                                <?php if ($userType === 'organisateur'): ?>
                                    <li><a class="dropdown-item" href="events.php#my-events">Mes Événements</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="events.php?action=logout">Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="#" class="btn custom-btn d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#loginModal">Se connecter</a>
                    <?php endif; ?>
                    <a href="#" id="chatbotTrigger" class="btn custom-btn d-lg-block d-none ms-2" data-bs-toggle="modal" data-bs-target="#chatbotModal">Nous contacter</a>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <section class="hero-section" id="section_1">
            <div class="section-overlay"></div>
            <div class="container d-flex justify-content-center align-items-center">
                <div class="row">
                    <div class="col-12 mt-auto mb-5 text-center">
                        <small>Aurora Event présente</small>
                        <h1 class="text-white mb-5">Soirée Live 2025</h1>
                        <a class="btn custom-btn smoothscroll" href="#" id="commencerButton" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Commencer
                        </a>
                    </div>
                    <div class="col-lg-12 col-12 mt-auto d-flex flex-column flex-lg-row text-center">
                        <div class="date-wrap">
                            <h5 class="text-white">
                                <i class="custom-icon bi-clock me-2"></i>
                                13 - 03<sup>ème</sup>, Mars 2025
                            </h5>
                        </div>
                        <div class="location-wrap mx-auto py-3 py-lg-0">
                            <h5 class="text-white">
                                <i class="custom-icon bi-geo-alt me-2"></i>
                                Gammarth Tunis, Tunisie
                            </h5>
                        </div>
                        <div class="social-share">
                            <ul class="social-icon d-flex align-items-center justify-content-center">
                                <span class="text-white me-3">Partager :</span>
                                <li class="social-icon-item">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://votre-evenement.com" class="social-icon-link" target="_blank">
                                        <span class="bi-facebook"></span>
                                    </a>
                                </li>
                                <li class="social-icon-item">
                                    <a href="https://twitter.com/intent/tweet?url=https://votre-evenement.com&text=Rejoignez%20cet%20événement%20incroyable%20!" class="social-icon-link" target="_blank">
                                        <span class="bi-twitter"></span>
                                    </a>
                                </li>
                                <li class="social-icon-item">
                                    <a href="https://instagram.com/votre-compte-instagram" class="social-icon-link" target="_blank">
                                        <span class="bi-instagram"></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="video-wrap">
                <video autoplay loop muted class="custom-video" poster="">
                    <source src="../assets/video/pexels-2022395.mp4" type="video/mp4">
                    Votre navigateur ne supporte pas les vidéos HTML5.
                </video>
            </div>
        </section>

        <section class="about-section section-padding" id="section_2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 mb-4 mb-lg-0 d-flex align-items-center">
                        <div class="services-info">
                            <h2 class="text-white mb-4">À propos d'Aurora Event</h2>
                            <p class="text-white">
                                Aurora Event est votre plateforme de référence pour créer et participer à des événements qui rassemblent les gens. Que vous souhaitiez rejoindre un concert public, assister à un atelier de cuisine, explorer un cours de peinture ou organiser un événement privé pour des amis, Aurora Event rend cela simple et accessible.
                            </p>
                            <h6 class="text-white mt-4">Créez votre propre expérience</h6>
                            <p class="text-white">
                                Notre plateforme permet à chacun - des artistes et créateurs aux particuliers - d'organiser des événements publics ou privés, en ligne ou dans la vie réelle.
                            </p>
                            <h6 class="text-white mt-4">Connectez-vous, partagez et profitez</h6>
                            <p class="text-white">
                                Avec Aurora Event, chaque moment devient une opportunité de se connecter, de partager et de créer des expériences inoubliables. Merci de faire partie d'Aurora Event !
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="about-text-wrap">
                            <img src="../assets/images/logo.png" class="about-image img-fluid" alt="Logo Aurora Event">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-padding" id="section_3">
            <div class="container">
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="p-4" style="background-color: #f8f9fa; border-radius: 15px;">
                            <h3 class="mb-4 text-center">Pourquoi Choisir Aurora Event ?</h3>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Expériences Sélectionnées</h5>
                                            <p>Nous choisissons les meilleurs événements pour garantir des expériences mémorables.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Réservation Facile</h5>
                                            <p>Processus de réservation simple pour sécuriser votre place.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Support Client</h5>
                                            <p>Équipe dédiée disponible pour répondre à vos questions.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Options Flexibles</h5>
                                            <p>Différentes méthodes de paiement et politiques d'annulation.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="reviews-section section-padding" id="section_4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <h2 class="mb-4">Ce que disent nos utilisateurs</h2>
                        <p>Vos commentaires nous aident à nous améliorer et inspirent d'autres personnes à nous rejoindre !</p>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="../assets/images/image1.png" alt="Utilisateur 1" class="review-image">
                            <h5 class="review-name">Karim</h5>
                            <p class="review-comment">"Ce site a tout changé ! Facile à utiliser et très efficace."</p>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="../assets/images/image2.png" alt="Utilisateur 2" class="review-image">
                            <h5 class="review-name">Manel</h5>
                            <p class="review-comment">"Fantastique ! Je le recommande vivement à tous ceux qui recherchent de la qualité."</p>
                            <div class="review-stars">★★★★☆</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="../assets/images/image3.png" alt="Utilisateur 3" class="review-image">
                            <h5 class="review-name">Emily</h5>
                            <p class="review-comment">"J'ai eu une merveilleuse expérience avec ce site. Excellent service !"</p>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="site-footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 d-flex align-items-center">
                        <img src="../assets/images/logo.png" alt="Logo Aurora Event" style="height: 50px; margin-right: 10px">
                        <h2 class="text-white mb-0">Aurora Event</h2>
                    </div>
                    <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center">
                        <ul class="social-icon d-flex justify-content-lg-end">
                            <li class="social-icon-item">
                                <a href="https://twitter.com/share?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-twitter"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-facebook"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.instagram.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-instagram"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.youtube.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-youtube"></span>
                                </a>
                            </li>
                            <li class="social-icon-item">
                                <a href="https://www.pinterest.com/pin/create/button/?url=https://www.yoursite.com" class="social-icon-link" target="_blank" rel="noopener noreferrer">
                                    <span class="bi-pinterest"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-12 mb-4 pb-2">
                    <h5 class="site-footer-title mb-3">Liens</h5>
                    <ul class="site-footer-links">
                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Accueil</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link click-scroll" href="#section_2">À propos</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link" href="events.php">Événements</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link click-scroll" href="#section_4">Avis</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Une question ?</h5>
                    <p class="text-white d-flex mb-1">
                        <a href="tel: +216 94-166-711" class="site-footer-link">+216 94-166-711</a>
                    </p>
                    <p class="text-white d-flex">
                        <a href="mailto:auroraevent@gmail.com" class="site-footer-link">auroraevent@gmail.com</a>
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Localisation</h5>
                    <p class="text-white d-flex mt-3 mb-2" style="font-size: 0.9rem; white-space: nowrap;">
                        Av. Fethi Zouhir, Cebalat Ben Ammar 2083
                    </p>
                    <a class="link-fx-1 color-contrast-higher mt-3" href="https://www.google.com/maps?q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" target="_blank">
                        <span>Voir sur Maps</span>
                        <svg class="icon" viewBox="0 0 32 32" aria-hidden="true">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="16" cy="16" r="15.5"></circle>
                                <line x1="10" y1="18" x2="16" y2="12"></line>
                                <line x1="16" y1="12" x2="22" y2="18"></line>
                            </g>
                        </svg>
                    </a>
                    <iframe width="100%" height="300" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_API_KEY&q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        <div class="site-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mt-lg-5">
                        <ul class="site-footer-links">
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Conditions générales</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Politique de confidentialité</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Vos retours</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title" id="loginModalLabel">
                        <i class="bi bi-stars me-2"></i>Connexion Aurora
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <?php
                    if (isset($_GET['error'])) {
                        $error = $_GET['error'];
                        if ($error === 'invalid') {
                            echo '<div class="alert alert-danger">Email ou mot de passe incorrect.</div>';
                        } elseif ($error === 'empty') {
                            echo '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
                        } elseif ($error === 'captcha') {
                            echo '<div class="alert alert-danger">Veuillez compléter le captcha correctement.</div>';
                        }
                    }
                    ?>
                    <div id="login-step1">
                        <form method="POST" action="afficher.php" id="loginForm">
                            <div class="form-group">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-envelope-fill me-2"></i>Adresse email
                                </label>
                                <input type="email" class="form-control aurora-input" name="email" id="loginEmail" placeholder="votre@email.com">
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-lock-fill me-2"></i>Mot de passe
                                </label>
                                <input type="password" class="form-control aurora-input" name="password" id="loginPassword" placeholder="••••••••">
                            </div>
                            <div class="d-grid">
                                <button type="button" id="proceedToCaptcha" class="btn aurora-btn py-3">
                                    <i class="bi bi-shield-check me-2"></i>Continuer
                                </button>
                            </div>
                            <input type="hidden" name="action" value="login">
                        </form>
                    </div>
                    <div id="login-step2" style="display: none;">
                        <div class="text-center mb-3">
                            <h6 class="aurora-subtitle">Vérification de sécurité</h6>
                            <p class="text-white-50 small">Veuillez compléter la vérification ci-dessous</p>
                        </div>
                        <div class="captcha-container">
                            <div class="custom-captcha mb-3">
                                <div id="captcha-challenge" class="captcha-challenge mb-2"></div>
                                <div class="input-group">
                                    <input type="text" id="captcha-input" class="form-control aurora-input" placeholder="Entrez le code captcha">
                                    <button type="button" id="refresh-captcha" class="btn btn-outline-secondary">
                                        <i class="bio bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <div id="captcha-error" class="error-message mt-2"></div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" id="verify-captcha" class="btn aurora-btn py-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                            </button>
                            <button type="button" id="backToLogin" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </button>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="#" class="aurora-link" id="showResetPassword">Mot de passe oublié ?</a>
                    </div>
                    <div class="text-center mt-3">
                        <h6 class="aurora-subtitle">Nouveau sur Aurora Event ?</h6>
                        <a href="#" id="showSignupBtn" class="aurora-link">Inscrivez-vous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title" id="signupModalLabel">
                        <i class="bi bi-stars me-2"></i>Inscription Aurora
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <form method="POST" action="afficher.php" id="add-user-form" onsubmit="return validateForm(this)">
                        <div class="form-group">
                            <label class="form-label aurora-label">
                                <i class="bi bi-person-vcard me-2"></i>CIN
                            </label>
                            <input type="text" class="form-control aurora-input" name="cin" id="signupCin" placeholder="Votre CIN" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-person-fill me-2"></i>Nom
                                </label>
                                <input type="text" class="form-control aurora-input" name="nom" id="signupLastName" placeholder="Votre nom" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label aurora-label">
                                    <i class="bi bi-person-fill me-2"></i>Prénom
                                </label>
                                <input type="text" class="form-control aurora-input" name="prenom" id="signupFirstName" placeholder="Votre prénom" required>
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="signupGender" class="form-label aurora-label">
                                <i class="bi bi-gender-ambiguous me-2"></i>Genre
                            </label>
                            <select class="form-select aurora-input" name="genre" id="signupGender" required>
                                <option value="">Sélectionnez un genre</option>
                                <option value="homme">Homme</option>
                                <option value="femme">Femme</option>
                                <option value="autre">Autre</option>
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupPhone" class="form-label aurora-label">
                                <i class="bi bi-telephone-fill me-2"></i>Téléphone
                            </label>
                            <input type="tel" class="form-control aurora-input" name="telephone" id="signupPhone" placeholder="Votre numéro de téléphone" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="birthDate" class="form-label aurora-label">
                                <i class="bi bi-calendar-date me-2"></i>Date de naissance
                            </label>
                            <input type="date" class="form-control aurora-input" name="date_naissance" id="birthDate" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupEmail" class="form-label aurora-label">
                                <i class="bi bi-envelope-fill me-2"></i>Adresse email
                            </label>
                            <input type="email" class="form-control aurora-input" name="email" id="signupEmail" placeholder="votre@email.com" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupType" class="form-label aurora-label">
                                <i class="bi bi-person-badge me-2"></i>Type
                            </label>
                            <select class="form-select aurora-input" name="type" id="signupType" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="participant">Participant</option>
                                <option value="organisateur">Organisateur</option>
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <label for="signupPassword" class="form-label aurora-label">
                                <i class="bi bi-lock-fill me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control aurora-input" name="mot_de_pass" id="signupPassword" placeholder="••••••••" required>
                            <div class="error-message"></div>
                        </div>
                        <input type="hidden" name="signup" value="1">
                        <button type="submit" class="btn aurora-btn w-100 py-3">
                            <i class="bi bi-person-plus-fill me-2"></i>S'inscrire
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <h6 class="aurora-subtitle">Déjà un compte ?</h6>
                        <a href="#" id="showLoginBtn" class="aurora-link">Connectez-vous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
     <!-- Modal de réinitialisation de mot de passe -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content aurora-modal">
              <div class="modal-header aurora-modal-header border-0">
                  <h5 class="modal-title aurora-title">
                      <i class="bi bi-key-fill me-2"></i>Réinitialisation du mot de passe
                  </h5>
                  <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body aurora-modal-body">
                  <!-- Étape 1: Demande de réinitialisation -->
                  <div id="reset-step1" style="display: block;">
                      <div class="steps mb-4">
                          <div class="step active">1</div>
                          <div class="step">2</div>
                          <div class="step">3</div>
                      </div>
  
                      <div class="form-group">
                          <label for="reset-email" class="form-label aurora-label">
                              <i class="bi bi-envelope-fill me-2"></i>Adresse email
                          </label>
                          <input type="email" class="form-control aurora-input" id="reset-email" placeholder="votre@email.com">
                          <div id="reset-email-error" class="error-message"></div>
                      </div>
  
                      <button id="reset-submit-email" class="btn aurora-btn w-100 py-3 mt-3">
                          <i class="bi bi-arrow-right me-2"></i>Continuer
                      </button>
                  </div>
  
                  <!-- Étape 2: Vérification du code -->
                  <div id="reset-step2" style="display: none;">
                      <div class="steps mb-4">
                          <div class="step completed">1</div>
                          <div class="step active">2</div>
                          <div class="step">3</div>
                      </div>
  
                      <p class="text-center text-white mb-4">Nous avons envoyé un code de vérification à <strong id="reset-user-email"></strong></p>
  
                      <div class="form-group">
                          <label for="reset-code" class="form-label aurora-label">
                              <i class="bi bi-shield-lock-fill me-2"></i>Code de vérification
                          </label>
                          <input type="text" class="form-control aurora-input" id="reset-code" placeholder="Entrez le code à 6 chiffres" maxlength="6">
                          <div id="reset-code-error" class="error-message"></div>
                      </div>
  
                      <button id="reset-submit-code" class="btn aurora-btn w-100 py-3 mt-3">
                          <i class="bi bi-check-circle me-2"></i>Vérifier le code
                      </button>
                      <button id="reset-resend-code" class="btn btn-secondary w-100 py-3 mt-2">
                          <i class="bi bi-arrow-repeat me-2"></i>Renvoyer le code
                      </button>
                  </div>
  
                  <!-- Étape 3: Nouveau mot de passe -->
                  <div id="reset-step3" style="display: none;">
                      <div class="steps mb-4">
                          <div class="step completed">1</div>
                          <div class="step completed">2</div>
                          <div class="step active">3</div>
                      </div>
  
                      <div class="form-group">
                          <label for="new-password" class="form-label aurora-label">
                              <i class="bi bi-lock-fill me-2"></i>Nouveau mot de passe
                          </label>
                          <input type="password" class="form-control aurora-input" id="new-password" placeholder="Au moins 8 caractères">
                          <div id="new-password-error" class="error-message"></div>
                      </div>
  
                      <div class="form-group">
                          <label for="confirm-new-password" class="form-label aurora-label">
                              <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
                          </label>
                          <input type="password" class="form-control aurora-input" id="confirm-new-password" placeholder="Retapez votre mot de passe">
                      </div>
  
                      <button id="reset-submit-password" class="btn aurora-btn w-100 py-3 mt-3">
                          <i class="bi bi-check-circle me-2"></i>Réinitialiser le mot de passe
                      </button>
                  </div>
  
                  <!-- Confirmation finale -->
                  <div id="reset-confirmation" style="display: none;" class="text-center">
                      <div class="mb-4">
                          <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #4BB543;"></i>
                      </div>
                      <h5 class="aurora-title mb-3">Mot de passe réinitialisé!</h5>
                      <p class="text-white">Votre mot de passe a été modifié avec succès.</p>
                      <button class="btn aurora-btn mt-3" data-bs-dismiss="modal">
                          <i class="bi bi-box-arrow-in-right me-2"></i>Retour à la connexion
                      </button>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <!-- Chatbot Modal -->
    <div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content aurora-modal">
                <div class="modal-header aurora-modal-header border-0">
                    <h5 class="modal-title aurora-title" id="chatbotModalLabel">
                        <i class="bi bi-robot me-2"></i>Assistant Aurora Event
                    </h5>
                    <button type="button" class="btn-close aurora-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body aurora-modal-body">
                    <div id="chatbot-container" style="height: 400px; overflow-y: auto; margin-bottom: 15px; border-radius: 5px; background-color: #2A2A2A; padding: 15px;">
                        <div class="chat-message bot-message">
                            <div class="message-bubble">Bonjour ! Je suis l'assistant d'Aurora Event. Comment puis-je vous aider aujourd'hui ?</div>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" id="chatbotInput" class="form-control aurora-input chatbot-input" placeholder="Tapez votre message ici..." aria-label="Tapez votre message">
                        <button class="btn custom-btn send-btn" id="sendMessageBtn" type="button">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/jquery.sticky.js"></script>
    <script src="../assets/js/click-scroll.js"></script>
    <script src="../assets/js/custom.js"></script>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Form validation
        function validateForm(form) {
            let isValid = true;
            clearErrors();
            const inputs = form.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    showError(input, 'Ce champ est requis');
                    isValid = false;
                }
            });
            const email = form.querySelector('#signupEmail');
            if (email && !isValidEmail(email.value.trim())) {
                showError(email, 'Veuillez entrer un email valide');
                isValid = false;
            }
            const password = form.querySelector('#signupPassword');
            if (password && password.value.length < 8) {
                showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
                isValid = false;
            }
            return isValid;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showError(input, message) {
            const formGroup = input.closest('.form-group');
            const error = formGroup.querySelector('.error-message') || document.createElement('div');
            error.className = 'error-message';
            error.innerText = message;
            error.style.display = 'block';
            formGroup.appendChild(error);
            input.classList.add('is-invalid');
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(error => {
                error.textContent = '';
                error.style.display = 'none';
            });
            document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
        }

        // Captcha System
        let captchaText = '';
        let captchaExpiry = 0;

        function generateCaptcha() {
            const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
            captchaText = '';
            for (let i = 0; i < 6; i++) {
                captchaText += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            captchaExpiry = Date.now() + 2 * 60 * 1000;
            const captchaContainer = document.getElementById('captcha-challenge');
            captchaContainer.innerHTML = '';
            const textElement = document.createElement('div');
            textElement.className = 'captcha-text';
            textElement.textContent = captchaText;
            captchaContainer.appendChild(textElement);
            for (let i = 0; i < 5; i++) {
                const line = document.createElement('div');
                line.className = 'captcha-line';
                line.style.setProperty('--angle', `${Math.random() * 180}deg`);
                line.style.top = `${Math.random() * 100}%`;
                captchaContainer.appendChild(line);
            }
            for (let i = 0; i < 50; i++) {
                const dot = document.createElement('div');
                dot.className = 'captcha-dot';
                dot.style.left = `${Math.random() * 100}%`;
                dot.style.top = `${Math.random() * 100}%`;
                captchaContainer.appendChild(dot);
            }
            document.getElementById('captcha-input').value = '';
            document.getElementById('captcha-error').textContent = '';
            document.getElementById('captcha-error').style.display = 'none';
        }

        function verifyCaptcha() {
            const input = document.getElementById('captcha-input').value.trim();
            const errorElement = document.getElementById('captcha-error');
            if (Date.now() > captchaExpiry) {
                errorElement.textContent = "Le captcha a expiré. Veuillez réessayer.";
                errorElement.style.display = 'block';
                generateCaptcha();
                return false;
            }
            if (input !== captchaText) {
                errorElement.textContent = "Code captcha incorrect. Veuillez réessayer.";
                errorElement.style.display = 'block';
                generateCaptcha();
                return false;
            }
            return true;
        }

        // Login form handling
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('proceedToCaptcha')) {
                document.getElementById('proceedToCaptcha').addEventListener('click', function() {
                    const email = document.getElementById('loginEmail').value.trim();
                    const password = document.getElementById('loginPassword').value.trim();
                    let isValid = true;
                    clearErrors();
                    if (!email) {
                        showError(document.getElementById('loginEmail'), 'L\'email est requis');
                        isValid = false;
                    } else if (!isValidEmail(email)) {
                        showError(document.getElementById('loginEmail'), 'Veuillez entrer un email valide');
                        isValid = false;
                    }
                    if (!password) {
                        showError(document.getElementById('loginPassword'), 'Le mot de passe est requis');
                        isValid = false;
                    }
                    if (isValid) {
                        document.getElementById('login-step1').style.display = 'none';
                        document.getElementById('login-step2').style.display = 'block';
                        generateCaptcha();
                    }
                });
            }
            if (document.getElementById('refresh-captcha')) {
                document.getElementById('refresh-captcha').addEventListener('click', generateCaptcha);
            }
            if (document.getElementById('verify-captcha')) {
                document.getElementById('verify-captcha').addEventListener('click', function() {
                    if (verifyCaptcha()) {
                        document.getElementById('loginForm').submit();
                    }
                });
            }
            if (document.getElementById('backToLogin')) {
                document.getElementById('backToLogin').addEventListener('click', function() {
                    document.getElementById('login-step2').style.display = 'none';
                    document.getElementById('login-step1').style.display = 'block';
                });
            }

            // Handle "Commencer" button click
            const commencerButton = document.getElementById('commencerButton');
            if (commencerButton) {
                commencerButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                        // Logout the current user
                        fetch('afficher.php?action=logout', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                        })
                        .then(response => {
                            if (response.ok) {
                                // Clear session data on client side
                                sessionStorage.clear();
                                // Show login modal
                                bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')).show();
                            }
                        })
                        .catch(error => console.error('Logout error:', error));
                    } else {
                        // If not logged in, just show the login modal
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')).show();
                    }
                });
            }
        });

        // Modal navigation
        document.getElementById('showSignupBtn').addEventListener('click', function(e) {
            e.preventDefault();
            bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
            new bootstrap.Modal(document.getElementById('signupModal')).show();
        });
        document.getElementById('showLoginBtn').addEventListener('click', function(e) {
            e.preventDefault();
            bootstrap.Modal.getInstance(document.getElementById('signupModal')).hide();
            new bootstrap.Modal(document.getElementById('loginModal')).show();
        });
        document.getElementById('showResetPassword').addEventListener('click', function(e) {
            e.preventDefault();
            bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
            new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
        });

        // Chatbot handling
        const API_KEY = 'AIzaSyC4tFotf2XQ9LEud7A91vpdPdBS58pnS5k';
        const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

        document.getElementById('sendMessageBtn').addEventListener('click', sendChatbotMessage);
        document.getElementById('chatbotInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendChatbotMessage();
        });

        function sendChatbotMessage() {
            const input = document.getElementById('chatbotInput');
            const message = input.value.trim();
            const chatContainer = document.getElementById('chatbot-container');
            if (message) {
                const userMessage = document.createElement('div');
                userMessage.className = 'chat-message user-message';
                userMessage.innerHTML = `<div class="message-bubble">${message}</div>`;
                chatContainer.appendChild(userMessage);
                input.value = '';
                chatContainer.scrollTop = chatContainer.scrollHeight;
                const loadingMessage = document.createElement('div');
                loadingMessage.className = 'chat-message bot-message loading';
                loadingMessage.innerHTML = '<div class="message-bubble"><span class="typing-indicator"><span>.</span><span>.</span><span>.</span></span></div>';
                chatContainer.appendChild(loadingMessage);
                chatContainer.scrollTop = chatContainer.scrollHeight;
                fetchGeminiResponse(message)
                    .then(response => {
                        chatContainer.removeChild(loadingMessage);
                        const botMessage = document.createElement('div');
                        botMessage.className = 'chat-message bot-message';
                        botMessage.innerHTML = `<div class="message-bubble">${response || "Je suis désolé, je n'ai pas pu traiter votre demande. Veuillez réessayer."}</div>`;
                        chatContainer.appendChild(botMessage);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    })
                    .catch(error => {
                        console.error('Erreur API Gemini:', error);
                        chatContainer.removeChild(loadingMessage);
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'chat-message bot-message';
                        errorMessage.innerHTML = `<div class="message-bubble">Désolé, je rencontre des difficultés techniques. Veuillez réessayer plus tard.</div>`;
                        chatContainer.appendChild(errorMessage);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    });
            }
        }

        async function fetchGeminiResponse(message) {
            try {
                const prompt = `Tu es l'assistant virtuel d'Aurora Event, une plateforme de gestion d'événements. 
                Réponds de manière amicale, concise et utile aux questions concernant l'organisation d'événements, 
                la participation à des événements, et notre plateforme. Limite tes réponses à 2-3 phrases.
                Question de l'utilisateur: ${message}`;
                const response = await fetch(`${API_URL}?key=${API_KEY}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        contents: [{ parts: [{ text: prompt }] }]
                    })
                });
                if (!response.ok) throw new Error('Erreur réseau');
                const data = await response.json();
                return data.candidates[0].content.parts[0].text;
            } catch (error) {
                console.error('Erreur lors de l\'appel à l\'API Gemini:', error);
                return null;
            }
        }

        // Password Reset Handling
        document.getElementById('reset-submit-email').addEventListener('click', function() {
            const email = document.getElementById('reset-email').value.trim();
            const emailError = document.getElementById('reset-email-error');
            const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/view/'));
            const resetUrl = baseUrl + '/controller/reset_password.php';
            if (!email) {
                emailError.textContent = "Veuillez entrer votre adresse email";
                emailError.style.display = 'block';
                return;
            }
            if (!isValidEmail(email)) {
                emailError.textContent = "Veuillez entrer une adresse email valide";
                emailError.style.display = 'block';
                return;
            }
            emailError.textContent = "Envoi en cours...";
            emailError.style.color = "#FFD700";
            emailError.style.display = 'block';
            const formData = new FormData();
            formData.append('action', 'send_reset_code');
            formData.append('email', email);
            fetch(resetUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau: ' + response.status);
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse invoalide du serveur: Content-Type incorrect');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.querySelector('#reset-step1 .steps .step:nth-child(1)').classList.remove('active');
                    document.querySelector('#reset-step1 .steps .step:nth-child(1)').classList.add('completed');
                    document.getElementById('reset-step1').style.display = 'none';
                    document.getElementById('reset-step2').style.display = 'block';
                    document.getElementById('reset-user-email').textContent = email;
                } else {
                    emailError.textContent = data.message || "Une erreur s'est produite. Veuillez réessayer.";
                    emailError.style.color = "#ff6b6b";
                    emailError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                emailError.textContent = "Une erreur s'est produite: " + error.message;
                emailError.style.color = "#ff6b6b";
                emailError.style.display = 'block';
            });
        });

        document.getElementById('reset-submit-code').addEventListener('click', function() {
            const code = document.getElementById('reset-code').value.trim();
            const codeError = document.getElementById('reset-code-error');
            const email = document.getElementById('reset-user-email').textContent;
            const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/view/'));
            const resetUrl = baseUrl + '/controller/reset_password.php';
            if (!code) {
                codeError.textContent = "Veuillez entrer le code de vérification";
                codeError.style.display = 'block';
                return;
            }
            codeError.textContent = "Vérification en cours...";
            codeError.style.color = "#FFD700";
            codeError.style.display = 'block';
            const formData = new FormData();
            formData.append('action', 'verify_code');
            formData.append('email', email);
            formData.append('code', code);
            fetch(resetUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau: ' + response.status);
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse invalide du serveur: Content-Type incorrect');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.querySelector('#reset-step2 .steps .step:nth-child(2)').classList.remove('active');
                    document.querySelector('#reset-step2 .steps .step:nth-child(2)').classList.add('completed');
                    document.getElementById('reset-step2').style.display = 'none';
                    document.getElementById('reset-step3').style.display = 'block';
                } else {
                    codeError.textContent = data.message || "Code incorrect. Veuillez réessayer.";
                    codeError.style.color = "#ff6b6b";
                    codeError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                codeError.textContent = "Une erreur s'est produite: " + error.message;
                codeError.style.color = "#ff6b6b";
                codeError.style.display = 'block';
            });
        });

        document.getElementById('reset-resend-code').addEventListener('click', function() {
            const email = document.getElementById('reset-user-email').textContent;
            const codeError = document.getElementById('reset-code-error');
            const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/view/'));
            const resetUrl = baseUrl + '/controller/reset_password.php';
            codeError.textContent = "Envoi en cours...";
            codeError.style.color = "#FFD700";
            codeError.style.display = 'block';
            const formData = new FormData();
            formData.append('action', 'resend_code');
            formData.append('email', email);
            fetch(resetUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau: ' + response.status);
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse invalide du serveur: Content-Type incorrect');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    codeError.textContent = "Un nouveau code a été envoyé à votre adresse email.";
                    codeError.style.color = "#4BB543";
                    codeError.style.display = 'block';
                    setTimeout(() => codeError.textContent = "", 3000);
                } else {
                    codeError.textContent = data.message || "Une erreur s'est produite. Veuillez réessayer.";
                    codeError.style.color = "#ff6b6b";
                    codeError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                codeError.textContent = "Une erreur s'est produite: " + error.message;
                codeError.style.color = "#ff6b6b";
                codeError.style.display = 'block';
            });
        });

        document.getElementById('reset-submit-password').addEventListener('click', function() {
            const newPassword = document.getElementById('new-password').value.trim();
            const confirmPassword = document.getElementById('confirm-new-password').value.trim();
            const passwordError = document.getElementById('new-password-error');
            const email = document.getElementById('reset-user-email').textContent;
            const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/view/'));
            const resetUrl = baseUrl + '/controller/reset_password.php';
            if (!newPassword) {
                passwordError.textContent = "Veuillez entrer un nouveau mot de passe";
                passwordError.style.display = 'block';
                return;
            }
            if (newPassword.length < 8) {
                passwordError.textContent = "Le mot de passe doit contenir au moins 8 caractères";
                passwordError.style.display = 'block';
                return;
            }
            if (newPassword !== confirmPassword) {
                passwordError.textContent = "Les mots de passe ne correspondent pas";
                passwordError.style.display = 'block';
                return;
            }
            passwordError.textContent = "Traitement en cours...";
            passwordError.style.color = "#FFD700";
            passwordError.style.display = 'block';
            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('email', email);
            formData.append('password', newPassword);
            fetch(resetUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau: ' + response.status);
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse invalide du serveur: Content-Type incorrect');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.querySelector('#reset-step3 .steps .step:nth-child(3)').classList.remove('active');
                    document.querySelector('#reset-step3 .steps .step:nth-child(3)').classList.add('completed');
                    document.getElementById('reset-step3').style.display = 'none';
                    document.getElementById('reset-confirmation').style.display = 'block';
                } else {
                    passwordError.textContent = data.message || "Une erreur s'est produite. Veuillez réessayer.";
                    passwordError.style.color = "#ff6b6b";
                    passwordError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                passwordError.textContent = "Une erreur s'est produite: " + error.message;
                passwordError.style.color = "#ff6b6b";
                passwordError.style.display = 'block';
            });
        });
    </script>
</body>
</html>