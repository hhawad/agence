<?php require __DIR__ . '/commun/header.php'; ?>

<h1>HD Transit</h1>
<p>Envoi de colis par avion entre la France et la Guinée, dans les deux sens.</p>

<h2>Suivre un colis</h2>

<form method="get" action="index.php">
    <p>
        <label for="numero">Numéro de suivi</label><br>
        <input type="text" id="numero" name="numero" maxlength="20" required
               placeholder="Ex : HD2026-7K3P9Q"
               value="<?= htmlspecialchars($numero) ?>">
        <button type="submit">Suivre</button>
    </p>
</form>

<?php if ($erreur !== ''): ?>
    <p><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<?php if ($colis !== null): ?>
    <h3>Colis <?= htmlspecialchars($colis['numero_suivi']) ?></h3>
    <p>Trajet : <?= htmlspecialchars($colis['ville_depart'] . ' (' . $colis['pays_depart'] . ') → '
                                    . $colis['ville_arrivee'] . ' (' . $colis['pays_arrivee'] . ')') ?></p>
    <p>Statut actuel : <strong><?= htmlspecialchars($colis['statut']) ?></strong></p>

    <h4>Historique</h4>
    <table border="1" cellpadding="6">
        <tr>
            <th>Date</th>
            <th>Étape</th>
        </tr>
        <?php foreach ($historique as $etape): ?>
            <tr>
                <td><?= date('d/m/Y à H:i', strtotime($etape['date_changement'])) ?></td>
                <td><?= htmlspecialchars($etape['statut']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if (!clientConnecte()): ?>
    <h2>Envoyer un colis</h2>
    <p><a href="inscription.php">Créez votre compte</a> ou <a href="connexion.php">connectez-vous</a>
       pour préparer votre envoi en ligne.</p>
<?php endif; ?>

<?php require __DIR__ . '/commun/footer.php'; ?>
