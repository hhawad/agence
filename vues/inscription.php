<?php require __DIR__ . '/commun/header.php'; ?>

<h1>Créer un compte</h1>

<?php if ($succes): ?>
    <p>Votre compte a bien été créé. Vous pouvez maintenant vous connecter.</p>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
    <ul>
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="inscription.php">
    <p>
        <label for="nom">Nom *</label><br>
        <input type="text" id="nom" name="nom" maxlength="50" required
               value="<?= htmlspecialchars($nom) ?>">
    </p>
    <p>
        <label for="prenom">Prénom *</label><br>
        <input type="text" id="prenom" name="prenom" maxlength="50" required
               value="<?= htmlspecialchars($prenom) ?>">
    </p>
    <p>
        <label for="email">Email *</label><br>
        <input type="email" id="email" name="email" maxlength="100" required
               value="<?= htmlspecialchars($email) ?>">
    </p>
    <p>
        <label for="telephone">Téléphone *</label><br>
        <input type="tel" id="telephone" name="telephone" maxlength="20" required
               value="<?= htmlspecialchars($telephone) ?>">
    </p>
    <p>
        <label for="mot_de_passe">Mot de passe * (8 caractères minimum)</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" required>
    </p>
    <p>
        <label for="confirmation">Confirmer le mot de passe *</label><br>
        <input type="password" id="confirmation" name="confirmation" minlength="8" required>
    </p>
    <p>
        <button type="submit">Créer mon compte</button>
    </p>
</form>

<?php require __DIR__ . '/commun/footer.php'; ?>
