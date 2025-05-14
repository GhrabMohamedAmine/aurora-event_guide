<?php
// Inclusion des fichiers nécessaires pour la configuration de la base de données et la classe Event
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

// Démarrage de la session pour gérer les données utilisateur (messages d'erreur, coordonnées, etc.)
session_start();

// Vérification de la connexion PDO (nécessaire pour le cache des coordonnées)
// Si la connexion n'est pas initialisée dans config.php, un message d'erreur est loggé et $pdo est défini à null
if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log("PDO connection is not initialized in config.php");
    $pdo = null;
}

// Fonction pour géocoder une adresse en coordonnées (latitude, longitude) via l'API Nominatim avec cache
function geocodeLocation($location) {
    global $pdo; // Utilisation de la connexion PDO globale

    // Tentative d'utiliser le cache si PDO est disponible
    if ($pdo instanceof PDO) {
        try {
            $cacheKey = md5($location); // Clé unique basée sur le hash MD5 de l'adresse
            $stmt = $pdo->prepare("SELECT lat, lng FROM geocode_cache WHERE cache_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute([$cacheKey]); // Recherche dans le cache (valable 30 jours)
            $cached = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cached) {
                return ['lat' => floatval($cached['lat']), 'lng' => floatval($cached['lng'])];
            }
        } catch (PDOException $e) {
            error_log("Database error in geocodeLocation: " . $e->getMessage()); // Log des erreurs de base de données
        }
    }

    // Appel à l'API Nominatim si pas de cache
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($location) . "&limit=1";
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: AuroraEvent/1.0 (contact: auroraevent@gmail.com)\r\n" // User-Agent requis par Nominatim
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context); // Requête HTTP

    // Gestion des erreurs de requête
    if ($response === false) {
        error_log("Geocoding failed for location: $location");
        return null;
    }

    $data = json_decode($response, true); // Décodage de la réponse JSON
    if (isset($data[0]) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        $coords = ['lat' => floatval($data[0]['lat']), 'lng' => floatval($data[0]['lon'])];
        // Sauvegarde dans le cache si PDO est disponible
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("INSERT INTO geocode_cache (cache_key, lat, lng, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE lat = ?, lng = ?, created_at = NOW()");
                $stmt->execute([$cacheKey, $coords['lat'], $coords['lng'], $coords['lat'], $coords['lng']]); // Mise à jour ou insertion
            } catch (PDOException $e) {
                error_log("Failed to cache geocoding result: " . $e->getMessage());
            }
        }
        return $coords;
    }

    error_log("No coordinates found for location: $location");
    return null; // Retour null si aucune coordonnée n'est trouvée
}

// Génération d'un jeton CSRF pour sécuriser les formulaires
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validation de l'ID de l'événement passé dans l'URL
$selected_event_id = filter_input(INPUT_GET, 'id_event', FILTER_VALIDATE_INT); // Filtre pour un entier valide
$selected_event = null;
$event_locations = []; // Tableau pour stocker les emplacements des événements

// Cas où un événement spécifique est sélectionné
if ($selected_event_id && $selected_event_id > 0) {
    $selected_event = Event::getById($selected_event_id); // Récupération de l'événement par ID
    if (!$selected_event) {
        $_SESSION['error'] = "Aucun événement trouvé avec l'ID $selected_event_id.";
        header('Location: afficher.php#section_3'); // Redirection en cas d'erreur
        exit;
    }

    $location = htmlspecialchars($selected_event->getLieu()); // Sécurité contre les injections HTML
    $coords = geocodeLocation($location);
    if ($coords) {
        $event_locations = [
            [
                'id' => $selected_event->getIdEvent(),
                'title' => htmlspecialchars($selected_event->getTitre()),
                'location' => $location,
                'lat' => $coords['lat'],
                'lng' => $coords['lng']
            ]
        ];
    } else {
        $_SESSION['error'] = "Impossible de géocoder l'emplacement : $location.";
    }
}
// Cas où tous les événements sont affichés
else {
    $events = Event::getAll(); // Récupération de tous les événements

    foreach ($events as $event) {
        $location = htmlspecialchars($event->getLieu());
        $coords = geocodeLocation($location);
        if ($coords) {
            $event_locations[] = [
                'id' => $event->getIdEvent(),
                'title' => htmlspecialchars($event->getTitre()),
                'location' => $location,
                'lat' => $coords['lat'],
                'lng' => $coords['lng']
            ];
        } else {
            $_SESSION['error'] = "Impossible de géocoder l'emplacement : $location. Utilisation de coordonnées par défaut.";
            $event_locations[] = [
                'id' => $event->getIdEvent(),
                'title' => htmlspecialchars($event->getTitre()),
                'location' => $location,
                'lat' => 48.8566, // Coordonnées par défaut 
                'lng' => 2.3522
            ];
        }
    }
}

