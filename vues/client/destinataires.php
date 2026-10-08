<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Mon carnet de destinataires</h1>

<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
    <ul>
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Mes destinataires</h2>

<?php if (empty($destinataires)): ?>
    <p>Vous n'avez encore aucun destinataire.</p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Téléphone</th>
            <th>Ville</th>
            <th>Pays</th>
            <th>Action</th>
        </tr>
        <?php foreach ($destinataires as $destinataire): ?>
            <tr>
                <td><?= htmlspecialchars($destinataire['nom']) ?></td>
                <td><?= htmlspecialchars($destinataire['prenom']) ?></td>
                <td><?= htmlspecialchars($destinataire['telephone']) ?></td>
                <td><?= htmlspecialchars($destinataire['ville']) ?></td>
                <td><?= htmlspecialchars($destinataire['pays']) ?></td>
                <td>
                    <form method="post" action="destinataires.php"
                          onsubmit="return confirm('Supprimer ce destinataire ?');">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id_destinataire" value="<?= $destinataire['id_destinataire'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h2>Ajouter un destinataire</h2>

<form method="post" action="destinataires.php">
    <input type="hidden" name="action" value="ajouter">
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
        <label for="telephone">Téléphone *</label><br>
        <input type="tel" id="telephone" name="telephone" maxlength="20" required
               value="<?= htmlspecialchars($telephone) ?>">
    </p>
    <p>
        <label for="ville">Ville *</label><br>
        <input type="text" id="ville" name="ville" maxlength="100" required
               value="<?= htmlspecialchars($ville) ?>">
    </p>
    <p>
        <label for="pays">Pays *</label><br>
        <select id="pays" name="pays" required>
            <?php foreach ($paysAutorises as $paysPossible): ?>
                <option value="<?= $paysPossible ?>" <?= $pays === $paysPossible ? 'selected' : '' ?>>
                    <?= $paysPossible ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <button type="submit">Ajouter</button>
    </p>
</form>

<p><a href="accueil.php">Retour à mon espace</a></p>

<?php require __DIR__ . '/../commun/footer.php'; ?>
