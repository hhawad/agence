<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Bonjour <?= htmlspecialchars($prenom) ?> !</h1>

<p>Vous êtes connecté(e) à l'espace agent
   <?= $estAdmin ? '(administrateur)' : '(employé)' ?>.</p>
<p>Votre agence : <strong><?= htmlspecialchars($agence['nom']) ?></strong></p>

<p>Bientôt ici : la recherche d'un colis, la pesée, la validation et le changement de statut.</p>

<?php require __DIR__ . '/../commun/footer.php'; ?>
