<?php require __DIR__ . '/../commun/header.php'; ?>

<h1>Bonjour <?= htmlspecialchars($prenom) ?> !</h1>

<p>Vous êtes connecté(e) à l'espace agent
   <?= $estAdmin ? '(administrateur)' : '(employé)' ?>.</p>
<p>Votre agence : <strong><?= htmlspecialchars($agence['nom']) ?></strong></p>

<h2>Rechercher un colis</h2>
<form method="get" action="colis.php">
    <label for="numero">Numéro de suivi</label>
    <input type="text" id="numero" name="numero" maxlength="20" required placeholder="HD2026-...">
    <button type="submit">Ouvrir</button>
</form>

<h2>Colis à traiter <?= $estAdmin ? '(toutes les agences)' : '' ?></h2>

<?php if (empty($listeColis)): ?>
    <p>Aucun colis à traiter pour le moment.</p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>N° de suivi</th>
            <th>Date</th>
            <th>Trajet</th>
            <th>Statut</th>
        </tr>
        <?php foreach ($listeColis as $c): ?>
            <tr>
                <td>
                    <a href="colis.php?numero=<?= urlencode($c['numero_suivi']) ?>">
                        <?= htmlspecialchars($c['numero_suivi']) ?>
                    </a>
                </td>
                <td><?= date('d/m/Y', strtotime($c['date_enregistrement'])) ?></td>
                <td><?= htmlspecialchars($c['ville_depart'] . ' → ' . $c['ville_arrivee']) ?></td>
                <td><?= htmlspecialchars($c['statut']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../commun/footer.php'; ?>
