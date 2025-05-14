<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../meteo.php');
    exit();
}

// Récupérer tous les événements
$events = Event::getAll();

// Définir la clé API WeatherAPI
$weatherApiKey = 'ad0f74f4a66f4de18a3170229250705';

// Vérifier si la clé API est valide
if (empty($weatherApiKey) || $weatherApiKey === 'YOUR_WEATHER_API_KEY') {
    $errorMessage = "Erreur : Veuillez configurer une clé API WeatherAPI valide dans le fichier meteo.php.";
}

// Récupérer la ville par défaut (Paris)
$defaultCity = 'tunis, tunisie';

// Si un événement spécifique est demandé
$eventId = isset($_GET['id_event']) ? intval($_GET['id_event']) : null;
$selectedEvent = null;
$eventCity = null;
$eventDate = null;

if ($eventId) {
    foreach ($events as $event) {
        if ($event->getIdEvent() == $eventId) {
            $selectedEvent = $event;
            break;
        }
    }
    
    // Récupérer la ville et la date de l'événement
    $eventLieu = $selectedEvent ? $selectedEvent->getLieu() : '';
    $eventDate = $selectedEvent ? strtotime($selectedEvent->getDate()) : time(); // Convertir la date de l'événement en timestamp

    // Créer un tableau dynamique de lieux
    $eventLocations = [];
    $uniqueLieux = [];
    foreach ($events as $event) {
        $lieu = trim($event->getLieu());
        if (!empty($lieu) && !isset($uniqueLieux[$lieu])) {
            $uniqueLieux[$lieu] = $lieu; // Stocker le lieu unique
            // Tentative de normalisation du lieu pour WeatherAPI
            $normalizedCity = $lieu;
            // Détecter un pays pour ajouter un suffixe si nécessaire
            $countryMap = [
               
            ];
            $foundCountry = null;
            foreach ($countryMap as $country => $code) {
                if (stripos($lieu, $country) !== false) {
                    $foundCountry = $code;
                    break;
                }
            }
            if ($foundCountry) {
                // Si le lieu contient une ville et un pays, garder la ville ; sinon utiliser une ville par défaut
                $cityParts = array_map('trim', explode(',', $lieu));
                if (count($cityParts) > 1) {
                    $normalizedCity = $cityParts[0] . ', ' . $foundCountry;
                } else {
                    $defaultCities = [
                       
                    ];
                    $normalizedCity = ($defaultCities[$foundCountry] ?? 'Paris') . ', ' . $foundCountry;
                }
            }
            $eventLocations[$lieu] = $normalizedCity;
        }
    }

    // Trouver la ville pour l'événement sélectionné
    foreach ($eventLocations as $location => $city) {
        if (stripos($eventLieu, $location) !== false) {
            $eventCity = $city;
            $defaultCity = $city;
            break;
        }
    }
}

if (!$eventCity) {
    $eventCity = $defaultCity;
}

// Fonction pour récupérer les prévisions météo pour une date spécifique
function getForecast($city, $apiKey, $date) {
    $cacheKey = "weather_forecast_{$city}_{$date}";
    $cachedData = localStorageGet($cacheKey);
    
    if ($cachedData && (time() - $cachedData['timestamp']) < 3600) {
        return $cachedData['data'];
    }

    $url = "http://api.weatherapi.com/v1/forecast.json?key={$apiKey}&q=" . urlencode($city) . "&dt=" . date('Y-m-d', $date) . "&lang=fr";
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);
    if (!$data || isset($data['error'])) {
        return [];
    }

    $forecast = [
        'temp' => round($data['forecast']['forecastday'][0]['day']['avgtemp_c']),
        'feels_like' => round($data['forecast']['forecastday'][0]['day']['avgtemp_c']), // Approximation
        'humidity' => $data['forecast']['forecastday'][0]['day']['avghumidity'],
        'wind_speed' => $data['forecast']['forecastday'][0]['day']['maxwind_kph'] / 3.6,
        'description' => $data['forecast']['forecastday'][0]['day']['condition']['text'],
        'icon' => $data['forecast']['forecastday'][0]['day']['condition']['icon'],
        'pressure' => $data['forecast']['forecastday'][0]['hour'][0]['pressure_mb'], // Approximation
        'visibility' => $data['forecast']['forecastday'][0]['day']['avgvis_km'] * 1000,
    ];

    localStorageSet($cacheKey, $forecast);
    return $forecast;
}