// Gestion de la recherche d'adresse par l'utilisateur
$searched_address = '';
$client_coords = null;
if (isset($_POST['submit_address']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $searched_address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING); // Nettoyage de l'entrée
    if ($searched_address) {
        $coords = geocodeLocation($searched_address);
        if ($coords) {
            $client_coords = $coords;
            $_SESSION['client_coords'] = $client_coords; // Stockage en session
        } else {
            $_SESSION['error'] = "Impossible de géocoder votre emplacement : $searched_address.";
        }
    } else {
        $_SESSION['error'] = "Veuillez entrer une adresse valide.";
    }
} elseif (isset($_SESSION['client_coords'])) {
    $client_coords = $_SESSION['client_coords']; // Récupération des coordonnées de session
    $searched_address = isset($_POST['address']) ? filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) : '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Définition de l'encodage des caractères -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Réglages pour la responsivité -->
    <title>Localisation des Événements - Aurora Event</title>
    <!-- Inclusion des fichiers CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /> <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" /> <!-- Cluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" /> <!-- Style par défaut des clusters -->
    <style>
        /* Styles globaux */
        body {
            background-color: #602299; /* Couleur de fond violet foncé */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Style de la carte d'événement */
        .event-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: white;
        }

        .event-header {
            background-color: #301934;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
            text-align: center;
        }

        .event-content {
            padding: 2rem;
            background: white;
            border-radius: 0 0 15px 15px;
        }

        /* Style de la carte Leaflet */
        .map-container {
            height: 700px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        #map {
            width: 100%;
            height: 100%;
        }

        /* Style de la barre de recherche */
        .search-container {
            margin-bottom: 2rem;
        }

        .search-bar {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-bar input {
            padding: 10px 40px 10px 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 1rem;
        }

        .search-bar button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #301934;
            font-size: 1.2rem;
            cursor: pointer;
        }

        /* Style des boutons */
        .btn-purple {
            background-color: #301934;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .btn-purple:hover {
            background-color: #602299;
            color: white;
        }

        /* Style de la liste des événements */
        .events-list {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .events-list h3 {
            font-size: 1.5rem;
            color: #301934;
            margin-bottom: 1rem;
        }

        .event-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }

        .event-item:hover {
            background-color: #f8f9fa;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .event-item.active {
            background-color: #301934;
            color: white;
            border-color: #301934;
        }

        .event-item h5 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .event-item p {
            font-size: 0.9rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: inherit;
        }

        .event-item .distance {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .event-item.active .distance {
            color: #ddd;
        }

        /* Styles des messages */
        .message {
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 8px;
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

        /* Style du lien de retour */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #301934;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #602299;
        }

        /* Styles de l'en-tête et de la navigation */
        .site-header {
            background-color: #301934;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar-brand img {
            height: 50px;
            margin-right: 10px;
        }

        /* Style de la barre de défilement de la liste */
        .events-list::-webkit-scrollbar {
            width: 6px;
        }

        .events-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .events-list::-webkit-scrollbar-thumb {
            background: #301934;
            border-radius: 10px;
        }

        .events-list::-webkit-scrollbar-thumb:hover {
            background: #602299;
        }

        /* Styles responsives */
        @media (max-width: 768px) {
            .map-container {
                height: 500px;
            }
            .event-card {
                margin: 1rem;
            }
        }

        @media (max-width: 576px) {
            .search-bar {
                max-width: 100%;
            }
            .btn-purple {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- En-tête avec logo et message de bienvenue -->
    <header class="site-header">
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex flex-wrap">
                    <p class="d-flex me-4 mb-0">
                        <img src="images/logo.png" alt="Aurora Event Logo" style="height: 30px; margin-right: 10px;">
                        <strong class="text-white">Welcome to Aurora Event</strong>
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation responsive avec Bootstrap -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Logo d'Aurora Event">
                Aurora Event
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center ms-auto me-lg-5">
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="afficher.php#section_1">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="afficher.php#section_2">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="afficher.php#section_3">Événements</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="afficher.php#section_4">Avis</a>
                    </li>
                </ul>
                <a href="connect.html" class="btn btn-purple d-lg-block d-none">Nous contacter</a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container">
        <div class="event-card">
            <div class="event-header">
                <h2>Localisation des Événements</h2>
            </div>
            <div class="event-content">
                <a href="afficher.php#section_3" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux événements</a>

                <!-- Affichage des messages de succès -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="message success" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Affichage des messages d'erreur -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error" role="alert">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Formulaire de recherche d'adresse -->
                <div class="search-container">
                    <div class="search-bar" role="search">
                        <form method="POST" id="addressSearchForm" aria-label="Rechercher une localisation">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="text" name="address" placeholder="Entrez votre localisation" value="<?php echo htmlspecialchars($searched_address); ?>" aria-label="Adresse">
                            <button type="submit" name="submit_address" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                        </form>
                        <button type="button" id="useCurrentLocation" class="btn btn-purple mt-3">Utiliser ma position actuelle</button>
                        <a href="addEvenement.php?adresseEVN=<?php echo urlencode($searched_address); ?>" class="btn btn-purple mt-3">Valider</a>
                    </div>
                </div>

                <!-- Liste des événements -->
                <div class="events-list">
                    <h3>Événements</h3>
                    <?php if (empty($event_locations)): ?>
                        <p>Aucun événement disponible pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($event_locations as $event): ?>
                            <div class="event-item <?php echo ($selected_event_id == $event['id']) ? 'active' : ''; ?>" data-id="<?= $event['id'] ?>" data-lat="<?= $event['lat'] ?>" data-lng="<?= $event['lng'] ?>" role="button" tabindex="0">
                                <h5><?= $event['title'] ?></h5>
                                <p><i class="fas fa-map-marker-alt"></i> <?= $event['location'] ?></p>
                                <p class="distance" data-id="<?= $event['id'] ?>"></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Conteneur de la carte -->
                <div class="map-container">
                    <div id="map" role="region" aria-label="Carte des événements"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inclusion des fichiers JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery.sticky.js"></script>
    <script src="../js/click-scroll.js"></script>
    <script src="../js/custom.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        // Exécution du code une fois que le DOM est chargé
        document.addEventListener('DOMContentLoaded', function() {
            const eventLocations = <?= json_encode($event_locations) ?>; // Données des événements depuis PHP
            const selectedEventId = <?= $selected_event_id ?: 'null' ?>; // ID de l'événement sélectionné
            const clientCoords = <?= json_encode($client_coords) ?>; // Coordonnées de l'utilisateur

            let map; // Variable pour la carte Leaflet
            let markers = {}; // Objet pour stocker les marqueurs
            let clientMarker = null; // Marqueur de l'utilisateur
            const clusterGroup = L.markerClusterGroup(); // Groupe pour les clusters de marqueurs

            // Initialisation de la carte
            if (eventLocations.length > 0) {
                const firstEvent = eventLocations[0];
                map = L.map('map').setView([firstEvent.lat, firstEvent.lng], 15); // Centrage sur le premier événement
            } else {
                map = L.map('map').setView([48.8566, 2.3522], 10); // Centrage par défaut
            }

            // Ajout des tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Ajustement de la vue pour inclure tous les marqueurs
            if (eventLocations.length > 0 && !selectedEventId) {
                const bounds = L.latLngBounds(eventLocations.map(e => [e.lat, e.lng]));
                if (clientCoords) {
                    bounds.extend([clientCoords.lat, clientCoords.lng]); // Inclure la position de l'utilisateur
                }
                map.fitBounds(bounds, { padding: [50, 50] }); // Ajuster la vue avec un padding
            }

            // Ajout des marqueurs pour chaque événement
            eventLocations.forEach(event => {
                const marker = L.marker([event.lat, event.lng]);
                marker.bindPopup(`<b>${event.title}</b><br>${event.location}`); // Popup avec titre et lieu
                clusterGroup.addLayer(marker); // Ajout au groupe de clusters
                markers[event.id] = marker;

                // Gestion du clic sur un marqueur
                marker.on('click', function() {
                    map.setView([event.lat, event.lng], 17); // Zoom sur le marqueur
                    document.querySelectorAll('.event-item').forEach(item => item.classList.remove('active'));
                    document.querySelector(`.event-item[data-id="${event.id}"]`).classList.add('active'); // Surligner l'événement
                });

                // Si un événement est sélectionné, ouvrir sa popup
                if (selectedEventId && event.id === selectedEventId) {
                    marker.openPopup();
                    map.setView([event.lat, event.lng], 17);
                }
            });
            map.addLayer(clusterGroup); // Ajout des clusters à la carte

            // Calcul de la distance entre deux points (formule de Haversine)
            function calculateDistance(lat1, lng1, lat2, lng2) {
                const R = 6371; // Rayon de la Terre en km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLng / 2) * Math.sin(dLng / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                const distance = R * c;
                return distance.toFixed(2); // Retour en km avec 2 décimales
            }

            // Mise à jour des distances dans la liste des événements
            function updateDistances(clientLat, clientLng) {
                eventLocations.forEach(event => {
                    const distance = calculateDistance(clientLat, clientLng, event.lat, event.lng);
                    const distanceElement = document.querySelector(`.distance[data-id="${event.id}"]`);
                    if (distanceElement) {
                        distanceElement.textContent = `Distance: ${distance} km`;
                    }
                });
            }

            // Ajout du marqueur de l'utilisateur si des coordonnées sont disponibles
            if (clientCoords) {
                clientMarker = L.marker([clientCoords.lat, clientCoords.lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        shadowSize: [41, 41]
                    })
                }).addTo(map);
                clientMarker.bindPopup('<b>Votre localisation</b>').openPopup(); // Popup pour l'utilisateur
                map.setView([clientCoords.lat, clientCoords.lng], 17); // Centrage sur l'utilisateur
                updateDistances(clientCoords.lat, clientCoords.lng); // Mise à jour des distances
            }

            // Gestion des clics sur les éléments de la liste des événements
            document.querySelectorAll('.event-item').forEach(item => {
                item.addEventListener('click', function() {
                    const eventId = parseInt(this.dataset.id);
                    const event = eventLocations.find(e => e.id === eventId);
                    if (event) {
                        map.setView([event.lat, event.lng], 17); // Centrage sur l'événement
                        const marker = markers[eventId];
                        if (marker) {
                            marker.openPopup(); // Ouverture de la popup
                        }
                        document.querySelectorAll('.event-item').forEach(i => i.classList.remove('active')); // Désactivation des autres
                        this.classList.add('active'); // Activation de l'élément cliqué
                    }
                });

                // Support clavier (accessibilité)
                item.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.click(); // Déclencher le clic avec Entrée
                    }
                });
            });

            // Gestion de la recherche d'adresse
            document.getElementById('addressSearchForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Empêcher la soumission classique
                const address = document.querySelector('input[name="address"]').value;
                if (address) {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`, {
                        headers: {
                            'User-Agent': 'AuroraEvent/1.0 (contact: auroraevent@gmail.com)'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                const { lat, lon } = data[0];
                                if (clientMarker) {
                                    map.removeLayer(clientMarker); // Suppression du marqueur existant
                                }
                                clientMarker = L.marker([lat, lon], {
                                    icon: L.icon({
                                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                                        iconSize: [25, 41],
                                        iconAnchor: [12, 41],
                                        popupAnchor: [1, -34],
                                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                        shadowSize: [41, 41]
                                    })
                                }).addTo(map);
                                clientMarker.bindPopup(`<b>Votre localisation</b><br>${address}`).openPopup();
                                map.setView([lat, lon], 17); // Centrage sur la nouvelle position
                                updateDistances(lat, lon); // Mise à jour des distances
                            } else {
                                alert('Adresse non trouvée. Veuillez vérifier l\'adresse saisie.');
                            }
                        })
                        .catch(error => {
                            console.error('Erreur de géocodage:', error);
                            alert('Une erreur s\'est produite lors de la recherche de l\'adresse. Veuillez réessayer plus tard.');
                        });
                } else {
                    alert('Veuillez entrer une adresse valide.');
                }
            });

            // Gestion de la géolocalisation
            document.getElementById('useCurrentLocation').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (clientMarker) {
                            map.removeLayer(clientMarker); // Suppression du marqueur existant
                        }
                        clientMarker = L.marker([lat, lng], {
                            icon: L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                shadowSize: [41, 41]
                            })
                        }).addTo(map);
                        clientMarker.bindPopup('<b>Votre localisation</b>').openPopup();
                        map.setView([lat, lng], 17); // Centrage sur la position actuelle
                        updateDistances(lat, lng); // Mise à jour des distances
                    }, function(error) {
                        alert('Impossible d\'obtenir votre position : ' + error.message); // Gestion des erreurs de géolocalisation
                    });
                } else {
                    alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                }
            });
        });
    </script>
</body>
</html>