<?php require_once __DIR__.'/../../templates/header.php'; ?>

<div class="content">
    <div class="content-header">
        <h2><i class="fas fa-user-edit"></i> Modifier Utilisateur</h2>
        <a href="?action=list" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form class="user-form" method="POST">
        <div class="form-group">
            <label>CIN</label>
            <input type="text" value="<?= $user['cin'] ?>" disabled>
        </div>
        
        <div class="form-group">
            <label>ID User</label>
            <input type="text" value="<?= $user['id_user'] ?>" disabled>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom*</label>
                <input type="text" name="nom" value="<?= $user['nom'] ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom*</label>
                <input type="text" name="prenom" value="<?= $user['prenom'] ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="type">Type*</label>
                <select name="type" required>
                    <option value="admin" <?= $user['type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="organisator" <?= $user['type'] === 'organisator' ? 'selected' : '' ?>>Organisateur</option>
                    <option value="participant" <?= $user['type'] === 'participant' ? 'selected' : '' ?>>Participant</option>
                </select>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" name="telephone" value="<?= $user['telephone'] ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" name="email" value="<?= $user['email'] ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once __DIR__.'/../../templates/footer.php'; ?>