<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Mes colis</h1>

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

<?php if (empty($listeColis)): ?>
    <p>Vous n'avez encore envoyé aucun colis.
       <a href="nouveau-colis.php">Envoyer un colis</a></p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>N° de suivi</th>
            <th>Date</th>
            <th>Destinataire</th>
            <th>Trajet</th>
            <th>Contenu</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
        <?php foreach ($listeColis as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['numero_suivi']) ?></strong></td>
                <td><?= date('d/m/Y', strtotime($c['date_enregistrement'])) ?></td>
                <td><?= htmlspecialchars($c['prenom_destinataire'] . ' ' . $c['nom_destinataire']) ?></td>
                <td><?= htmlspecialchars($c['ville_depart'] . ' → ' . $c['ville_arrivee']) ?></td>
                <td><?= htmlspecialchars($c['description_contenu']) ?></td>
                <td>
                    <?php if ($c['montant'] !== null): ?>
                        <?= number_format((float) $c['montant'], 2, ',', ' ') ?> €
                        <?= $c['est_paye'] ? '(payé)' : '(à payer)' ?>
                    <?php elseif ($c['prix_kg'] !== null): ?>
                        ≈ <?= number_format((float) $c['poids_estime'] * (float) $c['prix_kg'], 2, ',', ' ') ?> € (estimé)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($c['statut']) ?></td>
                <td>
                    <?php if ((int) $c['id_statut'] === 1): ?>
                        <form method="post" action="mes-colis.php"
                              onsubmit="return confirm('Annuler cet envoi ?');">
                            <input type="hidden" name="action" value="annuler">
                            <input type="hidden" name="id_colis" value="<?= $c['id_colis'] ?>">
                            <button type="submit">Annuler</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="accueil.php">Retour à mon espace</a></p>

<?php require __DIR__ . '/../commun/footer.php'; ?>
