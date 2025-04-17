<?php
require_once '../../controller/SponsorController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SponsorController();
    if ($controller->update(
        $_POST['id_sponsor'],
        $_POST['cin'],
        $_POST['entreprise'],
        $_POST['mail'],
        $_POST['telephone']
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
$sponsor = (new SponsorController())->getById($id);
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier un Sponsor</h2>
        <form id="sponsorForm" method="post" onsubmit="return validateForm(event)">
            <input type="hidden" name="id_sponsor" value="<?= $sponsor['id_sponsor'] ?>">
            <div class="form-group">
                <label>CIN</label>
                <input type="text" id="cin" name="cin" class="form-control" value="<?= $sponsor['cin'] ?>">
            </div>
            <div class="form-group">
                <label>Entreprise</label>
                <input type="text" id="entreprise" name="entreprise" class="form-control" value="<?= $sponsor['entreprise'] ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" id="mail" name="mail" class="form-control" value="<?= $sponsor['mail'] ?>">
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-control" value="<?= $sponsor['telephone'] ?>">
            </div>
            <div class="buttons-container">
                <a href="sponsoring.php" class="btn btn-danger">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>

    <script>
    function validateForm(event) {
        event.preventDefault();
        
        const cin = document.getElementById('cin').value.trim();
        const entreprise = document.getElementById('entreprise').value.trim();
        const mail = document.getElementById('mail').value.trim();
        const telephone = document.getElementById('telephone').value.trim();

        if (!cin || !entreprise || !mail || !telephone) {
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
