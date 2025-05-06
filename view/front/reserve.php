<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';
require_once __DIR__ . '/../../model/reserve.php';
require_once __DIR__ . '/../../controller/reserveC.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    $_SESSION['error'] = 'Please log in to make a reservation.';
    header('Location: afficher.php');
    exit;
}

// Validate event ID
$event_id_input = filter_input(INPUT_GET, 'id_event', FILTER_SANITIZE_STRING);
if ($event_id_input === null || $event_id_input === false || !ctype_digit($event_id_input)) {
    error_log("Invalid id_event received: " . ($event_id_input ?? 'null'));
    $_SESSION['error'] = 'Invalid event ID. Please select a valid event.';
    header('Location: afficher.php');
    exit;
}

$event_id = (int)$event_id_input;
if ($event_id <= 0) {
    error_log("Non-positive id_event received: " . $event_id);
    $_SESSION['error'] = 'Invalid event ID. The ID must be positive.';
    header('Location: afficher.php');
    exit;
}

// Fetch the event
$event = Event::getById($event_id);
if (!$event) {
    error_log("No event found for id_event: " . $event_id);
    $_SESSION['error'] = "No event found with ID $event_id.";
    header('Location: afficher.php');
    exit;
}

// Get event price
$event_price = $event->getPrix() ?? 0;

// Pre-fill form data with session data
$form_data = $_SESSION['form_data'] ?? [
    'email' => $_SESSION['user_email'],
    'nombre_places' => 1,
    'categorie' => '',
    'mode_paiement' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $data = [
        'id_event' => $event_id,
        'email' => $_SESSION['user_email'], // Use session email
        'id_user' => $_SESSION['user_id'], // Use session user ID
        'nombre_places' => filter_input(INPUT_POST, 'nombre_places', FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
                'max_range' => 20
            ]
        ]),
        'categorie' => trim(filter_input(INPUT_POST, 'categorie', FILTER_SANITIZE_STRING)),
        'mode_paiement' => trim(filter_input(INPUT_POST, 'mode_paiement', FILTER_SANITIZE_STRING)),
    ];

    // Validate data
    if (!$data['nombre_places'] || $data['nombre_places'] < 1 || $data['nombre_places'] > 20) {
        $errors[] = 'Number of places must be between 1 and 20.';
    }

    $categories_valides = ['VIP', 'Standard', 'Economy'];
    if (empty($data['categorie']) || !in_array($data['categorie'], $categories_valides)) {
        $errors[] = 'Invalid category.';
    }

    $paiements_valides = ['Credit Card', 'Cash', 'Mobile Payment'];
    if (empty($data['mode_paiement']) || !in_array($data['mode_paiement'], $paiements_valides)) {
        $errors[] = 'Invalid payment method.';
    }

    // Create reservation if no errors
    if (empty($errors)) {
        try {
            // Create Reservation object with individual parameters
            $reservation = new Reservation(
                null, // id_reservation (auto-incremented)
                $data['id_event'],
                $data['id_user'], // Use session id_user
                null, // nom (not stored in reservation table)
                null, // telephone (not stored in reservation table)
                $data['nombre_places'],
                $data['categorie'],
                $data['mode_paiement']
            );
            $reservationController = new ReservationC();
            if ($reservationController->ajouterReservation($reservation)) {
                $_SESSION['success'] = 'Reservation created successfully!';
                header('Location: afficher.php');
                exit;
            } else {
                $errors[] = 'Failed to create reservation. Please try again.';
                error_log("Failed to create reservation for event ID: $event_id, user ID: {$data['id_user']}");
            }
        } catch (Exception $e) {
            $errors[] = 'Technical error: ' . $e->getMessage();
            error_log("Reservation error: " . $e->getMessage());
        }
    }

    // Store errors for display
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $data;
        header("Location: reserve.php?id_event=$event_id");
        exit;
    }
}

