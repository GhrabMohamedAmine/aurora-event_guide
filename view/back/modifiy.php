<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

session_start();

// Get the reservation ID from the URL
$reservation_id_input = filter_input(INPUT_GET, 'id_reservation', FILTER_SANITIZE_STRING);

// Validate the reservation ID
if ($reservation_id_input === null || !ctype_digit($reservation_id_input) || $reservation_id_input === '0' || ltrim($reservation_id_input, '0') === '') {
    error_log("Invalid id_reservation received: " . ($reservation_id_input ?? 'null'));
    $_SESSION['error'] = 'ID de réservation invalide. Veuillez sélectionner une réservation valide.';
    header('Location: afficher.php');
    exit;
}

$reservation_id = (int)$reservation_id_input;
$reservation = Reservation::getById($reservation_id);

if (!$reservation) {
    error_log("No reservation found for id_reservation: " . $reservation_id);
    $_SESSION['error'] = "Aucune réservation trouvée avec l'ID $reservation_id.";
    header('Location: afficher.php');
    exit;
}

// Get all events for the dropdown
$events = Event::getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $data = [
        'id_reservation' => $reservation_id,
        'id_event' => filter_input(INPUT_POST, 'id_event', FILTER_SANITIZE_STRING),
        'nom' => trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING)),
        'telephone' => trim(filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING)),
        'nombre_places' => filter_input(INPUT_POST, 'nombre_places', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]),
        'categorie' => trim(filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING)),
        'mode_paiement' => trim(filter_input(INPUT_POST, 'mode_paiement', FILTER_SANITIZE_STRING)),
    ];

    // Validate the input
    if ($data['id_event'] === null || !ctype_digit($data['id_event']) || $data['id_event'] === '0' || ltrim($data['id_event'], '0') === '') {
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

    // If no errors, update the reservation and user data
    if (empty($errors)) {
        try {
            // Update Reservation object
            $reservation->setIdEvent($data['id_event']);
            $reservation->setNombrePlaces($data['nombre_places']);
            $reservation->setCategorie($data['categorie']);
            $reservation->setModePaiement($data['mode_paiement']);

            // Update user data (nom and telephone) in the user table
            $db = getDB();
            $sql = "UPDATE users SET nom = :nom, telephone = :telephone WHERE id_user = :id_user";
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $data['nom'],
                'telephone' => $data['telephone'],
                'id_user' => $reservation->getIdUser()
            ]);

            // Update reservation in the database
            if ($reservation->update()) {
                $_SESSION['success'] = 'Réservation mise à jour avec succès !';
                header('Location: afficher.php');
                exit;
            } else {
                $errors[] = 'Échec de la mise à jour de la réservation. Veuillez réessayer.';
            }
        } catch (Exception $e) {
            $errors[] = 'Erreur : ' . $e->getMessage();
        }
    }

    $_SESSION['error'] = implode('<br>', $errors);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Réservation - Aurora Event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #602299;
            min-height: 100vh;
        }

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

        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #381d51;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            color: #381d51;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 1rem;
            width: 100%;
        }

        .form-control:focus {
            border-color: #381d51;
            box-shadow: 0 0 5px rgba(56, 29, 81, 0.3);
            outline: none;
        }

        .btn-submit {
            background-color: #28a745;
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
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
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
            background-color: #c82333;
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

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo" style="height: 40px; margin-right: 10px;">
            <h1>Aurora Event</h1>
        </div>
        <ul class="sidebar-menu">
            <li>
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.html" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li class="active">
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <span>Events</span>
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
                <i class="fas fa-ticket-alt"></i>
                <a href="#reservations" style="color: inherit; text-decoration: none;">
                    <span>Reservations</span>
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

    <main class="main-content">
        <div class="form-container">
            <h2>Modifier la Réservation</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form id="reservationForm" action="modifiy.php?id_reservation=<?= htmlspecialchars($reservation_id) ?>" method="POST">
                <div class="form-group">
                    <label for="id_event">Événement</label>
                    <select class="form-control" id="id_event" name="id_event" required>
                        <option value="">-- Sélectionnez un événement --</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= htmlspecialchars($event->getIdEvent()) ?>" <?= $reservation->getIdEvent() == $event->getIdEvent() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event->getTitre()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="idEventError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($reservation->getNom()) ?>" required>
                    <div id="nomError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="telephone">Numéro de téléphone</label>
                    <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($reservation->getTelephone()) ?>" required>
                    <div id="telephoneError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="nombre_places">Nombre de places</label>
                    <input type="number" class="form-control" id="nombre_places" name="nombre_places" min="1" value="<?= htmlspecialchars($reservation->getNombrePlaces()) ?>" required>
                    <div id="nombrePlacesError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select class="form-control" id="categorie" name="categorie" required>
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <option value="VIP" <?= $reservation->getCategorie() === 'VIP' ? 'selected' : '' ?>>VIP</option>
                        <option value="Standard" <?= $reservation->getCategorie() === 'Standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="Economy" <?= $reservation->getCategorie() === 'Economy' ? 'selected' : '' ?>>Economy</option>
                    </select>
                    <div id="categorieError" class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="mode_paiement">Mode de paiement</label>
                    <select class="form-control" id="mode_paiement" name="mode_paiement" required>
                        <option value="">-- Sélectionnez un mode de paiement --</option>
                        <option value="Credit Card" <?= $reservation->getModePaiement() === 'Credit Card' ? 'selected' : '' ?>>Carte de crédit</option>
                        <option value="Cash" <?= $reservation->getModePaiement() === 'Cash' ? 'selected' : '' ?>>Espèces</option>
                        <option value="Mobile Payment" <?= $reservation->getModePaiement() === 'Mobile Payment' ? 'selected' : '' ?>>Paiement mobile</option>
                    </select>
                    <div id="modePaiementError" class="error-message"></div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">Mettre à jour</button>
                    <a href="afficher.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

            // Validation en temps réel
            inputs.id_event.addEventListener('change', validateIdEvent);
            inputs.nom.addEventListener('input', validateNom);
            inputs.telephone.addEventListener('input', validateTelephone);
            inputs.nombre_places.addEventListener('input', validateNombrePlaces);
            inputs.categorie.addEventListener('change', validateCategorie);
            inputs.mode_paiement.addEventListener('change', validateModePaiement);

            // Validation à la soumission
            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (!validateIdEvent()) isValid = false;
                if (!validateNom()) isValid = false;
                if (!validateTelephone()) isValid = false;
                if (!validateNombrePlaces()) isValid = false;
                if (!validateCategorie()) isValid = false;
                if (!validateModePaiement()) isValid = false;

                if (!isValid) {
                    e.preventDefault();
                }
            });

            function validateIdEvent() {
                const value = inputs.id_event.value;
                const errorElement = document.getElementById('idEventError');
                if (value === '') {
                    showError(inputs.id_event, errorElement, 'Veuillez sélectionner un événement');
                    return false;
                }
                clearError(inputs.id_event, errorElement);
                return true;
            }

            function validateNom() {
                const value = inputs.nom.value.trim();
                const errorElement = document.getElementById('nomError');
                if (value === '') {
                    showError(inputs.nom, errorElement, 'Le nom est requis');
                    return false;
                }
                if (value.length < 3) {
                    showError(inputs.nom, errorElement, 'Le nom doit contenir au moins 3 caractères');
                    return false;
                }
                clearError(inputs.nom, errorElement);
                return true;
            }

            function validateTelephone() {
                const value = inputs.telephone.value.trim();
                const errorElement = document.getElementById('telephoneError');
                const phoneRegex = /^[0-9]{8}$/;
                if (value === '') {
                    showError(inputs.telephone, errorElement, 'Le numéro de téléphone est requis');
                    return false;
                }
                if (!phoneRegex.test(value)) {
                    showError(inputs.telephone, errorElement, 'Veuillez entrer un numéro valide (8 chiffres)');
                    return false;
                }
                clearError(inputs.telephone, errorElement);
                return true;
            }

            function validateNombrePlaces() {
                const value = inputs.nombre_places.value;
                const errorElement = document.getElementById('nombrePlacesError');
                if (value === '' || isNaN(value)) {
                    showError(inputs.nombre_places, errorElement, 'Veuillez entrer un nombre');
                    return false;
                }
                if (parseInt(value) <= 0) {
                    showError(inputs.nombre_places, errorElement, 'Le nombre de places doit être positif');
                    return false;
                }
                clearError(inputs.nombre_places, errorElement);
                return true;
            }

            function validateCategorie() {
                const value = inputs.categorie.value;
                const errorElement = document.getElementById('categorieError');
                if (value === '') {
                    showError(inputs.categorie, errorElement, 'Veuillez sélectionner une catégorie');
                    return false;
                }
                clearError(inputs.categorie, errorElement);
                return true;
            }

            function validateModePaiement() {
                const value = inputs.mode_paiement.value;
                const errorElement = document.getElementById('modePaiementError');
                if (value === '') {
                    showError(inputs.mode_paiement, errorElement, 'Veuillez sélectionner un mode de paiement');
                    return false;
                }
                clearError(inputs.mode_paiement, errorElement);
                return true;
            }

            function showError(input, errorElement, message) {
                input.classList.add('error-input');
                errorElement.textContent = message;
            }

            function clearError(input, errorElement) {
                input.classList.remove('error-input');
                errorElement.textContent = '';
            }

            inputs.telephone.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }
            });

            inputs.nombre_places.addEventListener('input', function(e) {
                if (this.value < 1) {
                    this.value = 1;
                }
            });
        });
    </script>
</body>
</html>