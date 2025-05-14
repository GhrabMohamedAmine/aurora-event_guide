<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__.'/../../controller/DemandeSponsoringController.php';
require_once __DIR__.'/../../controller/SponsorController.php';

$controller = new DemandeSponsoringController();
$sponsorController = new SponsorController();
$sponsors = $sponsorController->getAllSponsors();

$id = $_GET['id'];
$demande = $controller->getById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sponsorController->getSponsorById($_POST['id_sponsor'])) {
        echo "<script>alert('Le sponsor sélectionné n\'existe pas!');</script>";
    } else {
        $result = $controller->update(
            $id,
            $_POST['id_sponsor'],
            $_POST['id_organisateur'],
            $_POST['montant'],
            $_POST['idevent'],
            $_POST['statut']
        );
        
        if ($result) {
            header('Location: offre.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Demande de Sponsoring</title>
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
                <h2 style="font-size: 18px; color: #381d51;">Modifier la Demande de Sponsoring</h2>
            </div>
            <div class="nav-links">
                <a href="offre.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" id="sponsorForm" onsubmit="return validateForm(event)">
                <div class="form-group">
                    <label>Id Sponsor</label>
                    <input type="text" name="id_sponsor" id="id_sponsor" class="form-control" value="<?= htmlspecialchars($demande['id_sponsor']) ?>">
                </div>
                <div class="form-group">
                    <label>Id Organisateur</label>
                    <input type="text" name="id_organisateur" id="id_organisateur" class="form-control" value="<?= htmlspecialchars($demande['id_organisateur']) ?>">
                </div>
                <div class="form-group">
                    <label>Montant</label>
                    <input type="text" name="montant" id="montant" class="form-control" value="<?= htmlspecialchars($demande['montant']) ?>">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="enattente" <?= ($demande['statut'] === 'enattente') ? 'selected' : '' ?>>En attente</option>
                        <option value="accepter" <?= ($demande['statut'] === 'accepter') ? 'selected' : '' ?>>Accepter</option>
                        <option value="refuser" <?= ($demande['statut'] === 'refuser') ? 'selected' : '' ?>>Refuser</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID Event</label>
                    <input type="text" name="idevent" id="idevent" class="form-control" value="<?= htmlspecialchars($demande['idevent']) ?>">
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
        
        const id_sponsor = document.getElementById('id_sponsor').value.trim();
        const id_organisateur = document.getElementById('id_organisateur').value.trim();
        const montant = parseFloat(document.getElementById('montant').value);
        const statut = document.getElementById('statut').value;
        const idevent = document.getElementById('idevent').value.trim();
        
        if (!id_sponsor || !id_organisateur || !montant || !statut || !idevent) {
            alert('Tous les champs sont obligatoires');
            return false;
        }

        if (montant <= 0) {
            alert('Le montant doit être supérieur à 0');
            return false;
        }

        document.getElementById('sponsorForm').submit();
        return true;
    }
    </script>
</body>
</html>