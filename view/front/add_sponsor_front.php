<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../controller/SponsorController.php';

$controller = new SponsorController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $controller->createSponsor(
            $_POST['nom_sponsor'],
            $_POST['entreprise'],
            $_POST['mail'],
            $_POST['telephone']
        );

        if ($result) {
            header('Location: front.php?success=1');
            exit();
        } else {
            $error = "Erreur lors de l'ajout du sponsor";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Sponsor - Aroura event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="js/validation.js" defer></script>
    <script src="js/gemini-autofill.js" defer></script>
    <style>
        :root {
            --primary-color: #6a0dad;
            --secondary-color: #d4af37;
            --light-purple: #9c73b8;
            --dark-purple: #4b0082;
            --light-gold: #f0e6d2;
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding: 40px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-purple) 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-header h1 {
            color: var(--secondary-color);
            margin: 0;
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .form-header p {
            color: var(--light-gold);
            margin: 10px 0 0;
            font-size: 1.1rem;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-gray);
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--light-purple);
        }

        .error-message {
            background-color: #ffe6e6;
            color: #dc3545;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-purple {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-purple:hover {
            background-color: var(--dark-purple);
            transform: translateY(-2px);
        }

        .btn-light {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-light:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 0;
            }

            .form-container {
                padding: 20px;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1><i class="fas fa-user-plus"></i> Ajouter un Sponsor</h1>
            <p>Remplissez le formulaire ci-dessous pour ajouter un nouveau sponsor</p>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom_sponsor"><i class="fas fa-user"></i> Nom du Sponsor</label>
                    <input type="text" id="nom_sponsor" name="nom_sponsor" placeholder="Nom du sponsor" required>
                </div>

                <div class="form-group">
                    <label for="entreprise"><i class="fas fa-building"></i> Nom de l'entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" placeholder="Nom de l'entreprise" required>
                </div>

                <div class="form-group">
                    <label for="mail"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="mail" name="mail" placeholder="Email de contact" required>
                </div>

                <div class="form-group">
                    <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="Numéro de téléphone" required>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-purple">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="front.php" class="btn btn-light">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>