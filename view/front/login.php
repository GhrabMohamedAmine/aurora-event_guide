<?php
require_once __DIR__ . '/../../config.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user']) && isset($_SESSION['user']['id_user'])) {
    header('Location: afficher.php');
    exit;
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // Authenticate user
    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id_user, email, password, nom, prenom, telephone FROM user WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Successful login: Set session data
                $_SESSION['user'] = [
                    'id_user' => $user['id_user'],
                    'email' => $user['email'],
                    'nom' => $user['nom'] ?? '',
                    'prenom' => $user['prenom'] ?? '',
                    'telephone' => $user['telephone'] ?? '',
                ];
                // Redirect to the events page
                header('Location: afficher.php');
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Retrieve errors for display
$login_errors = $errors;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aurora Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #602299;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .login-header {
            background-color: #301934;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
            text-align: center;
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
    <div class="login-card">
        <div class="login-header">
            <h2>Login</h2>
            <p class="mb-0">Sign in to Aurora Event</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($login_errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($login_errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div id="email-error" class="error-message"></div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div id="password-error" class="error-message"></div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-purple btn-lg">Login</button>
                </div>
                
                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = {
                email: document.getElementById('email'),
                password: document.getElementById('password')
            };

            // Real-time validation
            inputs.email.addEventListener('input', validateEmail);
            inputs.password.addEventListener('input', validatePassword);

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (!validateEmail()) isValid = false;
                if (!validatePassword()) isValid = false;

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Validation functions
            function validateEmail() {
                const value = inputs.email.value.trim();
                const errorElement = document.getElementById('email-error');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (value === '') {
                    showError(inputs.email, errorElement, 'Email is required');
                    return false;
                } else if (!emailPattern.test(value)) {
                    showError(inputs.email, errorElement, 'Invalid email format');
                    return false;
                } else {
                    clearError(inputs.email, errorElement);
                    return true;
                }
            }

            function validatePassword() {
                const value = inputs.password.value.trim();
                const errorElement = document.getElementById('password-error');

                if (value === '') {
                    showError(inputs.password, errorElement, 'Password is required');
                    return false;
                } else {
                    clearError(inputs.password, errorElement);
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
        });
    </script>
</body>
</html>