<?php
// Start session for flash messages
session_start();

// Simulated backend logic (replace with actual backend implementation)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/User.php'; // Assuming a User model to fetch id_user by email
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

$reservationController = new ReservationC();
$reservations = [];

// Handle search by email
if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Fetch id_user by email (simulated)
        $user = User::getByEmail($email); // Replace with actual method
        if ($user && isset($user['id_user'])) {
            $id_user = $user['id_user'];
            // Fetch reservations by id_user
            $reservations = $reservationController->getReservationsByUserId($id_user); // Replace with actual method
            if (empty($reservations)) {
                $_SESSION['error'] = "No reservations found for email: $email.";
            }
        } else {
            $_SESSION['error'] = "No user found with email: $email.";
        }
    } else {
        $_SESSION['error'] = "Please enter a valid email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>- Aurora Event -</title>
    
    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;400;700&display=swap" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/templatemo-festava-live.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    
    <style>
      /* Custom styles for the event section */
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
        transition: transform 1.5s ease-in-out; /* Slower transition */
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
        transition: transform 1.5s ease-in-out; /* Slower transition */
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
        color: #ff6b6b;
      }
      
      .btn-reserve {
        background-color: #ff6b6b;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s;
        align-self: flex-start;
      }
      
      .btn-reserve:hover {
        background-color: #ff5252;
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
        background-color: #ff6b6b;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
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
      
      .message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        text-align: center;
      }
      
      .error {
        background-color: #f8d7da;
        color: #721c24;
      }
      
      /* Responsive adjustments */
      @media (max-width: 992px) {
        .event-container {
          flex-direction: column;
        }
        
        .event-photos, .event-info {
          flex: none;
          width: 100%;
        }
      }
      
      @media (max-width: 576px) {
        .table {
          font-size: 12px;
        }
        
        .table th, .table td {
          padding: 8px;
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
                <strong class="text-dark">Welcome TO Aurora Event </strong>
              </p>
            </div>
          </div>
        </div>
      </header>

      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand" href="index.php">
            <img src="images/logo.png" alt="Logo d'Auroura Event" style="height: 50px; margin-right: 10px" />
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
          <video autoplay="" loop="" muted="" class="custom-video" poster="">
            <source src="video/pexels-2022395.mp4" type="video/mp4" />
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
                <img src="images/logo.png" class="about-image img-fluid" />
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
          
          <!-- Event 1 -->
          <div class="event-container">
            <div class="event-photos">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Concert Event">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" alt="Concert Event 2">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Concert Event 3">
                </div>
              </div>
              <div class="carousel-indicators">
                <div class="carousel-indicator active"></div>
                <div class="carousel-indicator"></div>
                <div class="carousel-indicator"></div>
              </div>
            </div>
            
            <div class="event-info">
              <h3>Summer Music Festival</h3>
              <p>Join us for the biggest music festival of the year featuring international artists and local talents across multiple stages. Experience unforgettable performances under the stars with state-of-the-art sound and lighting.</p>
              <div class="event-details">
                <div class="event-detail">
                  <i class="bi-calendar"></i>
                  <span>June 15-17, 2025</span>
                </div>
                <div class="event-detail">
                  <i class="bi-geo-alt"></i>
                  <span>Gammarth Beach, Tunis</span>
                </div>
                <div class="event-detail">
                  <i class="bi-ticket-perforated"></i>
                  <span>Starting from 50 TND</span>
                </div>
                <div class="event-detail">
                  <i class="bi-clock"></i>
                  <span>6:00 PM - 2:00 AM</span>
                </div>
              </div>
              <button class="btn-reserve">Reserve Now</button>
            </div>
          </div>
          
          <!-- Event 2 -->
          <div class="event-container">
            <div class="event-photos">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Art Exhibition">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1536922246289-88c42f957773?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1504&q=80" alt="Art Exhibition 2">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1578926375605-eaf7559b1458?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1466&q=80" alt="Art Exhibition 3">
                </div>
              </div>
              <div class="carousel-indicators">
                <div class="carousel-indicator active"></div>
                <div class="carousel-indicator"></div>
                <div class="carousel-indicator"></div>
              </div>
            </div>
            
            <div class="event-info">
              <h3>Contemporary Art Exhibition</h3>
              <p>Experience the works of emerging Tunisian artists in this groundbreaking exhibition showcasing modern interpretations of traditional themes. This curated collection represents the most exciting new voices in North African contemporary art.</p>
              <div class="event-details">
                <div class="event-detail">
                  <i class="bi-calendar"></i>
                  <span>July 5-30, 2025</span>
                </div>
                <div class="event-detail">
                  <i class="bi-geo-alt"></i>
                  <span>City of Culture, Tunis</span>
                </div>
                <div class="event-detail">
                  <i class="bi-ticket-perforated"></i>
                  <span>Free Entry</span>
                </div>
                <div class="event-detail">
                  <i class="bi-clock"></i>
                  <span>10:00 AM - 6:00 PM</span>
                </div>
              </div>
              <button class="btn-reserve">Reserve Now</button>
            </div>
          </div>
          
          <!-- Event 3 -->
          <div class="event-container">
            <div class="event-photos">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" alt="Food Festival">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1555244162-803834f70033?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Food Festival 2">
                </div>
                <div class="carousel-item">
                  <img src="https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1469&q=80" alt="Food Festival 3">
                </div>
              </div>
              <div class="carousel-indicators">
                <div class="carousel-indicator active"></div>
                <div class="carousel-indicator"></div>
                <div class="carousel-indicator"></div>
              </div>
            </div>
            
            <div class="event-info">
              <h3>Tunisian Food Festival</h3>
              <p>A culinary journey through Tunisia's rich gastronomic heritage with master chefs, cooking demonstrations, and tastings. Discover traditional recipes, modern fusion cuisine, and the stories behind Tunisia's most beloved dishes.</p>
              <div class="event-details">
                <div class="event-detail">
                  <i class="bi-calendar"></i>
                  <span>August 10-12, 2025</span>
                </div>
                <div class="event-detail">
                  <i class="bi-geo-alt"></i>
                  <span>Hammamet Medina</span>
                </div>
                <div class="event-detail">
                  <i class="bi-ticket-perforated"></i>
                  <span>30 TND per day</span>
                </div>
                <div class="event-detail">
                  <i class="bi-clock"></i>
                  <span>11:00 AM - 10:00 PM</span>
                </div>
              </div>
              <button class="btn-reserve">Reserve Now</button>
            </div>
          </div>

          <!-- Your Reservations Section -->
          <div class="section-title">
            <h2>Your Reservations</h2>
          </div>
          <div class="search-container">
            <form method="POST" action="" id="searchForm" autocomplete="off">
              <div class="search-bar">
                <input type="email" name="email" id="email" placeholder="Enter your email address" required autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
              </div>
            </form>
          </div>

          <div class="table-container">
            <?php if (isset($_SESSION['error'])): ?>
              <div class="message error">
                <?= htmlspecialchars($_SESSION['error']) ?>
              </div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (empty($reservations)): ?>
              <p>Please enter your email address to view your reservations.</p>
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
                      <td><?= htmlspecialchars($reservation['id_reservation'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['event_title'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['nom'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['prenom'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['telephone'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['nombre_places'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['categorie'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($reservation['mode_paiement'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars(isset($reservation['total']) ? number_format($reservation['total'], 2) : 'N/A') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
          
          <!-- Why Choose Us Section -->
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
            <!-- Review Card 1 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="images/image1.png" alt="User 1" class="review-image" />
                <h5 class="review-name">Karim</h5>
                <p class="review-comment">
                  "This website has been a game-changer! Easy to use and very effective."
                </p>
                <div class="review-stars">★★★★★</div>
              </div>
            </div>

            <!-- Review Card 2 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="images/image2.png" alt="User 2" class="review-image" />
                <h5 class="review-name">Manel</h5>
                <p class="review-comment">
                  "Fantastic! I highly recommend it to anyone looking for quality."
                </p>
                <div class="review-stars">★★★★☆</div>
              </div>
            </div>

            <!-- Review Card 3 -->
            <div class="col-lg-4 col-md-6 col-12">
              <div class="review-card">
                <img src="images/image3.png" alt="User 3" class="review-image" />
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
              <img src="images/logo.png" alt="Aurora Event Logo" style="height: 50px; margin-right: 10px" />
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
      // Event Carousel Script with slower animation
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize all carousels on the page
        document.querySelectorAll('.event-photos').forEach(eventPhoto => {
          const carousel = eventPhoto.querySelector('.carousel-inner');
          const items = eventPhoto.querySelectorAll('.carousel-item');
          const indicators = eventPhoto.querySelectorAll('.carousel-indicator');
          let currentIndex = 0;
          const itemCount = items.length;
          
          // Set initial active state
          updateActiveIndicator();
          
          // Auto-rotate carousel every 6 seconds (slower than before)
          let interval = setInterval(nextSlide, 6000);
          
          // Function to go to next slide
          function nextSlide() {
            currentIndex = (currentIndex + 1) % itemCount;
            updateCarousel();
            updateActiveIndicator();
          }
          
          // Function to update carousel position
          function updateCarousel() {
            carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
          }
          
          // Function to update active indicator
          function updateActiveIndicator() {
            indicators.forEach((indicator, index) => {
              if (index === currentIndex) {
                indicator.classList.add('active');
              } else {
                indicator.classList.remove('active');
              }
            });
          }
          
          // Add click event to indicators
          indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
              currentIndex = index;
              updateCarousel();
              updateActiveIndicator();
              // Reset timer when manually changing slide
              clearInterval(interval);
              interval = setInterval(nextSlide, 6000);
            });
          });
          
          // Pause on hover
          carousel.addEventListener('mouseenter', () => {
            clearInterval(interval);
          });
          
          // Resume on mouse leave
          carousel.addEventListener('mouseleave', () => {
            clearInterval(interval);
            interval = setInterval(nextSlide, 6000);
          });
        });
        
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
          anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
              behavior: 'smooth'
            });
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