// Fonctions pour gérer le cache (simulées en PHP)
function localStorageSet($key, $data) {
    $cache = ['timestamp' => time(), 'data' => $data];
    $_SESSION[$key] = $cache;
}

function localStorageGet($key) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
}

// Récupérer les données météo pour la date de l'événement
$currentWeather = isset($errorMessage) ? null : getForecast($eventCity, $weatherApiKey, $eventDate);

// Fonction pour traduire l'icône WeatherAPI en classe FontAwesome
function getWeatherIcon($iconUrl) {
    $iconCode = basename($iconUrl, ".png");
    $iconMap = [
        '113' => 'fas fa-sun text-warning',
        '116' => 'fas fa-cloud-sun text-warning',
        '119' => 'fas fa-cloud text-secondary',
        '122' => 'fas fa-cloud text-secondary',
        '176' => 'fas fa-cloud-sun-rain text-primary',
        '200' => 'fas fa-bolt text-warning',
        '263' => 'fas fa-cloud-rain text-primary',
        '296' => 'fas fa-cloud-rain text-primary',
        '353' => 'fas fa-cloud-showers-heavy text-primary',
        '386' => 'fas fa-bolt text-warning',
        '389' => 'fas fa-bolt text-warning',
        '395' => 'fas fa-snowflake text-info',
        '143' => 'fas fa-smog text-secondary',
    ];
    return $iconMap[$iconCode] ?? 'fas fa-question text-secondary';
}

// Déterminer les recommandations en fonction de la météo
function getWeatherRecommendations($weather) {
    $description = strtolower($weather['description'] ?? '');
    $temp = $weather['temp'] ?? 20;
    $windSpeed = $weather['wind_speed'] ?? 0;
    
    $recommendations = [];
    
    if (stripos($description, 'pluie') !== false || stripos($description, 'orage') !== false) {
        $recommendations[] = "Prévoir un parapluie ou un imperméable.";
        $recommendations[] = "Éviter les zones inondables.";
    }
    
    if ($temp > 30) {
        $recommendations[] = "Porter des vêtements légers et boire beaucoup d'eau.";
        $recommendations[] = "Éviter l'exposition prolongée au soleil.";
    } elseif ($temp < 5) {
        $recommendations[] = "Porter des vêtements chauds et une écharpe.";
        $recommendations[] = "Attention aux surfaces glissantes si gel.";
    }
    
    if ($windSpeed > 10) {
        $recommendations[] = "Attention aux vents forts, sécuriser les objets extérieurs.";
    }
    
    if (stripos($description, 'ensoleillé') !== false) {
        $recommendations[] = "Porter des lunettes de soleil et de la crème solaire.";
    }
    
    return $recommendations ?: ["Aucune recommandation particulière."];
}

// Générer une alerte météo si nécessaire
function getWeatherAlert($weather) {
    $description = strtolower($weather['description'] ?? '');
    $windSpeed = $weather['wind_speed'] ?? 0;
    
    if (stripos($description, 'orage') !== false) {
        return [
            'title' => 'Alerte Orage',
            'message' => 'Possibilité d\'orages dans la région. Restez vigilant et évitez les espaces ouverts.'
        ];
    } elseif ($windSpeed > 15) {
        return [
            'title' => 'Alerte Vent Fort',
            'message' => 'Vents forts attendus. Sécurisez les objets extérieurs et soyez prudent.'
        ];
    } elseif (stripos($description, 'pluie') !== false && $windSpeed > 10) {
        return [
            'title' => 'Alerte Pluie et Vent',
            'message' => 'Pluie et vents forts prévus. Prenez des précautions et évitez les déplacements inutiles.'
        ];
    }
    
    return null;
}

