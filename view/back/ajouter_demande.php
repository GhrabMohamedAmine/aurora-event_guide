<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../controller/DemandeSponsoringController.php';

$controller = new DemandeSponsoringController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $controller->add(
            $_POST['id_sponsor'],
            $_POST['id_organisateur'],
            $_POST['montant'],
            $_POST['idevent']
        );
        
        if ($result) {
            header('Location: offre.php');
            exit;
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
    <title>Ajouter une Demande de Sponsoring</title>
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
            <li><i class="fas fa-home"></i> Accueil</li>
            <li><i class="fas fa-calendar-alt"></i> Événements</li>
            <li><i class="fas fa-users"></i> Utilisateurs</li>
            <li><i class="fas fa-envelope"></i> Messages</li>
            <li><i class="fas fa-cog"></i> Paramètres</li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Ajouter une Demande de Sponsoring</h2>
            </div>
            <div class="nav-links">
                <a href="offre.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" id="demandeForm" onsubmit="return validateForm(event)">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>ID Sponsor</label>
                    <input type="text" name="id_sponsor" id="id_sponsor" class="form-control">
                </div>
                <div class="form-group">
                    <label>ID Organisateur</label>
                    <input type="text" name="id_organisateur" id="id_organisateur" class="form-control">
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="text" name="montant" id="montant" class="form-control">
                </div>
                <div class="form-group">
                    <label>ID Événement</label>
                    <input type="text" name="idevent" id="idevent" class="form-control">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn btn-edit">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="offre.php" class="btn btn-delete">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
    function validateForm(event) {
        event.preventDefault();
        
        let errors = [];
        const id_sponsor = document.getElementById('id_sponsor').value.trim();
        const id_organisateur = document.getElementById('id_organisateur').value.trim();
        const montant = document.getElementById('montant').value.trim();
        const idevent = document.getElementById('idevent').value.trim();
        
        if (!id_sponsor) errors.push("L'ID du sponsor est requis");
        if (!id_organisateur) errors.push("L'ID de l'organisateur est requis");
        if (!montant) errors.push("Le montant est requis");
        if (!idevent) errors.push("L'ID de l'événement est requis");
        if (isNaN(montant)) {
            errors.push("Le montant doit être un nombre");
        }
        
        if (errors.length > 0) {
            alert(errors.join('\n'));
            return false;
        }
        
        document.getElementById('demandeForm').submit();
        return true;
    }
    </script>
</body>
</html>