// Retrieve form errors
$form_errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($event->getTitre()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #602299; }
        .reservation-card {
            max-width: 600px;
            margin: 2rem auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .event-header {
            background-color: #301934;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        .form-container {
            padding: 2rem;
            background: white;
            border-radius: 0 0 15px 15px;
        }
        .btn-purple {
            background-color: #301934;
            color: white;
        }
        .btn-purple:hover {
            background-color: #301934;
            color: white;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }
        .is-invalid + .error-message {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="reservation-card">
            <div class="event-header text-center">
                <h2><?= htmlspecialchars($event->getTitre()) ?></h2>
                <p class="mb-0"><?= htmlspecialchars($event->getDate()) ?> | Prix: <?= htmlspecialchars($event_price) ?> TND</p>
            </div>
            
            <div class="form-container">
                <?php if (!empty($form_errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($form_errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="reserve.php?id_event=<?= $event_id ?>" id="reservationForm">
                    <input type="hidden" name="id_event" value="<?= $event_id ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($_SESSION['user_email']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nombre_places" class="form-label">Number of Places</label>
                        <input type="number" class="form-control <?= in_array('Number of places must be between 1 and 20.', $form_errors) ? 'is-invalid' : '' ?>" 
                               id="nombre_places" name="nombre_places" min="1" max="20" 
                               value="<?= htmlspecialchars($form_data['nombre_places'] ?? '1') ?>" required>
                        <div id="nombre_places-error" class="error-message">Number of places must be between 1 and 20</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total" class="form-label">Total Price (TND)</label>
                        <input type="text" class="form-control" id="total" name="total" 
                               value="<?= htmlspecialchars(($form_data['nombre_places'] ?? 1) * $event_price) ?>" readonly>
                        <div id="total-error" class="error-message"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categorie" class="form-label">Category</label>
                        <select class="form-select <?= in_array('Invalid category.', $form_errors) ? 'is-invalid' : '' ?>" 
                                id="categorie" name="categorie" required>
                            <option value="">-- Select --</option>
                            <option value="VIP" <?= ($form_data['categorie'] ?? '') === 'VIP' ? 'selected' : '' ?>>VIP</option>
                            <option value="Standard" <?= ($form_data['categorie'] ?? '') === 'Standard' ? 'selected' : '' ?>>Standard</option>
                            <option value="Economy" <?= ($form_data['categorie'] ?? '') === 'Economy' ? 'selected' : '' ?>>Economy</option>
                        </select>
                        <div id="categorie-error" class="error-message">Please select a category</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="mode_paiement" class="form-label">Payment Method</label>
                        <select class="form-select <?= in_array('Invalid payment method.', $form_errors) ? 'is-invalid' : '' ?>" 
                                id="mode_paiement" name="mode_paiement" required>
                            <option value="">-- Select --</option>
                            <option value="Credit Card" <?= ($form_data['mode_paiement'] ?? '') === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="Cash" <?= ($form_data['mode_paiement'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="Mobile Payment" <?= ($form_data['mode_paiement'] ?? '') === 'Mobile Payment' ? 'selected' : '' ?>>Mobile Payment</option>
                        </select>
                        <div id="mode_paiement-error" class="error-message">Please select a payment method</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-purple btn-lg">Confirm Reservation</button>
                        <a href="afficher.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reservationForm');
            const inputs = {
                nombre_places: document.getElementById('nombre_places'),
                total: document.getElementById('total'),
                categorie: document.getElementById('categorie'),
                mode_paiement: document.getElementById('mode_paiement')
            };
            
            // Event price from PHP
            const eventPrice = <?= json_encode($event_price) ?>;

            // Real-time validation and total calculation
            inputs.nombre_places.addEventListener('input', function() {
                validateNombrePlaces();
                updateTotal();
            });
            inputs.categorie.addEventListener('change', validateCategorie);
            inputs.mode_paiement.addEventListener('change', validateModePaiement);

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (!validateNombrePlaces()) isValid = false;
                if (!validateCategorie()) isValid = false;
                if (!validateModePaiement()) isValid = false;

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Update total price
            function updateTotal() {
                const places = parseInt(inputs.nombre_places.value) || 1;
                const total = places * eventPrice;
                inputs.total.value = total.toFixed(2);
            }

            // Validation functions
            function validateNombrePlaces() {
                const value = inputs.nombre_places.value;
                const errorElement = document.getElementById('nombre_places-error');

                if (value === '' || isNaN(value)) {
                    showError(inputs.nombre_places, errorElement, 'Please enter a number');
                    return false;
                } else if (parseInt(value) < 1 || parseInt(value) > 20) {
                    showError(inputs.nombre_places, errorElement, 'Number of places must be between 1 and 20');
                    return false;
                } else {
                    clearError(inputs.nombre_places, errorElement);
                    return true;
                }
            }

            function validateCategorie() {
                const value = inputs.categorie.value;
                const errorElement = document.getElementById('categorie-error');

                if (value === '') {
                    showError(inputs.categorie, errorElement, 'Please select a category');
                    return false;
                } else {
                    clearError(inputs.categorie, errorElement);
                    return true;
                }
            }

            function validateModePaiement() {
                const value = inputs.mode_paiement.value;
                const errorElement = document.getElementById('mode_paiement-error');

                if (value === '') {
                    showError(inputs.mode_paiement, errorElement, 'Please select a payment method');
                    return false;
                } else {
                    clearError(inputs.mode_paiement, errorElement);
                    return true;
                }
            }

            // Utility functions
            function showError(input, errorElement, message) {
                input.classList.add('is-invalid');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }

            function clearError(input, errorElement) {
                input.classList.remove('is-invalid');
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }

            // Control number of places
            inputs.nombre_places.addEventListener('input', function(e) {
                if (this.value < 1) {
                    this.value = 1;
                } else if (this.value > 20) {
                    this.value = 20;
                }
                validateNombrePlaces();
                updateTotal();
            });

            // Initialize total
            updateTotal();
        });
    </script>
</body>
</html>