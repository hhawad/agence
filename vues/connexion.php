<?php require __DIR__ . '/commun/header.php'; ?>

<h1>Se connecter</h1>

<?php if ($erreur !== ''): ?>
    <p><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post" action="connexion.php">
    <p>
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($email) ?>">
    </p>
    <p>
        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
    </p>
    <p>
        <button type="submit">Se connecter</button>
    </p>
</form>

<p>Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>

<?php require __DIR__ . '/commun/footer.php'; ?>
