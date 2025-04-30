<?php
require_once '../../controller/SponsorController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SponsorController();
    if ($controller->add(
        $_POST['nom_sponsor'],
        $_POST['entreprise'],
        $_POST['mail'],
        $_POST['telephone']
    )) {
        header('Location: sponsoring.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Sponsor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #381d51;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-edit {
            background-color: #ffc107;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
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
            <li>
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
            <li class="active">
                <i class="fas fa-handshake"></i>
                <a href="Sponsoring.php" style="color: inherit; text-decoration: none;">
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

    <main class="main-content">
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Ajouter un Sponsor</h2>
            </div>
            <div class="nav-links">
                <a href="sponsoring.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" id="sponsorForm">
                <div class="form-group">
                    <label for="nom_sponsor">Nom du Sponsor</label>
                    <input type="text" id="nom_sponsor" name="nom_sponsor" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="entreprise">Entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="mail">Email</label>
                    <input type="text" id="mail" name="mail" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" class="form-control">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="sponsoring.php" class="btn btn-delete">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Ajouter du style pour la validation
    const style = document.createElement('style');
    style.textContent = `
        .form-control.validated {
            transition: all 0.3s ease;
        }
        .form-group {
            position: relative;
        }
        .validation-error {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
            outline: none;
        }
        .form-control.invalid {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        .form-control.valid {
            border-color: #28a745;
            background-color: #f8fff8;
        }
    `;
    document.head.appendChild(style);

    // Fonction pour valider un champ
    function validateField(field, rules) {
        const value = field.value.trim();
        let error = null;

        // Vérification si le champ est requis
        if (rules.required && !value) {
            error = rules.requiredMessage || "Ce champ est requis";
        } 
        // Vérification de format avec regex
        else if (value && rules.pattern && !rules.pattern.test(value)) {
            error = rules.patternMessage || "Format invalide";
        }
        // Vérification de longueur minimale
        else if (value && rules.minLength && value.length < rules.minLength) {
            error = rules.minLengthMessage || `Ce champ doit contenir au moins ${rules.minLength} caractères`;
        }
        // Vérification de longueur maximale
        else if (value && rules.maxLength && value.length > rules.maxLength) {
            error = rules.maxLengthMessage || `Ce champ doit contenir au maximum ${rules.maxLength} caractères`;
        }

        // Récupérer ou créer le conteneur d'erreur
        let errorContainer = field.nextElementSibling;
        if (!errorContainer || !errorContainer.classList.contains('validation-error')) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'validation-error';
            field.parentNode.insertBefore(errorContainer, field.nextSibling);
        }

        // Ajouter ou supprimer le message d'erreur
        if (error) {
            errorContainer.textContent = error;
            errorContainer.style.opacity = '1';
            errorContainer.style.height = 'auto';
            field.classList.add('invalid');
            field.classList.remove('valid');
            return false;
        } else {
            errorContainer.style.opacity = '0';
            errorContainer.style.height = '0';
            field.classList.remove('invalid');
            field.classList.add('valid');
            return true;
        }
    }

    // Définir les règles de validation pour chaque champ
    const validationRules = {
        nom_sponsor: {
            required: true,
            requiredMessage: "Le nom du sponsor est requis",
            minLength: 2,
            minLengthMessage: "Le nom doit contenir au moins 2 caractères"
        },
        entreprise: {
            required: true,
            requiredMessage: "Le nom de l'entreprise est requis",
            minLength: 2,
            minLengthMessage: "Le nom de l'entreprise doit contenir au moins 2 caractères"
        },
        mail: {
            required: true,
            requiredMessage: "L'adresse email est requise",
            pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/,
            patternMessage: "Veuillez entrer une adresse email valide"
        },
        telephone: {
            required: true,
            requiredMessage: "Le numéro de téléphone est requis",
            pattern: /^\d{8}$/,
            patternMessage: "Le numéro de téléphone doit contenir exactement 8 chiffres"
        }
    };

    // Initialiser la validation en temps réel
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('sponsorForm');
        const fields = form.querySelectorAll('input');
        
        // Configurer la validation en temps réel pour chaque champ
        fields.forEach(field => {
            const rules = validationRules[field.id];
            if (rules) {
                field.classList.add('validated');
                
                // Validation à la perte de focus
                field.addEventListener('blur', function() {
                    validateField(field, rules);
                    field.dataset.touched = 'true';
                });
                
                // Validation pendant la saisie après le premier blur
                field.addEventListener('input', function() {
                    if (field.dataset.touched === 'true') {
                        validateField(field, rules);
                    }
                });
            }
        });
        
        // Validation du formulaire à la soumission
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            let formIsValid = true;
            
            // Valider tous les champs
            fields.forEach(field => {
                const rules = validationRules[field.id];
                if (rules) {
                    const isFieldValid = validateField(field, rules);
                    formIsValid = formIsValid && isFieldValid;
                }
            });
            
            // Soumettre le formulaire si tout est valide
            if (formIsValid) {
                this.submit();
            } else {
                // Faire défiler jusqu'au premier champ invalide
                const firstInvalidField = form.querySelector('.form-control.invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
    </script>
</body>
</html>
