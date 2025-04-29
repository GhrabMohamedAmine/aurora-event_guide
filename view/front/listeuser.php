<?php
require_once __DIR__ . '/../../config.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT id_user, nom, prenom, email, type, mot_de_pass FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching users: " . $e->getMessage();
    error_log("Database error in listeuser.php: " . $e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Users - Aurora Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #602299; color: white; }
        .container { margin-top: 2rem; }
        .table { background-color: white; border-radius: 10px; overflow: hidden; }
        .table th, .table td { vertical-align: middle; }
        .header { background-color: #301934; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; }
        .btn-use-id { font-size: 0.875rem; }
        .password-cell { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h2>List of Users</h2>
            <p>Select a User ID to use for your reservation.</p>
        </div>

        <?php if (empty($users)): ?>
            <div class="alert alert-warning text-center">
                No users found in the database. Please contact an administrator to register a user.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID User</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id_user']) ?></td>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['type']) ?></td>
                            <td class="password-cell"><?= htmlspecialchars($user['mot_de_pass']) ?></td>
                            <td>
                                <a href="reserve.php?id_event=<?= isset($_GET['id_event']) ? htmlspecialchars($_GET['id_event']) : '' ?>&id_user=<?= htmlspecialchars($user['id_user']) ?>" class="btn btn-sm btn-primary btn-use-id">Use this ID</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="reserve.php?id_event=<?= isset($_GET['id_event']) ? htmlspecialchars($_GET['id_event']) : '' ?>" class="btn btn-outline-light">Back to Reservation</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>