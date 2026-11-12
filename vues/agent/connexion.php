<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Espace agent : connexion</h1>

<?php if ($erreur !== ''): ?>
    <p><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post" action="connexion.php">
    <p>
        <label for="identifiant">Identifiant</label><br>
        <input type="text" id="identifiant" name="identifiant" required
               value="<?= htmlspecialchars($identifiant) ?>">
    </p>
    <p>
        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
    </p>
    <p>
        <button type="submit">Se connecter</button>
    </p>
</form>

<?php require __DIR__ . '/../commun/footer.php'; ?>
