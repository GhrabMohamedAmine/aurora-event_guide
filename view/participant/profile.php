<?php require_once __DIR__.'/../../templates/header.php'; ?>

<div class="content">
    <div class="content-header">
        <h2><i class="fas fa-user-edit"></i> Mon Profil</h2>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST" class="user-form">
        <div class="form-group">
            <label>CIN</label>
            <input type="text" value="<?= htmlspecialchars($user['cin']) ?>" disabled>
        </div>
        
        <div class="form-group">
            <label>ID User</label>
            <input type="text" value="<?= htmlspecialchars($user['id_user']) ?>" disabled>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom*</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom*</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>">
        </div>
        
        <div class="form-group">
            <label for="mot_de_pass">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="mot_de_pass">
        </div>
        
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once __DIR__.'/../../templates/footer.php'; ?>