<?php require __DIR__ . '/commun/header.php'; ?>

<h1>Installation de HD Transit</h1>

<?php if ($succes): ?>
    <p>Le compte administrateur a bien été créé ✅.
       Vous pouvez maintenant vous <a href="agent/connexion.php">connecter à l'espace agent</a>.</p>
<?php elseif ($installationTerminee): ?>
    <p>L'installation est déjà terminée : un compte administrateur existe.</p>
    <p><a href="agent/connexion.php">Aller à la connexion de l'espace agent</a></p>
<?php else: ?>

    <p>Créez le premier compte <strong>administrateur</strong>.
       Cette page ne servira qu'une seule fois.</p>

    <?php if (!empty($erreurs)): ?>
        <ul>
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="installation.php">
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
            <label for="identifiant">Identifiant de connexion *</label><br>
            <input type="text" id="identifiant" name="identifiant" maxlength="50" required
                   value="<?= htmlspecialchars($identifiant) ?>">
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
            <label for="id_agence">Agence *</label><br>
            <select id="id_agence" name="id_agence" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($agences as $a): ?>
                    <option value="<?= $a['id_agence'] ?>"
                        <?= $idAgence === (int) $a['id_agence'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <button type="submit">Créer l'administrateur</button>
        </p>
    </form>

<?php endif; ?>

<?php require __DIR__ . '/commun/footer.php'; ?>
