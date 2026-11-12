<?php require __DIR__ . '/../commun/header.php'; ?>

<p><a href="accueil.php">← Retour à l'espace agent</a></p>

<?php if ($colis === null): ?>

    <h1>Colis introuvable</h1>
    <p>Aucun colis ne correspond au numéro « <?= htmlspecialchars($numero) ?> ».</p>

<?php else: ?>

    <h1>Colis <?= htmlspecialchars($colis['numero_suivi']) ?></h1>

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

    <p>Statut actuel : <strong><?= htmlspecialchars($colis['statut']) ?></strong></p>

    <table border="1" cellpadding="6">
        <tr>
            <th>Expéditeur</th>
            <td><?= htmlspecialchars($colis['prenom_client'] . ' ' . $colis['nom_client']
                                     . ' - ' . $colis['telephone_client']) ?></td>
        </tr>
        <tr>
            <th>Destinataire</th>
            <td><?= htmlspecialchars($colis['prenom_destinataire'] . ' ' . $colis['nom_destinataire']
                                     . ' - ' . $colis['telephone_destinataire']) ?></td>
        </tr>
        <tr>
            <th>Trajet</th>
            <td><?= htmlspecialchars($colis['agence_depart'] . ' → ' . $colis['agence_arrivee']) ?></td>
        </tr>
        <tr>
            <th>Contenu</th>
            <td><?= htmlspecialchars($colis['description_contenu']) ?></td>
        </tr>
        <tr>
            <th>Poids estimé / réel</th>
            <td>
                <?= number_format((float) $colis['poids_estime'], 2, ',', ' ') ?> kg /
                <?= $colis['poids_reel'] !== null
                    ? number_format((float) $colis['poids_reel'], 2, ',', ' ') . ' kg'
                    : 'pas encore pesé' ?>
            </td>
        </tr>
        <tr>
            <th>Prix</th>
            <td>
                <?php if ($colis['montant'] !== null): ?>
                    <?= number_format((float) $colis['montant'], 2, ',', ' ') ?> €
                    (<?= number_format((float) $colis['prix_kg_applique'], 2, ',', ' ') ?> €/kg) -
                    <strong><?= $colis['est_paye'] ? 'payé' : 'NON payé' ?></strong>
                <?php else: ?>
                    calculé à la pesée
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <h2>Action</h2>

    <?php if ($statut === Colis::STATUT_CREE && $agitAuDepart): ?>

        <form method="post">
            <input type="hidden" name="action" value="valider">
            <p>
                <label for="poids_reel">Poids réel en kg (après pesée) *</label><br>
                <input type="number" id="poids_reel" name="poids_reel" min="0.01" max="9999.99" step="0.01" required>
                <?php if ($prixKg !== null): ?>
                    (tarif : <?= number_format($prixKg, 2, ',', ' ') ?> €/kg)
                <?php endif; ?>
            </p>
            <p>
                <label><input type="checkbox" name="est_paye" value="1"> Le client a payé</label>
            </p>
            <p><button type="submit">Valider le dépôt</button></p>
        </form>

    <?php elseif ($statut === Colis::STATUT_DEPOSE && $agitAuDepart): ?>

        <form method="post">
            <input type="hidden" name="action" value="transit">
            <button type="submit">Le colis part dans l'avion → En transit</button>
        </form>

    <?php elseif ($statut === Colis::STATUT_TRANSIT && $agitALArrivee): ?>

        <form method="post">
            <input type="hidden" name="action" value="arrivee">
            <button type="submit">Le colis est arrivé à l'agence</button>
        </form>

    <?php elseif ($statut === Colis::STATUT_ARRIVE && $agitALArrivee): ?>

        <p>Vérifiez l'identité du destinataire avant de remettre le colis.</p>
        <form method="post" onsubmit="return confirm('Confirmer la remise du colis au destinataire ?');">
            <input type="hidden" name="action" value="retrait">
            <button type="submit">Colis remis au destinataire → Retiré</button>
        </form>

    <?php else: ?>

        <p>Aucune action possible pour vous sur ce colis.</p>

    <?php endif; ?>

    <?php if ($colis['montant'] !== null && !$colis['est_paye'] && ($agitAuDepart || $agitALArrivee)): ?>
        <form method="post">
            <input type="hidden" name="action" value="payer">
            <p><button type="submit">Enregistrer le paiement</button></p>
        </form>
    <?php endif; ?>

    <h2>Historique</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>Date</th>
            <th>Étape</th>
            <th>Par</th>
            <th>Commentaire</th>
        </tr>
        <?php foreach ($historique as $etape): ?>
            <tr>
                <td><?= date('d/m/Y à H:i', strtotime($etape['date_changement'])) ?></td>
                <td><?= htmlspecialchars($etape['statut']) ?></td>
                <td>
                    <?= $etape['prenom_agent'] !== null
                        ? htmlspecialchars($etape['prenom_agent'] . ' ' . $etape['nom_agent'])
                        : 'Client' ?>
                </td>
                <td><?= htmlspecialchars($etape['commentaire'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>

<?php require __DIR__ . '/../commun/footer.php'; ?>
