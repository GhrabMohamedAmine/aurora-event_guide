<?php
require_once '../../controller/SponsorController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SponsorController();
    
    // Handle photo upload
    $id = $_POST['id_sponsor'];
    $currentSponsor = $controller->getSponsorById($id);
    $photo = $currentSponsor->getPhoto(); // Keep current photo by default
    
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
            // if ($currentSponsor->getPhoto()) {
            //     unlink(__DIR__ . '/../../' . $currentSponsor->getPhoto());
            // }
            
            $photo = 'uploads/sponsors/' . $fileName;
        } else {
            $error = "Erreur lors de l'upload de l'image";
        }
    }
    
    if ($controller->updateFront(
        $_POST['id_sponsor'],
        $_POST['nom_sponsor'],
        $_POST['entreprise'],
        $_POST['mail'],
        $_POST['telephone'],
        $photo
    )) {
        header('Location: sponsoring.php');
        exit();
    }
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: sponsoring.php');
    exit;
}
$sponsor = (new SponsorController())->getSponsorById($id);
if (!$sponsor) {
    header('Location: sponsoring.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier un Sponsor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            padding: 40px; 
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #602299;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #602299;
            outline: none;
            box-shadow: 0 0 0 2px rgba(96,34,153,0.2);
        }
        .btn { 
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary { 
            background-color: #602299; 
            color: white; 
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .buttons-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        
        .current-photo {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .file-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier un Sponsor</h2>
        <form id="sponsorForm" method="post" onsubmit="return validateForm(event)" enctype="multipart/form-data">
            <input type="hidden" name="id_sponsor" value="<?= $sponsor->getIdSponsor() ?>">
            <div class="form-group">
                <label>Nom du Sponsor</label>
                <input type="text" id="nom_sponsor" name="nom_sponsor" class="form-control" value="<?= $sponsor->getNomSponsor() ?>">
            </div>
            <div class="form-group">
                <label>Entreprise</label>
                <input type="text" id="entreprise" name="entreprise" class="form-control" value="<?= $sponsor->getEntreprise() ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" id="mail" name="mail" class="form-control" value="<?= $sponsor->getMail() ?>">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-control" value="<?= $sponsor->getTelephone() ?>">
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Photo (Logo ou Représentant)</label>
                <div class="file-upload-wrapper">
                    <?php if ($sponsor->getPhoto()): ?>
                        <p>Photo actuelle:</p>
                        <img src="../../<?php echo htmlspecialchars($sponsor->getPhoto()); ?>" alt="Photo actuelle" class="current-photo">
                    <?php else: ?>
                        <p>Aucune photo actuelle</p>
                    <?php endif; ?>
                    
                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control">
                    <img id="photoPreview" class="file-preview" src="#" alt="Aperçu de l'image">
                </div>
            </div>
            <div class="buttons-container">
                <a href="sponsoring.php" class="btn btn-danger">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
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
    
    function validateForm(event) {
        event.preventDefault();
        
        const nom_sponsor = document.getElementById('nom_sponsor').value.trim();
        const entreprise = document.getElementById('entreprise').value.trim();
        const mail = document.getElementById('mail').value.trim();
        const telephone = document.getElementById('telephone').value.trim();

        if (!nom_sponsor || !entreprise || !mail || !telephone) {
            alert("Tous les champs sont obligatoires");
            return false;
        }

        if (!mail.includes('@') || !mail.includes('.')) {
            alert("Format d'email invalide");
            return false;
        }

        if (telephone.length !== 8) {
            alert("Le numéro de téléphone doit contenir 8 caractères");
            return false;
        }

        document.getElementById('sponsorForm').submit();
        return true;
    }
    </script>
</body>
</html>
