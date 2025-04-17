<?php
require_once '../../controller/SponsorController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new SponsorController();
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $controller->delete($_POST['id']);
        header('Location: sponsoring.php' );
        exit();
    }
}

// Get all sponsors for display
$controller = new SponsorController();
$sponsors = $controller->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Sponsors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            padding: 40px; 
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            max-width: 1200px;
            margin: auto;
        }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px; border: 1px solid #ddd; }
        .table th { background-color: #602299; color: white; }
        .btn { padding: 8px 15px; font-size: 14px; font-weight: bold; border: none; border-radius: 3px; cursor: pointer; margin: 2px; transition: opacity 0.3s; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-primary { background-color: #602299; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn:hover { opacity: 0.9; }
        .nav-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .back-btn {
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .back-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
        <h2>Liste des sponsors</h2>
        <a href="add_sponsor.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Sponsor
        </a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>CIN</th>
                <th>Entreprise</th>
                <th>Mail</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sponsors as $sponsor): ?>
            <tr>
                <td><?= htmlspecialchars($sponsor['cin']) ?></td>
                <td><?= htmlspecialchars($sponsor['entreprise']) ?></td>
                <td><?= htmlspecialchars($sponsor['mail']) ?></td>
                <td><?= htmlspecialchars($sponsor['telephone']) ?></td>
                <td>
                    <a href="modifier_sponsor.php?id=<?= $sponsor['id_sponsor'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="confirmDelete(<?= $sponsor['id_sponsor'] ?>)" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function confirmDelete(id) {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce sponsor ?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>