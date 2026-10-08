<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Bienvenue <?= htmlspecialchars($prenom) ?> !</h1>

<p>Vous êtes connecté(e) à votre espace client HD Transit.</p>

<ul>
    <li><a href="destinataires.php">Mon carnet de destinataires</a></li>
</ul>

<p>Bientôt ici : vos colis.</p>

<?php require __DIR__ . '/../commun/footer.php'; ?>
