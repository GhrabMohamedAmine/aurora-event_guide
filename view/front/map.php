<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

session_start();

// Check if PDO is available
if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log("PDO connection is not initialized in config.php");
    $pdo = null; // Explicitly set to null to avoid undefined variable issues
}

// Function to geocode location using Nominatim API with optional caching
function geocodeLocation($location) {
    global $pdo;

    // Attempt to use cache if PDO is available
    if ($pdo instanceof PDO) {
        try {
            $cacheKey = md5($location);
            $stmt = $pdo->prepare("SELECT lat, lng FROM geocode_cache WHERE cache_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute([$cacheKey]);
            $cached = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cached) {
                return ['lat' => floatval($cached['lat']), 'lng' => floatval($cached['lng'])];
            }
        } catch (PDOException $e) {
            error_log("Database error in geocodeLocation: " . $e->getMessage());
        }
    }

    // Geocode using Nominatim API
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($location) . "&limit=1";
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: AuroraEvent/1.0 (contact: auroraevent@gmail.com)\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        error_log("Geocoding failed for location: $location");
        return null;
    }

    $data = json_decode($response, true);
    if (isset($data[0]) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        $coords = ['lat' => floatval($data[0]['lat']), 'lng' => floatval($data[0]['lon'])];
        // Save to cache if PDO is available
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("INSERT INTO geocode_cache (cache_key, lat, lng, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE lat = ?, lng = ?, created_at = NOW()");
                $stmt->execute([$cacheKey, $coords['lat'], $coords['lng'], $coords['lat'], $coords['lng']]);
            } catch (PDOException $e) {
                error_log("Failed to cache geocoding result: " . $e->getMessage());
            }
        }
        return $coords;
    }

    error_log("No coordinates found for location: $location");
    return null;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate event ID from URL (if provided)
$selected_event_id = filter_input(INPUT_GET, 'id_event', FILTER_VALIDATE_INT);
$selected_event = null;
$event_locations = [];

if ($selected_event_id && $selected_event_id > 0) {
    $selected_event = Event::getById($selected_event_id);
    if (!$selected_event) {
        $_SESSION['error'] = "Aucun événement trouvé avec l'ID $selected_event_id.";
        header('Location: afficher.php#section_3');
        exit;
    }

    $location = htmlspecialchars($selected_event->getLieu());
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
} else {
    $events = Event::getAll();

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
                'lat' => 48.8566, // Default to Paris
                'lng' => 2.3522
            ];
        }
    }
}

// Handle address search and store client's coordinates
$searched_address = '';
$client_coords = null;
if (isset($_POST['submit_address']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $searched_address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    if ($searched_address) {
        $coords = geocodeLocation($searched_address);
        if ($coords) {
            $client_coords = $coords;
            $_SESSION['client_coords'] = $client_coords; // Store in session
        } else {
            $_SESSION['error'] = "Impossible de géocoder votre emplacement : $searched_address.";
        }
    } else {
        $_SESSION['error'] = "Veuillez entrer une adresse valide.";
    }
} elseif (isset($_SESSION['client_coords'])) {
    $client_coords = $_SESSION['client_coords'];
    $searched_address = isset($_POST['address']) ? filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) : '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localisation des Événements - Aurora Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        body {
            background-color: #602299;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

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

    <div class="container">
        <div class="event-card">
            <div class="event-header">
                <h2>Localisation des Événements</h2>
            </div>
            <div class="event-content">
                <a href="afficher.php#section_3" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux événements</a>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="message success" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error" role="alert">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

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

                <div class="map-container">
                    <div id="map" role="region" aria-label="Carte des événements"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/jquery.sticky.js"></script>
    <script src="../js/click-scroll.js"></script>
    <script src="../js/custom.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventLocations = <?= json_encode($event_locations) ?>;
            const selectedEventId = <?= $selected_event_id ?: 'null' ?>;
            const clientCoords = <?= json_encode($client_coords) ?>;

            let map;
            let markers = {};
            let clientMarker = null;
            const clusterGroup = L.markerClusterGroup();

            // Initialize map
            if (eventLocations.length > 0) {
                const firstEvent = eventLocations[0];
                map = L.map('map').setView([firstEvent.lat, firstEvent.lng], 15);
            } else {
                map = L.map('map').setView([48.8566, 2.3522], 10);
            }

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Fit map to show all markers if no specific event is selected
            if (eventLocations.length > 0 && !selectedEventId) {
                const bounds = L.latLngBounds(eventLocations.map(e => [e.lat, e.lng]));
                if (clientCoords) {
                    bounds.extend([clientCoords.lat, clientCoords.lng]);
                }
                map.fitBounds(bounds, { padding: [50, 50] });
            }

            // Add event markers to cluster group
            eventLocations.forEach(event => {
                const marker = L.marker([event.lat, event.lng]);
                marker.bindPopup(`<b>${event.title}</b><br>${event.location}`);
                clusterGroup.addLayer(marker);
                markers[event.id] = marker;

                marker.on('click', function() {
                    map.setView([event.lat, event.lng], 17);
                    document.querySelectorAll('.event-item').forEach(item => item.classList.remove('active'));
                    document.querySelector(`.event-item[data-id="${event.id}"]`).classList.add('active');
                });

                if (selectedEventId && event.id === selectedEventId) {
                    marker.openPopup();
                    map.setView([event.lat, event.lng], 17);
                }
            });
            map.addLayer(clusterGroup);

            // Calculate distance using Haversine formula (in kilometers)
            function calculateDistance(lat1, lng1, lat2, lng2) {
                const R = 6371; // Earth's radius in km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLng / 2) * Math.sin(dLng / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                const distance = R * c;
                return distance.toFixed(2);
            }

            // Update distances in event list
            function updateDistances(clientLat, clientLng) {
                eventLocations.forEach(event => {
                    const distance = calculateDistance(clientLat, clientLng, event.lat, event.lng);
                    const distanceElement = document.querySelector(`.distance[data-id="${event.id}"]`);
                    if (distanceElement) {
                        distanceElement.textContent = `Distance: ${distance} km`;
                    }
                });
            }

            // Add client marker if coordinates exist
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
                clientMarker.bindPopup('<b>Votre localisation</b>').openPopup();
                map.setView([clientCoords.lat, clientCoords.lng], 17);
                updateDistances(clientCoords.lat, clientCoords.lng);
            }

            // Event list click handler
            document.querySelectorAll('.event-item').forEach(item => {
                item.addEventListener('click', function() {
                    const eventId = parseInt(this.dataset.id);
                    const event = eventLocations.find(e => e.id === eventId);
                    if (event) {
                        map.setView([event.lat, event.lng], 17);
                        const marker = markers[eventId];
                        if (marker) {
                            marker.openPopup();
                        }
                        document.querySelectorAll('.event-item').forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                    }
                });

                // Keyboard support
                item.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.click();
                    }
                });
            });

            // Handle address search
            document.getElementById('addressSearchForm').addEventListener('submit', function(e) {
                e.preventDefault();
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
                                    map.removeLayer(clientMarker);
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
                                map.setView([lat, lon], 17);
                                updateDistances(lat, lon);
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

            // Handle geolocation
            document.getElementById('useCurrentLocation').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (clientMarker) {
                            map.removeLayer(clientMarker);
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
                        map.setView([lat, lng], 17);
                        updateDistances(lat, lng);
                    }, function(error) {
                        alert('Impossible d\'obtenir votre position : ' + error.message);
                    });
                } else {
                    alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                }
            });
        });
    </script>
</body>
</html>