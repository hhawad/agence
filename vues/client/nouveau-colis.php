<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Envoyer un colis</h1>

<?php if ($colisCree !== null): ?>
    <div>
        <h2>Votre envoi a bien été créé ✅</h2>
        <p>Numéro de suivi : <strong><?= htmlspecialchars($colisCree['numero_suivi']) ?></strong></p>
        <p>Prix estimé : <strong><?= number_format($colisCree['prix_estime'], 2, ',', ' ') ?> €</strong></p>
        <p>Déposez votre colis à l'agence de départ. Le prix définitif sera calculé
           après la pesée par notre agent.</p>
    </div>
<?php endif; ?>

<?php if (!empty($erreurs)): ?>
    <ul>
        <?php foreach ($erreurs as $erreur): ?>
            <li><?= htmlspecialchars($erreur) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (empty($destinataires)): ?>

    <p>Vous devez d'abord ajouter un destinataire dans
       <a href="destinataires.php">votre carnet</a>.</p>

<?php else: ?>

    <form method="post" action="nouveau-colis.php">
        <p>
            <label for="id_destinataire">Destinataire *</label><br>
            <select id="id_destinataire" name="id_destinataire" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($destinataires as $d): ?>
                    <option value="<?= $d['id_destinataire'] ?>"
                        <?= $idDestinataire === (int) $d['id_destinataire'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['prenom'] . ' ' . $d['nom'] . ' (' . $d['ville'] . ', ' . $d['pays'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="id_agence_depart">Agence de départ (où vous déposez le colis) *</label><br>
            <select id="id_agence_depart" name="id_agence_depart" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($agences as $a): ?>
                    <option value="<?= $a['id_agence'] ?>"
                        <?= $idAgenceDepart === (int) $a['id_agence'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nom'] . ' (' . $a['pays'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="id_agence_arrivee">Agence d'arrivée (où le destinataire récupère le colis) *</label><br>
            <select id="id_agence_arrivee" name="id_agence_arrivee" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($agences as $a): ?>
                    <option value="<?= $a['id_agence'] ?>"
                        <?= $idAgenceArrivee === (int) $a['id_agence'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['nom'] . ' (' . $a['pays'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="description">Contenu du colis *</label><br>
            <input type="text" id="description" name="description" maxlength="255" required
                   placeholder="Ex : vêtements, chaussures"
                   value="<?= htmlspecialchars($description) ?>">
        </p>
        <p>
            <label for="poids">Poids estimé en kg *</label><br>
            <input type="number" id="poids" name="poids" min="0.01" max="9999.99" step="0.01" required
                   value="<?= htmlspecialchars($poids) ?>">
        </p>
        <p>
            <label for="valeur_declaree">Valeur déclarée en € (facultatif)</label><br>
            <input type="number" id="valeur_declaree" name="valeur_declaree" min="0" step="0.01"
                   value="<?= htmlspecialchars($valeur) ?>">
        </p>
        <p>
            <button type="submit">Créer l'envoi</button>
        </p>
    </form>

<?php endif; ?>

<p><a href="accueil.php">Retour à mon espace</a></p>

<?php require __DIR__ . '/../commun/footer.php'; ?>
