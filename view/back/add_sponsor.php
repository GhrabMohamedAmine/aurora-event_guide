<?php
require_once '../../controller/SponsorController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SponsorController();
    if ($controller->add(
        $_POST['cin'],
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
<html>
<head>
    <title>Ajouter un Sponsor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { padding: 20px; font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #602299;
            outline: none;
        }
        .btn { padding: 8px 15px; font-size: 14px; font-weight: bold; border: none; border-radius: 3px; cursor: pointer; margin: 2px; }
        .btn-primary { background-color: #602299; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Ajouter un Sponsor</h2>
        <form method="POST" onsubmit="return validateForm(event)">
            <div class="form-group">
                <label for="cin">CIN</label>
                <input type="text" id="cin" name="cin">
            </div>
            
            <div class="form-group">
                <label for="entreprise">Entreprise</label>
                <input type="text" id="entreprise" name="entreprise">
            </div>
            
            <div class="form-group">
                <label for="mail">Email</label>
                <input type="text" id="mail" name="mail">
            </div>
            
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
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
        if (cin.length !== 8) {
            alert("Le numéro de cin doit contenir 8 caractères");
            return false;
        }
        event.target.submit();
        return true;
    }
    </script>
</body>
</html>
