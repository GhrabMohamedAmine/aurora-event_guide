<?php require_once __DIR__.'/../../templates/header.php'; ?>

<div class="content">
    <div class="content-header">
        <h2><i class="fas fa-user-plus"></i> Ajouter Utilisateur</h2>
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
            <label for="cin">CIN*</label>
            <input type="number" name="cin" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom*</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom*</label>
                <input type="text" name="prenom" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="type">Type*</label>
                <select name="type" required>
                    <option value="admin">Admin</option>
                    <option value="organisator">Organisateur</option>
                    <option value="participant">Participant</option>
                </select>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" name="telephone">
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="mot_de_pass">Mot de passe*</label>
            <input type="password" name="mot_de_pass" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>

<?php require_once __DIR__.'/../../templates/footer.php'; ?>