$recommendations = $currentWeather ? getWeatherRecommendations($currentWeather) : [];
$weatherAlert = $currentWeather ? getWeatherAlert($currentWeather) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo - Aurora Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #602299;
            min-height: 100vh;
            color: white;
        }

        /* Sidebar Styles */
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
            width: auto;
        }

        .sidebar-header-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #fff;
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

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #602299;
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            color: #381d51;
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

        .main-nav {
            display: flex;
            gap: 20px;
            margin-left: 20px;
        }

        .main-nav a {
            text-decoration: none;
            color: #381d51;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .main-nav a:hover {
            background-color: #f0e6ff;
        }

        /* Content Section */
        .content-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: #333;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: #381d51;
            font-size: 20px;
        }

        /* Weather Styles */
        .weather-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .weather-current {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #301934, #602299);
            color: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .city-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .current-date {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 20px;
        }

        .current-temp {
            font-size: 48px;
            font-weight: bold;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .temp-unit {
            font-size: 24px;
            margin-left: 5px;
            opacity: 0.9;
        }

        .weather-description {
            font-size: 18px;
            text-transform: capitalize;
            margin-bottom: 20px;
        }

        .weather-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .weather-details-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
            width: 100%;
        }

        .weather-detail {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s;
        }

        .weather-detail:hover {
            transform: translateY(-5px);
            background-color: rgba(255, 255, 255, 0.25);
        }

        .detail-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: bold;
        }

        .detail-icon {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .forecast-container {
            margin-top: 30px;
        }

        .forecast-title {
            color: #381d51;
            font-size: 18px;
            margin-bottom: 15px;
            text-align: center;
        }

        .forecast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .forecast-day {
            background-color: #f5f5f5;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .forecast-day:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .forecast-date {
            font-weight: bold;
            margin-bottom: 10px;
            color: #381d51;
        }

        .forecast-icon {
            font-size: 30px;
            margin: 10px 0;
        }

        .forecast-temps {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .forecast-temp {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .forecast-temp-label {
            font-size: 10px;
            opacity: 0.7;
        }

        .forecast-temp-max {
            color: #ff9800;
            font-weight: bold;
        }

        .forecast-temp-min {
            color: #03a9f4;
            font-weight: bold;
        }

        .forecast-description {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            text-transform: capitalize;
        }

        /* Event Weather Section */
        .event-weather-container {
            margin-top: 30px;
            background: linear-gradient(135deg, #381d51, #602299);
            padding: 25px;
            border-radius: 16px;
            color: white;
        }

        .event-weather-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .event-icon {
            font-size: 36px;
            background-color: rgba(255, 255, 255, 0.2);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-details {
            flex: 1;
        }

        .event-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .event-date-location {
            font-size: 14px;
            opacity: 0.8;
        }

        .event-description {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .recommendations {
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 12px;
        }

        .recommendations-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recommendations-list {
            list-style: none;
            padding: 0;
        }

        .recommendations-list li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .recommendations-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #ff9800;
        }

        /* Weather Alert */
        .weather-alert {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: rgba(255, 152, 0, 0.2);
            border-left: 4px solid #ff9800;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-icon {
            font-size: 24px;
            color: #ff9800;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .alert-message {
            font-size: 14px;
        }

        /* Error Message */
        .error-message {
            text-align: center;
            color: #e74c3c;
            font-size: 16px;
            margin: 20px 0;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content, .site-footer {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            .weather-details-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .forecast-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .main-content, .site-footer {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
            .weather-details-row {
                grid-template-columns: 1fr;
            }
            .forecast-grid {
                grid-template-columns: 1fr;
            }
            .event-weather-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        /* Footer */
        .site-footer {
            background-color: white;
            padding: 20px;
            margin-left: 250px;
            text-align: center;
            box-shadow: 0 -1px 3px rgba(0,0,0,0.1);
            color: #381d51;
            margin-top: auto;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .social-links a {
            color: #381d51;
            font-size: 20px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            color: #602299;
            transform: translateY(-3px);
        }

        .footer-text {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.php" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <span>Events</span>
                </a>
            </li>
            <li class="active">
                <i class="fas fa-cloud-sun"></i>
                <a href="meteo.php" style="color: inherit; text-decoration: none;">
                    <span>Météo</span>
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
                <a href="sponsoring.php" style="color: inherit; text-decoration: none;">
                    <span>Sponsoring</span>
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
                <h2 style="font-size: 18px; color: #381d51;">Prévisions Météo - Aurora Event</h2>
                <div class="main-nav">
                    <a href="index.php"><i class="fas fa-home"></i> Home</a>
                    <a href="afficher.php"><i class="fas fa-calendar"></i> Events</a>
                    <a href="products.php"><i class="fas fa-box"></i> Products</a>
                    <a href="Publications.php"><i class="fas fa-book"></i> Publications</a>
                    <a href="sponsoring.php"><i class="fas fa-exclamation-circle"></i> Sponsoring</a>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="locationSearch" placeholder="Rechercher une ville...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>

        <!-- Weather Content -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-cloud-sun"></i> Prévisions Météo</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="eventDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Choisir un événement
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="eventDropdown">
                        <li><a class="dropdown-item" href="meteo.php">Vue générale</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($events as $event): ?>
                            <li><a class="dropdown-item" href="meteo.php?id_event=<?= $event->getIdEvent() ?>"><?= $event->getTitre() ?> - <?= $event->getArtiste() ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <?php if (isset($errorMessage)): ?>
                <div class="error-message"><?= $errorMessage ?></div>
            <?php else: ?>
                <div class="weather-container">
                    <!-- Current Weather (for Event Date) -->
                    <?php if ($currentWeather): ?>
                        <div class="weather-current">
                            <div class="city-name"><?= $eventCity ?></div>
                            <div class="current-date"><?= date('l j F Y', $eventDate) ?></div>
                            <div class="weather-icon"><i class="<?= getWeatherIcon($currentWeather['icon']) ?>"></i></div>
                            <div class="current-temp"><?= $currentWeather['temp'] ?><span class="temp-unit">°C</span></div>
                            <div class="weather-description"><?= $currentWeather['description'] ?></div>
                            
                            <div class="weather-details-row">
                                <div class="weather-detail">
                                    <div class="detail-icon"><i class="fas fa-thermometer-half"></i></div>
                                    <div class="detail-label">Ressenti</div>
                                    <div class="detail-value"><?= $currentWeather['feels_like'] ?>°C</div>
                                </div>
                                <div class="weather-detail">
                                    <div class="detail-icon"><i class="fas fa-wind"></i></div>
                                    <div class="detail-label">Vent</div>
                                    <div class="detail-value"><?= round($currentWeather['wind_speed'], 1) ?> m/s</div>
                                </div>
                                <div class="weather-detail">
                                    <div class="detail-icon"><i class="fas fa-tint"></i></div>
                                    <div class="detail-label">Humidité</div>
                                    <div class="detail-value"><?= $currentWeather['humidity'] ?>%</div>
                                </div>
                                <div class="weather-detail">
                                    <div class="detail-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    <div class="detail-label">Pression</div>
                                    <div class="detail-value"><?= $currentWeather['pressure'] ?> hPa</div>
                                </div>
                                <div class="weather-detail">
                                    <div class="detail-icon"><i class="fas fa-eye"></i></div>
                                    <div class="detail-label">Visibilité</div>
                                    <div class="detail-value"><?= round($currentWeather['visibility'] / 1000) ?> km</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="error-message">Impossible de charger les données météo pour la date de l'événement. Vérifiez votre clé API ou votre connexion.</div>
                    <?php endif; ?>

                    <!-- Event-Specific Weather -->
                    <?php if ($selectedEvent && $currentWeather): ?>
                        <div class="event-weather-container">
                            <div class="event-weather-header">
                                <div class="event-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="event-details">
                                    <div class="event-title"><?= $selectedEvent->getTitre() ?> - <?= $selectedEvent->getArtiste() ?></div>
                                    <div class="event-date-location">
                                        <?= $selectedEvent->getDate() ?> | <?= $selectedEvent->getLieu() ?>
                                    </div>
                                </div>
                            </div>
                            <div class="event-description">
                                <strong>Description :</strong> <?= $selectedEvent->getDescription() ?>
                            </div>

                            <!-- Recommendations -->
                            <div class="recommendations">
                                <div class="recommendations-title">
                                    <i class="fas fa-lightbulb"></i> Recommandations pour l'événement
                                </div>
                                <ul class="recommendations-list">
                                    <?php foreach ($recommendations as $rec): ?>
                                        <li><?= $rec ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Weather Alert -->
                            <?php if ($weatherAlert): ?>
                                <div class="weather-alert">
                                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                    <div class="alert-content">
                                        <div class="alert-title"><?= $weatherAlert['title'] ?></div>
                                        <div class="alert-message"><?= $weatherAlert['message'] ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            const apiKey = "<?php echo $weatherApiKey; ?>";

            // Recherche de ville dans la barre de recherche
            $('#locationSearch').on('keypress', function(e) {
                if (e.which === 13) { // Touche Entrée
                    const location = $(this).val().trim();
                    if (location) {
                        searchLocation(location);
                    }
                }
            });

            function searchLocation(location) {
                $.getJSON(`http://api.weatherapi.com/v1/current.json?key=${apiKey}&q=${encodeURIComponent(location)}&lang=fr`, function(data) {
                    if (data && !data.error) {
                        window.location.href = `meteo.php?city=${encodeURIComponent(location)}`;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lieu non trouvé',
                            text: 'Veuillez entrer une ville valide (ex. Paris, Tunis).',
                            confirmButtonColor: '#602299'
                        });
                    }
                }).fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de rechercher ce lieu. Vérifiez votre connexion ou la clé API.',
                        confirmButtonColor: '#602299'
                    });
                });
            }
        });
    </script>
</body>
</html>