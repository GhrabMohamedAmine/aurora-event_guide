<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

// Start session for flash messages and user authentication
session_start();

// Get all events
$events = Event::getAll();

// Initialize reservation controller
$reservationController = new ReservationC();
$reservations = [];

// Handle search by id_reservation
if (isset($_POST['id_reservation']) && !empty($_POST['id_reservation'])) {
    $id_reservation = filter_var($_POST['id_reservation'], FILTER_VALIDATE_INT);
    if ($id_reservation !== false) {
        $reservations = $reservationController->getReservationById($id_reservation);
    } else {
        $_SESSION['error'] = "Veuillez entrer un ID de réservation valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aurora Event - Your platform for unforgettable events">
    <meta name="author" content="">
    <title>Aurora Event</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .event-container {
            display: flex;
            flex-direction: row;
            gap: 30px;
            margin-bottom: 40px;
        }

        .event-photos {
            flex: 1;
            position: relative;
            overflow: hidden;
            height: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .event-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .carousel-inner {
            display: flex;
            transition: transform 1.5s ease-in-out;
            height: 100%;
        }

        .carousel-item {
            min-width: 100%;
            height: 100%;
            position: relative;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1.5s ease-in-out;
        }

        .carousel-item:hover img {
            transform: scale(1.05);
        }

        .event-info h3 {
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 700;
            color: #381d51;
        }

        .event-info p {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #666;
        }

        .event-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .event-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 30px;
        }

        .event-detail i {
            color: #381d51;
        }

        .btn-reserve {
            background-color: #381d51;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            align-self: flex-start;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .btn-reserve:hover {
            background-color: #381d51;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .carousel-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .carousel-indicator.active {
            background-color: white;
            transform: scale(1.2);
        }

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

        .events-horizontal-scroll {
            display: flex;
            overflow-x: auto;
            gap: 30px;
            padding: 20px 0;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .events-horizontal-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-thumb {
            background: #381d51;
            border-radius: 10px;
        }

        .events-horizontal-scroll::-webkit-scrollbar-thumb:hover {
            background: #381d51;
        }

        .event-card-horizontal {
            flex: 0 0 350px;
            scroll-snap-align: start;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .event-card-horizontal:hover {
            transform: translateY(-10px);
        }

        .event-card-horizontal img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .event-card-content-horizontal {
            padding: 20px;
        }

        .event-card-content-horizontal h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #381d51;
        }

        .event-card-content-horizontal p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 8px;
        }

        .event-time {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #381d51;
            font-weight: 600;
            margin: 10px 0;
        }

        .event-time i {
            font-size: 1.2rem;
        }

        .event-price {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #381d51;
            font-weight: 600;
            margin: 10px 0;
        }

        .event-price i {
            font-size: 1.2rem;
        }

        .scroll-arrows {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .scroll-arrow {
            background-color: #381d51;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .scroll-arrow:hover {
            background-color: #381d51;
            transform: scale(1.1);
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

        /* Search Bar and Reservations Table */
        .search-container {
            margin: 20px 0;
            text-align: center;
        }

        .search-bar {
            position: relative;
            display: inline-block;
            max-width: 400px;
            width: 100%;
        }

        .search-bar input {
            padding: 10px 40px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        .search-bar button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #381d51;
            font-size: 18px;
            cursor: pointer;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        .table th {
            background-color: #381d51;
            color: white;
            font-size: 13px;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table tr:hover {
            background-color: #f9f9f9;
        }

        @media (max-width: 992px) {
            .event-container {
                flex-direction: column;
            }

            .event-photos, .event-info {
                flex: none;
                width: 100%;
            }

            .event-card-horizontal {
                flex: 0 0 300px;
            }
        }

        @media (max-width: 768px) {
            .event-card-horizontal {
                flex: 0 0 280px;
            }
        }

        @media (max-width: 576px) {
            .event-card-horizontal {
                flex: 0 0 85%;
            }
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
                            <strong class="text-dark">Welcome TO Aurora Event</strong>
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <img src="images/logo.png" alt="Logo d'Auroura Event" style="height: 50px; margin-right: 10px">
                    Aurora Event
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_1">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_2">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_3">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_4">Reviews</a>
                        </li>
                    </ul>
                    <a href="connect.html" class="btn custom-btn d-lg-block d-none">Connect with us</a>
                </div>
            </div>
        </nav>

        <section class="hero-section" id="section_1">
            <div class="section-overlay"></div>
            <div class="container d-flex justify-content-center align-items-center">
                <div class="row">
                    <div class="col-12 mt-auto mb-5 text-center">
                        <small>Aurora Event Presents</small>
                        <h1 class="text-white mb-5">Night Live 2025</h1>
                        <a class="btn custom-btn smoothscroll" href="#section_2">Let's begin</a>
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
                                <span class="text-white me-3">Share:</span>
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
                    <source src="video/pexels-2022395.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </section>

        <section class="about-section section-padding" id="section_2">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 mb-4 mb-lg-0 d-flex align-items-center">
                        <div class="services-info">
                            <h2 class="text-white mb-4">About Aurora Event</h2>
                            <p class="text-white">
                                Aurora Event is your go-to platform for creating and participating in events that bring people together. Whether you want to join a public concert, attend a cooking workshop, explore a painting class, or organize a private event for friends, Aurora Event makes it simple and accessible.
                            </p>
                            <h6 class="text-white mt-4">Create Your Own Experience</h6>
                            <p class="text-white">
                                Our platform empowers anyone—from artists and creators to everyday individuals—to host public or private events, both online and in real life.
                            </p>
                            <h6 class="text-white mt-4">Connect, Share, and Enjoy</h6>
                            <p class="text-white">
                                With Aurora Event, every moment becomes an opportunity to connect, share, and create unforgettable experiences. Thank you for being part of Aurora Event!
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="about-text-wrap">
                            <img src="images/logo.png" class="about-image img-fluid" alt="Aurora Event Logo">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-padding" id="section_3">
            <div class="container">
                <div class="section-title">
                    <h2>Upcoming Events</h2>
                </div>

                <div class="events-grid-container">
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

                    <?php if (empty($events)): ?>
                        <p>No events to display.</p>
                    <?php else: ?>
                        <div class="events-horizontal-scroll" id="eventsScroll">
                            <?php foreach ($events as $event): ?>
                                <div class="event-card-horizontal">
                                    <?php if ($event->getImage()): ?>
                                        <?php $imagePath = htmlspecialchars($event->getImage()); ?>
                                        <img src="../../<?= $imagePath ?>" alt="Event Image">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/350x250?text=No+Image" alt="Default Image">
                                    <?php endif; ?>
                                    <div class="event-card-content-horizontal">
                                        <h4><?= htmlspecialchars($event->getTitre()) ?></h4>
                                        <p><strong>Artist:</strong> <?= htmlspecialchars($event->getArtiste()) ?></p>
                                        <div class="event-time">
                                            <i class="far fa-clock"></i>
                                            <span><?= htmlspecialchars($event->getHeure() ?? 'Time not specified') ?></span>
                                        </div>
                                        <p><strong>Date:</strong> <?= htmlspecialchars($event->getDate()) ?></p>
                                        <p><strong>Location:</strong> <?= htmlspecialchars($event->getLieu()) ?></p>
                                        <div class="event-price">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span><?= htmlspecialchars(number_format($event->getPrix(), 2, '.', '')) ?> TND</span>
                                        </div>
                                        <p><?= htmlspecialchars($event->getDescription() ?? 'No description available.') ?></p>
                                        <div class="event-card-actions">
                                            <a href="reserve.php?id_event=<?= $event->getIdEvent() ?>" class="btn btn-reserve">
                                                <i class="fas fa-ticket-alt"></i> Reserve Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="scroll-arrows">
                            <div class="scroll-arrow" onclick="scrollEvents(-350)">
                                <i class="fas fa-chevron-left"></i>
                            </div>
                            <div class="scroll-arrow" onclick="scrollEvents(350)">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reservations Search and Display -->
                <div class="section-title">
                    <h2>Your Reservations</h2>
                </div>
                <div class="search-container">
                    <form method="POST" action="" id="searchForm" autocomplete="off">
                        <div class="search-bar">
                            <input type="number" name="id_reservation" id="id_reservation" placeholder="Enter your Reservation ID" required autocomplete="off">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>

                <div class="table-container">
                    <?php if (empty($reservations)): ?>
                        <p>No reservations found. Please enter your Reservation ID to view your reservation.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Name</th>
                                    <th>First Name</th>
                                    <th>Phone</th>
                                    <th>Seats</th>
                                    <th>Category</th>
                                    <th>Payment</th>
                                    <th>Total (TND)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($reservation['id_reservation']) ?></td>
                                        <td><?= htmlspecialchars($reservation['event_title']) ?></td>
                                        <td><?= htmlspecialchars($reservation['nom']) ?></td>
                                        <td><?= htmlspecialchars($reservation['prenom'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($reservation['telephone']) ?></td>
                                        <td><?= htmlspecialchars($reservation['nombre_places']) ?></td>
                                        <td><?= htmlspecialchars($reservation['categorie']) ?></td>
                                        <td><?= htmlspecialchars($reservation['mode_paiement']) ?></td>
                                        <td><?= htmlspecialchars(number_format($reservation['total'], 2)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="p-4" style="background-color: #f8f9fa; border-radius: 15px;">
                            <h3 class="mb-4 text-center">Why Choose Aurora Events?</h3>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Curated Experiences</h5>
                                            <p>We handpick the best events to ensure quality and memorable experiences.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Easy Booking</h5>
                                            <p>Simple reservation process to secure your spot at any event.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Customer Support</h5>
                                            <p>Dedicated team available to assist with any questions.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="bi-check-circle-fill" style="color: #602299; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5>Flexible Options</h5>
                                            <p>Various payment methods and cancellation policies available.</p>
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
                        <h2 class="mb-4">What Our Users Say</h2>
                        <p>Your feedback helps us improve and inspires others to join us!</p>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="images/image1.png" alt="User 1" class="review-image">
                            <h5 class="review-name">Karim</h5>
                            <p class="review-comment">
                                "This website has been a game-changer! Easy to use and very effective."
                            </p>
                            <div class="review-stars">★★★★★</div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="images/image2.png" alt="User 2" class="review-image">
                            <h5 class="review-name">Manel</h5>
                            <p class="review-comment">
                                "Fantastic! I highly recommend it to anyone looking for quality."
                            </p>
                            <div class="review-stars">★★★★☆</div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="review-card">
                            <img src="images/image3.png" alt="User 3" class="review-image">
                            <h5 class="review-name">Emily</h5>
                            <p class="review-comment">
                                "I've had a wonderful experience using this website. Great service!"
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
                        <img src="images/logo.png" alt="Aurora Event Logo" style="height: 50px; margin-right: 10px">
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
                    <h5 class="site-footer-title mb-3">Links</h5>
                    <ul class="site-footer-links">
                        <li class="site-footer-link-item">
                            <a href="#" class="site-footer-link">Home</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link click-scroll" href="#section_2">About</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link click-scroll" href="#section_3">Events</a>
                        </li>
                        <li class="site-footer-link-item">
                            <a class="nav-link click-scroll" href="#section_4">Reviews</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-4 mb-lg-0">
                    <h5 class="site-footer-title mb-3">Have a question?</h5>
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
                    <h5 class="site-footer-title mb-3">Location</h5>
                    <p class="text-white d-flex mt-3 mb-2" style="font-size: 0.9rem; white-space: nowrap;">
                        Av. Fethi Zouhir, Cebalat Ben Ammar 2083
                    </p>
                    <a class="link-fx-1 color-contrast-higher mt-3" href="https://www.google.com/maps?q=Lot+13,+V5XR%2BM37+Résidence+Essalem+II,+Av.+Fethi+Zouhir,+Cebalat+Ben+Ammar+2083" target="_blank">
                        <span>Our Maps</span>
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
                                <a href="#" class="site-footer-link">Terms & Conditions</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Privacy Policy</a>
                            </li>
                            <li class="site-footer-link-item">
                                <a href="#" class="site-footer-link">Your Feedback</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/click-scroll.js"></script>
    <script src="js/custom.js"></script>

    <script>
        function scrollEvents(offset) {
            const container = document.getElementById('eventsScroll');
            container.scrollBy({
                left: offset,
                behavior: 'smooth'
            });
        }

        document.addEventListener('keydown', function(e) {
            const container = document.getElementById('eventsScroll');
            if (e.key === 'ArrowLeft') {
                scrollEvents(-350);
            } else if (e.key === 'ArrowRight') {
                scrollEvents(350);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
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

            // Reset the search form on page load
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.reset();
            }
        });
    </script>
</body>
</html>