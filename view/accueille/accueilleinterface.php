<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/user_controller.php';

// Start the session
session_start();

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $db = config::getConnexion();
    $userController = new UserController($db);

    // Validate required fields
    $required_fields = ['cin', 'nom', 'prenom', 'genre', 'telephone', 'date_naissance', 'email', 'type', 'mot_de_pass'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $_SESSION['error_message'] = "Tous les champs sont obligatoires";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Sanitize and prepare user data
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

    // Create the user  
    $result = $userController->createUser($userData);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = $result['message'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = config::getConnexion();
    $userController = new UserController($db);
    
    // Handle login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $result = $userController->login($email, $password);
        
        if ($result === false) {
            header('Location: accueilleinterface.php?error=invalid');
            exit();
        }
        // Get the user ID and type for the redirect
        $stmt = $db->prepare("SELECT id_user, type FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Redirect based on user type
        switch ($user['type']) {
            case 'admin':
                header('Location: ../back/user_back.php?user_id=' . $user['id_user']);
                break;
            case 'organisateur':
                header('Location: ../front/user_front.php?user_id=' . $user['id_user'] . '&type=organisateur');
                break;
            case 'participant':
                header('Location: ../front/user_front.php?user_id=' . $user['id_user'] . '&type=participant');
                break;
            default:
                header('Location: ../front/user_front.php?user_id=' . $user['id_user']);
        }
        exit();
    }
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Plateforme de gestion d'événements Aurora Event" />
    <meta name="author" content="Aurora Event Team" />
    <title>Aurora Event - Accueil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="crossorigin" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&amp;display=swap" rel="stylesheet" />
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/bootstrap-icons.css" rel="stylesheet" />
    <link href="../assets/css/templatemo-festava-live.css" rel="stylesheet" />
  </head>
  <body>
    <div class="main-content">
      <div class="site-header">
        <div class="container">
          <div class="row">
            <div class="col-lg-12 col-12 d-flex flex-wrap">
              <p class="d-flex me-4 mb-0">
                <i class="bi-person custom-icon me-2"></i>
                <strong class="text-dark">Bienvenue sur Aurora Event</strong>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="accueil.php">Aurora Event</a>

          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
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

            <a href="#" id="chatbotTrigger" class="btn custom-btn d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#chatbotModal">
              Nous contacter
            </a>
          </div>
        </div>
      </div>

      <div class="hero-section" id="section_1">
        <div class="section-overlay"></div>

        <div class="container d-flex justify-content-center align-items-center">
          <div class="row">
            <div class="col-12 mt-auto mb-5 text-center">
              <small>Aurora Event présente</small>

              <h1 class="text-white mb-5">Soirée Live 2025</h1>

              <a class="btn custom-btn smoothscroll" href="#section_1" data-bs-toggle="modal" data-bs-target="#loginModal">
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
          <video autoplay="" loop="" muted="" class="custom-video" poster="">
            <source src="../assets/video/pexels-2022395.mp4" type="video/mp4" />
            Votre navigateur ne supporte pas les vidéos HTML5.
          </video>
        </div>
      </div>

      <div class="about-section section-padding" id="section_2">
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
                <img src="../assets/images/logo.png" class="about-image img-fluid" alt="Logo Aurora Event" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="contact-section section-padding" id="section_3">
        <div class="container">
          <div class="row">
            <div class="col-lg-8 col-12 mx-auto">
              <h2 class="text-center mb-4">Intéressé ?</h2>
      
              <div class="nav nav-tabs align-items-baseline justify-content-center" id="nav-tab" role="tablist">
      <section class="contact-section section-padding" id="section_3">
        <div class="container">
          <div class="row">
            <div class="col-lg-8 col-12 mx-auto">
              <h2 class="text-center mb-4">Intéressé ?</h2>
      
              <!-- Navigation des onglets -->
              <nav class="d-flex justify-content-center">
                <div class="nav nav-tabs align-items-baseline justify-content-center" id="nav-tab" role="tablist">
                  <button class="nav-link active" id="nav-ContactForm-tab" data-bs-toggle="tab" data-bs-target="#nav-ContactForm" type="button" role="tab" aria-controls="nav-ContactForm" aria-selected="true">
                    <h5>Formulaire de contact</h5>
                  </button>
                  <button class="nav-link" id="nav-ContactMap-tab" data-bs-toggle="tab" data-bs-target="#nav-ContactMap" type="button" role="tab" aria-controls="nav-ContactMap" aria-selected="false">
                    <h5>Google Maps</h5>
                  </button>
                </div>
              </nav>
      
              <!-- Contenu des onglets -->
              <div class="tab-content shadow-lg mt-5" id="nav-tabContent">
                <!-- Formulaire de contact -->
                <div class="tab-pane fade show active" id="nav-ContactForm" role="tabpanel" aria-labelledby="nav-ContactForm-tab">
                  <form class="custom-form contact-form mb-5 mb-lg-0" action="submit-contact-form.php" method="POST" role="form" id="contactForm" onsubmit="return validateContactForm(event)">
                    <div class="contact-form-body">
                      <div class="row">
                        <div class="col-lg-6 col-md-6 col-12">
                          <input type="text" name="name" id="contact-name" class="form-control" placeholder="Nom complet" />
                          <div class="error-message" id="name-error"></div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                          <input type="email" name="email" id="contact-email" class="form-control" placeholder="Adresse email" />
                          <div class="error-message" id="email-error"></div>
                        </div>
                      </div>
      
                      <textarea name="message" rows="3" class="form-control" id="contact-message" placeholder="Message"></textarea>
                      <div class="error-message" id="message-error"></div>
                      <div class="col-lg-4 col-md-10 col-8 mx-auto">
                        <button type="submit" class="form-control btn btn-primary">
                          Envoyer le message
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
      
                <!-- Carte Google Maps -->
                <div class="tab-pane fade" id="nav-ContactMap" role="tabpanel" aria-labelledby="nav-ContactMap-tab">
                  <iframe class="google-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3192.214759901409!2d10.211830!3d36.8831541!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12fd34d79fb4d121%3A0x2934de95ebfdc9c8!2sZone%20Industrielle%20Chotrana%20II%2C%20B.P.%20160%20P%C3%B4le%20Technologique%20El%20Ghazela%202083%20Ariana%20Tunis!5e0!3m2!1sfr!2stn!4v1670344209509!5m2!1sfr!2stn" width="100%" height="450" style="border: 0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
              <p>
                Vos commentaires nous aident à nous améliorer et inspirent d'autres personnes à nous rejoindre !
              </p>
            </div>
          </div>

          <div class="row mt-5">
            <!-- Avis 1 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="../assets/images/image1.png" alt="Utilisateur 1" class="review-image" />
                <h5 class="review-name">Karim</h5>
                <p class="review-comment">
                  "Ce site a tout changé ! Facile à utiliser et très efficace."
                </p>
                <div class="review-stars">★★★★★</div>
              </div>
            </div>

            <!-- Avis 2 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="../assets/images/image2.png" alt="Utilisateur 2" class="review-image" />
                <h5 class="review-name">Manel</h5>
                <p class="review-comment">
                  "Fantastique ! Je le recommande vivement à tous ceux qui recherchent de la qualité."
                </p>
                <div class="review-stars">★★★★☆</div>
              </div>
            </div>

            <!-- Avis 3 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="../assets/images/image3.png" alt="Utilisateur 3" class="review-image" />
                <h5 class="review-name">Emily</h5>
                <p class="review-comment">
                  "J'ai eu une merveilleuse expérience avec ce site. Excellent service !"
                </p>
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
              <img src="../assets/images/logo.png" alt="Logo Aurora Event" style="height: 50px; margin-right: 10px" />
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
                <a class="nav-link click-scroll" href="#section_3">Contact</a>
              </li>

              <li class="site-footer-link-item">
                <a class="nav-link click-scroll" href="#section_4">Avis</a>
              </li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
            <h5 class="site-footer-title mb-3">Une question ?</h5>

            <p class="text-white d-flex mb-1">
              <a href="tel: +216 94-166-711" class="site-footer-link">
                +216 94-166-711
              </a>
            </p>

            <p class="text-white d-flex">
              <a href="mailto:auroraevent@gmail.com" class="site-footer-link">
                auroraevent@gmail.com
              </a>
            </p>
          </div>

          <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
            <h5 class="site-footer-title mb-3">Localisation</h5>
          
            <p class="text-white d-flex mt-3 mb-2" style="font-size: 0.9rem; white-space: nowrap;">
                Av. Fethi Zouhir, Cebalat Ben Ammar 2083
            </p>
              
            
            <!-- Lien vers Google Maps -->
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
              
            
            <!-- Google Maps embed -->
            <iframe
              width="100%" height="300" frameborder="0" style="border:0"
              src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_API_KEY&q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083"
              allowfullscreen>
            </iframe>
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
    
    <!-- Modal de connexion -->
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
                      }
                  }
                  ?>
                  <form method="POST" action="accueilleinterface.php" id="loginForm">
                    <div class="form-group">
                      <label class="form-label aurora-label">
                          <i class="bi bi-envelope-fill me-2"></i>Adresse email
                      </label>
                      <input type="email" class="form-control aurora-input" name="email" id="loginEmail" placeholder="votre@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label aurora-label">
                            <i class="bi bi-lock-fill me-2"></i>Mot de passe
                        </label>
                        <input type="password" class="form-control aurora-input" name="password" id="loginPassword" placeholder="••••••••">
                    </div>
                    <input type="hidden" name="action" value="login">
                    <button type="submit" class="btn aurora-btn w-100 py-3">
                      <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                  </form>
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
                  <div id="reset-step1">
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
                          <div id="reset-email-error" class="error-message hidden"></div>
                      </div>
  
                      <button id="reset-submit-email" class="btn aurora-btn w-100 py-3 mt-3">
                          <i class="bi bi-arrow-right me-2"></i>Continuer
                      </button>
                  </div>
  
                  <!-- Étape 2: Vérification du code -->
                  <div id="reset-step2" class="hidden">
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
                          <div id="reset-code-error" class="error-message hidden"></div>
                      </div>
  
                      <button id="reset-submit-code" class="btn aurora-btn w-100 py-3 mt-3">
                          <i class="bi bi-check-circle me-2"></i>Vérifier le code
                      </button>
                      <button id="reset-resend-code" class="btn btn-secondary w-100 py-3 mt-2">
                          <i class="bi bi-arrow-repeat me-2"></i>Renvoyer le code
                      </button>
                  </div>
  
                  <!-- Étape 3: Nouveau mot de passe -->
                  <div id="reset-step3" class="hidden">
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
                          <div id="new-password-error" class="error-message hidden"></div>
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
                  <div id="reset-confirmation" class="hidden text-center">
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
    
    <!-- Modal d'inscription -->
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
                  <?php
                  if (isset($_GET['signup_error'])) {
                      $error = $_GET['signup_error'];
                      if ($error === 'email_exists') {
                          echo '<div class="alert alert-danger">Cette adresse email est déjà utilisée.</div>';
                      } elseif ($error === 'empty_fields') {
                          echo '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
                      }
                  }
                  ?>
                  <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="add-user-form" onsubmit="return validateForm(this)">
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
    
    <!-- Modal du chatbot -->
    <div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="chatbotModalLabel">Assistant Aurora Event</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <!-- Interface du chatbot -->
                  <div id="chatbot-container" style="height: 400px; overflow-y: auto; margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                      <div class="chat-message bot-message">
                          <p>Bonjour ! Je suis l'assistant d'Aurora Event. Comment puis-je vous aider aujourd'hui ?</p>
                      </div>
                      <!-- Les messages de chat apparaîtront ici -->
                  </div>
                  <div class="input-group">
                      <input type="text" id="chatbotInput" class="form-control" placeholder="Tapez votre message ici..." aria-label="Tapez votre message">
                      <button class="btn btn-primary" id="sendMessageBtn" type="button">Envoyer</button>
                  </div>
              </div>
              <div class="modal-footer">
                  <small class="text-muted">Notre assistant est disponible 24h/24 pour répondre à vos questions</small>
              </div>
          </div>
      </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/jquery.sticky.js"></script>
    <script src="../assets/js/click-scroll.js"></script>
    <script src="../assets/js/custom.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script src="../assets/js/inscri.js"></script>
    <script src="../assets/js/client.js"></script>
    
    <script>
        // Fonction de validation du formulaire de connexion
        function validateLoginForm(event) {
            event.preventDefault();
            
            // Récupérer les champs
            const email = document.getElementById('loginEmail');
            const password = document.getElementById('loginPassword');
            let isValid = true;
            
            // Réinitialiser les messages d'erreur
            clearErrors();
            
            // Valider l'email
            if (!email.value.trim()) {
                showError(email, 'L\'email est requis');
                isValid = false;
            } else if (!isValidEmail(email.value.trim())) {
                showError(email, 'Veuillez entrer un email valide');
                isValid = false;
            }
            
            // Valider le mot de passe
            if (!password.value.trim()) {
                showError(password, 'Le mot de passe est requis');
                isValid = false;
            } else if (password.value.length < 6) {
                showError(password, 'Le mot de passe doit contenir au moins 6 caractères');
                isValid = false;
            }
            
            // Si tout est valide, soumettre le formulaire
            if (isValid) {
                document.getElementById('loginForm').submit();
            }
        }
        
        // Fonction pour vérifier le format de l'email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Fonction pour afficher les messages d'erreur
        function showError(input, message) {
            const formGroup = input.closest('.form-group');
            const error = document.createElement('div');
            error.className = 'invalid-feedback d-block';
            error.innerText = message;
            formGroup.appendChild(error);
            input.classList.add('is-invalid');
        }
        
        // Fonction pour effacer les messages d'erreur
        function clearErrors() {
            const errorMessages = document.querySelectorAll('.invalid-feedback');
            const invalidInputs = document.querySelectorAll('.is-invalid');
            errorMessages.forEach(error => error.remove());
            invalidInputs.forEach(input => input.classList.remove('is-invalid'));
        }
        
        // Ajouter l'événement de validation au formulaire de connexion
        document.getElementById('loginForm').addEventListener('submit', validateLoginForm);

        // Gestion des modals
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher le modal d'inscription depuis le modal de connexion
            document.getElementById('showSignupBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                loginModal.hide();
                
                var signupModal = new bootstrap.Modal(document.getElementById('signupModal'));
                signupModal.show();
            });
            
            // Afficher le modal de connexion depuis le modal d'inscription
            document.getElementById('showLoginBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var signupModal = bootstrap.Modal.getInstance(document.getElementById('signupModal'));
                signupModal.hide();
                
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            });
            
            // Afficher le modal de réinitialisation de mot de passe
            document.getElementById('showResetPassword').addEventListener('click', function(e) {
                e.preventDefault();
                var loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                loginModal.hide();
                
                var resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                resetModal.show();
            });
            
            // Chatbot simple
            document.getElementById('sendMessageBtn').addEventListener('click', function() {
                const input = document.getElementById('chatbotInput');
                const message = input.value.trim();
                
                if (message) {
                    // Ajouter le message de l'utilisateur
                    const userMessage = document.createElement('div');
                    userMessage.className = 'chat-message user-message';
                    userMessage.innerHTML = `<p>${message}</p>`;
                    document.getElementById('chatbot-container').appendChild(userMessage);
                    
                    // Réponse automatique
                    setTimeout(function() {
                        const botMessage = document.createElement('div');
                        botMessage.className = 'chat-message bot-message';
                        botMessage.innerHTML = '<p>Merci pour votre message. Notre équipe vous répondra dès que possible.</p>';
                        document.getElementById('chatbot-container').appendChild(botMessage);
                        
                        // Faire défiler vers le bas
                        document.getElementById('chatbot-container').scrollTop = document.getElementById('chatbot-container').scrollHeight;
                    }, 1000);
                    
                    // Effacer le champ de saisie
                    input.value = '';
                    
                    // Faire défiler vers le bas
                    document.getElementById('chatbot-container').scrollTop = document.getElementById('chatbot-container').scrollHeight;
                }
            });
            
            // Permettre d'envoyer un message avec la touche Entrée
            document.getElementById('chatbotInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('sendMessageBtn').click();
                }
            });
        });

        // Validation du formulaire de contact
        function validateContactForm(event) {
          event.preventDefault();
          let isValid = true;
          
          // Clear previous error messages
          clearErrors();
          
          // Validate name
          const name = document.getElementById('contact-name');
          if (!name.value.trim()) {
            showError('name-error', 'Le nom est requis');
            isValid = false;
          }
          
          // Validate email
          const email = document.getElementById('contact-email');
          if (!email.value.trim()) {
            showError('email-error', 'L\'email est requis');
            isValid = false;
          } else if (!isValidEmail(email.value.trim())) {
            showError('email-error', 'Veuillez entrer un email valide');
            isValid = false;
          }
          
          // Validate message
          const message = document.getElementById('contact-message');
          if (!message.value.trim()) {
            showError('message-error', 'Le message est requis');
            isValid = false;
          }
          
          if (isValid) {
            document.getElementById('contactForm').submit();
          }
          
          return false;
        }
        
        function isValidEmail(email) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return emailRegex.test(email);
        }
        
        function showError(elementId, message) {
          const errorElement = document.getElementById(elementId);
          if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.color = '#dc3545';
            errorElement.style.fontSize = '0.875em';
            errorElement.style.marginTop = '0.25rem';
          }
        }
        
        function clearErrors() {
          const errorElements = document.getElementsByClassName('error-message');
          for (let element of errorElements) {
            element.textContent = '';
          }
        }
    </script>
  </body>
</html>