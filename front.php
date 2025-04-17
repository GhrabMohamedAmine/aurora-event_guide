<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Aurora Event - Plateforme de gestion d'événements" />
    <meta name="author" content="Aurora Team" />

    <title>Aurora Event - Plateforme Événementielle</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/bootstrap-icons.css" rel="stylesheet" />
    <link href="assets/css/templatemo-festava-live.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <main>
        <!-- Header Top Bar -->
        <header class="site-header">
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
        </header>

        <!-- Main Navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="front.php">
                    <img src="assets/images/logo.png" alt="Logo Aurora Event" style="height: 50px; margin-right: 10px">
                    Aurora Event
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                        <li class="nav-item"><a class="nav-link click-scroll" href="#section_1">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link click-scroll" href="#section_2">À propos</a></li>
                        <li class="nav-item"><a class="nav-link click-scroll" href="#section_3">Contact</a></li>
                        <li class="nav-item"><a class="nav-link click-scroll" href="#section_4">Avis</a></li>
                        
                        <?php
                        session_start();
                        if (isset($_SESSION['user'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i> Mon compte
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if ($_SESSION['user']['type'] === 'organisator'): ?>
                                        <li><a class="dropdown-item" href="controller/OrganizerController.php?action=profile">Mon profil</a></li>
                                        <li><a class="dropdown-item" href="controller/OrganizerController.php?action=dashboard">Tableau de bord</a></li>
                                    <?php elseif ($_SESSION['user']['type'] === 'participant'): ?>
                                        <li><a class="dropdown-item" href="controller/ParticipantController.php?action=profile">Mon profil</a></li>
                                        <li><a class="dropdown-item" href="controller/ParticipantController.php?action=dashboard">Tableau de bord</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="controller/AuthController.php?action=logout">Déconnexion</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if (!isset($_SESSION['user'])): ?>
                        <a href="controller/AuthController.php?action=login" class="btn custom-btn d-lg-block d-none">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section" id="section_1">
            <div class="section-overlay"></div>
            <div class="container d-flex justify-content-center align-items-center">
                <div class="row">
                    <div class="col-12 mt-auto mb-5 text-center">
                        <small>Aurora Event présente</small>
                        <h1 class="text-white mb-5">Night Live 2023</h1>
                        <?php if (!isset($_SESSION['user'])): ?>
                            <a class="btn custom-btn smoothscroll" href="controller/AuthController.php?action=login">Commencer</a>
                        <?php else: ?>
                            <?php if ($_SESSION['user']['type'] === 'organisator'): ?>
                                <a class="btn custom-btn smoothscroll" href="controller/OrganizerController.php?action=dashboard">Accéder au tableau de bord</a>
                            <?php else: ?>
                                <a class="btn custom-btn smoothscroll" href="controller/ParticipantController.php?action=dashboard">Accéder à mon espace</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-12 col-12 mt-auto d-flex flex-column flex-lg-row text-center">
                        <div class="date-wrap">
                            <h5 class="text-white">
                                <i class="custom-icon bi-clock me-2"></i>
                                13 - 03<sup>th</sup>, Mar 2025
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
                                    <a href="#" class="social-icon-link"><span class="bi-facebook"></span></a>
                                </li>
                                <li class="social-icon-item">
                                    <a href="#" class="social-icon-link"><span class="bi-twitter"></span></a>
                                </li>
                                <li class="social-icon-item">
                                    <a href="#" class="social-icon-link"><span class="bi-instagram"></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="video-wrap">
                <video autoplay loop muted class="custom-video" poster="">
                    <source src="assets/video/pexels-2022395.mp4" type="video/mp4">
                    Votre navigateur ne supporte pas les vidéos HTML5.
                </video>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section section-padding" id="section_2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 mb-4 mb-lg-0 d-flex align-items-center">
                        <div class="services-info">
                            <h2 class="text-white mb-4">À propos d'Aurora Event</h2>
                            <p class="text-white">
                                Aurora Event est votre plateforme pour créer et participer à des événements qui rassemblent les gens.
                            </p>
                            <h6 class="text-white mt-4">Créez votre propre expérience</h6>
                            <p class="text-white">
                                Notre plateforme permet à chacun d'organiser des événements publics ou privés.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="about-text-wrap">
                            <img src="assets/images/logo.png" class="about-image img-fluid" alt="À propos Aurora">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section section-padding" id="section_3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mx-auto">
                        <h2 class="text-center mb-4">Intéressé ?</h2>
                        
                        <nav class="d-flex justify-content-center">
                            <div class="nav nav-tabs" id="nav-tab">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-ContactForm">
                                    <h5>Formulaire de contact</h5>
                                </button>
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-ContactMap">
                                    <h5>Localisation</h5>
                                </button>
                            </div>
                        </nav>
                        
                        <div class="tab-content shadow-lg mt-5">
                            <div class="tab-pane fade show active" id="nav-ContactForm">
                                <form class="custom-form contact-form mb-5 mb-lg-0">
                                    <div class="contact-form-body">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-12">
                                                <input type="text" name="name" class="form-control" placeholder="Nom complet" required>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-12">
                                                <input type="email" name="email" class="form-control" placeholder="Adresse email" required>
                                            </div>
                                        </div>
                                        <textarea name="message" rows="3" class="form-control" placeholder="Message"></textarea>
                                        <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                            <button type="submit" class="form-control btn btn-primary">Envoyer</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="nav-ContactMap">
                                <iframe class="google-map" src="https://maps.google.com/maps?q=Gammarth,Tunisie&output=embed"
                                    width="100%" height="450" style="border:0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section class="reviews-section section-padding" id="section_4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <h2 class="mb-4">Ce que disent nos utilisateurs</h2>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="assets/images/image1.png" alt="Karim" class="review-image">
                            <h5 class="review-name">Karim</h5>
                            <p class="review-comment">
                                "Ce site a tout changé ! Simple à utiliser et très efficace."
                            </p>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="assets/images/image2.png" alt="Manel" class="review-image">
                            <h5 class="review-name">Manel</h5>
                            <p class="review-comment">
                                "Fantastique ! Je le recommande vivement."
                            </p>
                            <div class="review-stars">★★★★☆</div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="assets/images/image3.png" alt="Emily" class="review-image">
                            <h5 class="review-name">Emily</h5>
                            <p class="review-comment">
                                "Une expérience merveilleuse avec un excellent service !"
                            </p>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="site-footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 d-flex align-items-center">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 50px; margin-right: 10px">
                        <h2 class="text-white mb-0">Aurora Event</h2>
                    </div>
                    <div class="col-lg-6 col-12 d-flex justify-content-lg-end align-items-center">
                        <ul class="social-icon d-flex justify-content-lg-end">
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link"><span class="bi-twitter"></span></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link"><span class="bi-facebook"></span></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link"><span class="bi-instagram"></span></a>
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
                        <li class="site-footer-link-item"><a href="#" class="site-footer-link">Accueil</a></li>
                        <li class="site-footer-link-item"><a href="#section_2" class="site-footer-link">À propos</a></li>
                        <li class="site-footer-link-item"><a href="#section_3" class="site-footer-link">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Contact</h5>
                    <p class="text-white d-flex mb-1">
                        <a href="tel:+21694166711" class="site-footer-link">+216 94 166 711</a>
                    </p>
                    <p class="text-white d-flex">
                        <a href="mailto:auroraevent@gmail.com" class="site-footer-link">auroraevent@gmail.com</a>
                    </p>
                </div>

                <div class="col-lg-3 col-md-6 col-11 mb-4 mb-lg-0 mb-md-0">
                    <h5 class="site-footer-title mb-3">Localisation</h5>
                    <p class="text-white d-flex mt-3 mb-2">
                        Av. Fethi Zouhir, Cebalat Ben Ammar 2083
                    </p>
                    <a href="https://maps.google.com?q=Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" class="link-fx-1" target="_blank">
                        <span>Voir sur la carte</span>
                        <svg class="icon" viewBox="0 0 32 32" aria-hidden="true">
                            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="16" cy="16" r="15.5"></circle>
                                <line x1="10" y1="18" x2="16" y2="12"></line>
                                <line x1="16" y1="12" x2="22" y2="18"></line>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mt-lg-5">
                        <ul class="site-footer-links">
                            <li class="site-footer-link-item"><a href="#" class="site-footer-link">Mentions légales</a></li>
                            <li class="site-footer-link-item"><a href="#" class="site-footer-link">Politique de confidentialité</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.sticky.js"></script>
    <script src="assets/js/click-scroll.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/scripts.js"></script>
    
    <script>
    // Gestion de la navigation pour utilisateurs connectés
    document.addEventListener('DOMContentLoaded', function() {
        // Activation des tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Gestion du scroll smooth
        $('a.click-scroll').on('click', function(event) {
            if (this.hash !== '') {
                event.preventDefault();
                const hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function() {
                    window.location.hash = hash;
                });
            }
        });
    });
    </script>
</body>
</html>