<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';

session_start();

// Définir l'URL de base
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/evenement';

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$event = $event_id ? Event::getById($event_id) : null;

// Récupérer tous les événements pour la liste déroulante
$allEvents = Event::getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $data = [
        'id_event' => filter_input(INPUT_POST, 'id_event', FILTER_VALIDATE_INT),
        'nom' => trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING)),
        'telephone' => trim(filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING)),
        'nombre_places' => filter_input(INPUT_POST, 'nombre_places', FILTER_VALIDATE_INT),
        'categorie' => trim(filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING)),
        'mode_paiement' => trim(filter_input(INPUT_POST, 'mode_paiement', FILTER_SANITIZE_STRING)),
    ];

    if (!$data['id_event'] || $data['id_event'] <= 0) {
        $errors[] = 'Événement invalide sélectionné.';
    }
    if (empty($data['nom'])) {
        $errors[] = 'Le nom est requis.';
    }
    if (empty($data['telephone']) || !preg_match('/^[0-9]{8}$/', $data['telephone'])) {
        $errors[] = 'Veuillez entrer un numéro de téléphone valide (8 chiffres).';
    }
    if (!$data['nombre_places'] || $data['nombre_places'] <= 0) {
        $errors[] = 'Le nombre de places doit être un nombre positif.';
    }
    if (empty($data['categorie']) || !in_array($data['categorie'], ['VIP', 'Standard', 'Economy'])) {
        $errors[] = 'Veuillez sélectionner une catégorie valide.';
    }
    if (empty($data['mode_paiement']) || !in_array($data['mode_paiement'], ['Credit Card', 'Cash', 'Mobile Payment'])) {
        $errors[] = 'Veuillez sélectionner un mode de paiement valide.';
    }

    if (empty($errors)) {
        $reservation = new Reservation($data);
        if ($reservation->create()) {
            $_SESSION['success'] = 'Réservation créée avec succès !';
            header('Location: afficher.php');
            exit;
        } else {
            $errors[] = 'Échec de la création de la réservation. Veuillez réessayer.';
        }
    }

    $_SESSION['error'] = implode('<br>', $errors);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aurora Event - Réservation</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #301934; }
        .reservation-form-container { 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 30px; 
            background-color: #fff; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
        }
        .reservation-form-container h2 { 
            font-size: 2rem; 
            font-weight: 700; 
            color: #602299; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { 
            font-weight: 600; 
            color: #602299; 
            margin-bottom: 8px; 
            display: block; 
        }
        .form-control { 
            border-radius: 10px; 
            border: 1px solid #ced4da; 
            padding: 10px; 
            font-size: 1rem; 
        }
        .form-control:focus { 
            border-color: #602299; 
            box-shadow: 0 0 5px rgba(96, 34, 153, 0.3); 
        }
        .btn-submit { 
            background-color: #602299; 
            color: white; 
            border: none; 
            padding: 12px 30px; 
            border-radius: 30px; 
            font-weight: 600; 
            transition: all 0.3s; 
            width: 100%; 
            cursor: pointer; 
            margin-bottom: 15px;
        }
        .btn-submit:hover { 
            background-color: #4a1a7a; 
            transform: translateY(-3px); 
            box-shadow: 0 5px 15px rgba(96, 34, 153, 0.4); 
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        .btn-cancel:hover {
            background-color: #bb2d3b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
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
        .error-input {
            border-color: #dc3545 !important;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
        }
        .button-group {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="reservation-form-container">
        <h2>Réservation pour <?php echo $event ? htmlspecialchars($event->getTitre()) : 'Événement'; ?></h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form id="reservationForm" action="reserve.php" method="POST">
            <div class="form-group">
                <label for="id_event">Événement</label>
                <select class="form-control" id="id_event" name="id_event" required>
                    <option value="">-- Sélectionnez un événement --</option>
                    <?php foreach ($allEvents as $evt): ?>
                        <option value="<?= $evt->getId() ?>" <?= $event_id == $evt->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($evt->getTitre()) ?> (ID: <?= $evt->getId() ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="eventError" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
                <div id="nomError" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="telephone">Numéro de téléphone</label>
                <input type="text" class="form-control" id="telephone" name="telephone" required>
                <div id="telephoneError" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="nombre_places">Nombre de places</label>
                <input type="number" class="form-control" id="nombre_places" name="nombre_places" min="1" required>
                <div id="nombrePlacesError" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie</label>
                <select class="form-control" id="categorie" name="categorie" required>
                    <option value="">-- Sélectionnez une catégorie --</option>
                    <option value="VIP">VIP</option>
                    <option value="Standard">Standard</option>
                    <option value="Economy">Economy</option>
                </select>
                <div id="categorieError" class="error-message"></div>
            </div>
            
            <div class="form-group">
                <label for="mode_paiement">Mode de paiement</label>
                <select class="form-control" id="mode_paiement" name="mode_paiement" required>
                    <option value="">-- Sélectionnez un mode de paiement --</option>
                    <option value="Credit Card">Carte de crédit</option>
                    <option value="Cash">Espèces</option>
                    <option value="Mobile Payment">Paiement mobile</option>
                </select>
                <div id="modePaiementError" class="error-message"></div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">Confirmer la réservation</button>
                <a href="<?php echo $base_url; ?>/view/back/modify.php?id=<?php echo $event_id; ?>" class="btn-cancel">Modifier</a>
            </div>
        </form>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reservationForm');
            const inputs = {
                id_event: document.getElementById('id_event'),
                nom: document.getElementById('nom'),
                telephone: document.getElementById('telephone'),
                nombre_places: document.getElementById('nombre_places'),
                categorie: document.getElementById('categorie'),
                mode_paiement: document.getElementById('mode_paiement')
            };

            // Gestion du changement d'événement
            inputs.id_event.addEventListener('change', function() {
                const selectedEventId = this.value;
                if (selectedEventId) {
                    window.location.href = `reserve.php?id=${selectedEventId}`;
                }
            });

            // Validation en temps réel
            inputs.nom.addEventListener('input', validateNom);
            inputs.telephone.addEventListener('input', validateTelephone);
            inputs.nombre_places.addEventListener('input', validateNombrePlaces);
            inputs.categorie.addEventListener('change', validateCategorie);
            inputs.mode_paiement.addEventListener('change', validateModePaiement);

            // Validation à la soumission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (!validateEvent()) isValid = false;
                if (!validateNom()) isValid = false;
                if (!validateTelephone()) isValid = false;
                if (!validateNombrePlaces()) isValid = false;
                if (!validateCategorie()) isValid = false;
                if (!validateModePaiement()) isValid = false;
                
                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Fonctions de validation
            function validateEvent() {
                const value = inputs.id_event.value;
                const errorElement = document.getElementById('eventError');
                
                if (value === '') {
                    showError(inputs.id_event, errorElement, 'Veuillez sélectionner un événement');
                    return false;
                } else {
                    clearError(inputs.id_event, errorElement);
                    return true;
                }
            }

            function validateNom() {
                const value = inputs.nom.value.trim();
                const errorElement = document.getElementById('nomError');
                
                if (value === '') {
                    showError(inputs.nom, errorElement, 'Le nom est requis');
                    return false;
                } else if (value.length < 3) {
                    showError(inputs.nom, errorElement, 'Le nom doit contenir au moins 3 caractères');
                    return false;
                } else {
                    clearError(inputs.nom, errorElement);
                    return true;
                }
            }

            function validateTelephone() {
                const value = inputs.telephone.value.trim();
                const errorElement = document.getElementById('telephoneError');
                const phoneRegex = /^[0-9]{8}$/;
                
                if (value === '') {
                    showError(inputs.telephone, errorElement, 'Le numéro de téléphone est requis');
                    return false;
                } else if (!phoneRegex.test(value)) {
                    showError(inputs.telephone, errorElement, 'Veuillez entrer un numéro valide (8 chiffres)');
                    return false;
                } else {
                    clearError(inputs.telephone, errorElement);
                    return true;
                }
            }

            function validateNombrePlaces() {
                const value = inputs.nombre_places.value;
                const errorElement = document.getElementById('nombrePlacesError');
                
                if (value === '' || isNaN(value)) {
                    showError(inputs.nombre_places, errorElement, 'Veuillez entrer un nombre');
                    return false;
                } else if (parseInt(value) <= 0) {
                    showError(inputs.nombre_places, errorElement, 'Le nombre de places doit être positif');
                    return false;
                } else {
                    clearError(inputs.nombre_places, errorElement);
                    return true;
                }
            }

            function validateCategorie() {
                const value = inputs.categorie.value;
                const errorElement = document.getElementById('categorieError');
                
                if (value === '') {
                    showError(inputs.categorie, errorElement, 'Veuillez sélectionner une catégorie');
                    return false;
                } else {
                    clearError(inputs.categorie, errorElement);
                    return true;
                }
            }

            function validateModePaiement() {
                const value = inputs.mode_paiement.value;
                const errorElement = document.getElementById('modePaiementError');
                
                if (value === '') {
                    showError(inputs.mode_paiement, errorElement, 'Veuillez sélectionner un mode de paiement');
                    return false;
                } else {
                    clearError(inputs.mode_paiement, errorElement);
                    return true;
                }
            }

            // Fonctions utilitaires
            function showError(input, errorElement, message) {
                input.classList.add('error-input');
                errorElement.textContent = message;
            }

            function clearError(input, errorElement) {
                input.classList.remove('error-input');
                errorElement.textContent = '';
            }

            // Formatage du téléphone
            inputs.telephone.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }
            });
        });
    </script>
</body>
</html>