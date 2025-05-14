<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../controller/SponsorController.php';

$controller = new SponsorController();

if (!isset($_GET['id'])) {
    header('Location: front.php');
    exit();
}

$id = $_GET['id'];
$sponsor = $controller->getSponsorById($id);

if (!$sponsor) {
    header('Location: front.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle photo upload
        $photo = $sponsor->getPhoto(); // Keep current photo by default
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/sponsors/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique name for the file
            $fileName = uniqid('sponsor_') . '_' . basename($_FILES['photo']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            // Move uploaded file to target directory
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                // If there was a previous photo, we could delete it here
                // if ($sponsor->getPhoto()) {
                //     unlink(__DIR__ . '/../../' . $sponsor->getPhoto());
                // }
                
                $photo = 'uploads/sponsors/' . $fileName;
            } else {
                $error = "Erreur lors de l'upload de l'image";
            }
        }
        
        $result = $controller->updateFront(
            $id,
            $_POST['nom_sponsor'],
            $_POST['entreprise'],
            $_POST['mail'],
            $_POST['telephone'],
            $photo
        );

        if ($result) {
            header('Location: front.php?update_success=1');
            exit();
        } else {
            $error = "Erreur lors de la modification du sponsor";
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
    <title>Modifier un Sponsor - Aroura event</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="js/validation.js" defer></script>
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
        
        .form-group input[type="file"] {
            padding: 8px;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        
        .current-photo {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
            display: block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        .file-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
            display: none;
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
            <h1><i class="fas fa-edit"></i> Modifier un Sponsor</h1>
            <p>Modifiez les informations du sponsor ci-dessous</p>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nom_sponsor"><i class="fas fa-user"></i> Nom du sponsor</label>
                    <input type="text" id="nom_sponsor" name="nom_sponsor" value="<?php echo htmlspecialchars($sponsor->getNomSponsor()); ?>" required>
                </div>

                <div class="form-group">
                    <label for="entreprise"><i class="fas fa-building"></i> Nom de l'entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" value="<?php echo htmlspecialchars($sponsor->getEntreprise()); ?>" required>
                </div>

                <div class="form-group">
                    <label for="mail"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="mail" name="mail" value="<?php echo htmlspecialchars($sponsor->getMail()); ?>" required>
                </div>

                <div class="form-group">
                    <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($sponsor->getTelephone()); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="photo"><i class="fas fa-image"></i> Photo (Logo ou Représentant)</label>
                    <div class="file-upload-wrapper">
                        <?php if ($sponsor->getPhoto()): ?>
                            <p>Photo actuelle:</p>
                            <img src="../../<?php echo htmlspecialchars($sponsor->getPhoto()); ?>" alt="Photo actuelle" class="current-photo">
                        <?php else: ?>
                            <p>Aucune photo actuelle</p>
                        <?php endif; ?>
                        
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <img id="photoPreview" class="file-preview" src="#" alt="Aperçu de l'image">
                    </div>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-purple">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="front.php" class="btn btn-light">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photoPreview');

            // Show image preview when a file is selected
            photoInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        photoPreview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
</body>
</html>
