<?php require_once __DIR__.'/../../templates/header.php'; ?>

<div class="content">
    <div class="content-header">
        <h2><i class="fas fa-users"></i> Liste des Utilisateurs</h2>
        <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau</a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>CIN</th>
                <th>ID User</th>
                <th>Nom Complet</th>
                <th>Type</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['cin']) ?></td>
                <td><?= htmlspecialchars($user['id_user']) ?></td>
                <td><?= htmlspecialchars($user['nom'].' '.$user['prenom']) ?></td>
                <td><?= htmlspecialchars($user['type']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td class="actions">
                    <a href="?action=edit&id=<?= $user['cin'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                    <a href="?action=delete&id=<?= $user['cin'] ?>" class="btn btn-delete" onclick="return confirm('Confirmer la suppression ?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__.'/../../templates/footer.php'; ?>