<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Bienvenue <?= htmlspecialchars($prenom) ?> !</h1>

<p>Vous êtes connecté(e) à votre espace client HD Transit.</p>

<ul>
    <li><a href="nouveau-colis.php">Envoyer un colis</a></li>
    <li><a href="mes-colis.php">Mes colis</a></li>
    <li><a href="destinataires.php">Mon carnet de destinataires</a></li>
</ul>

<?php require __DIR__ . '/../commun/footer.php'; ?>
