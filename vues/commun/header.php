<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Transit</title>
</head>
<body>
    <header>
        <strong>HD Transit</strong>
        <nav>
            <a href="<?= URL_SITE ?>/index.php">Accueil</a>
            <?php if (isset($_SESSION['id_client'])): ?>
                <a href="<?= URL_SITE ?>/client/accueil.php">Mon espace</a>
                <a href="<?= URL_SITE ?>/deconnexion.php">Se déconnecter</a>
            <?php else: ?>
                <a href="<?= URL_SITE ?>/inscription.php">Créer un compte</a>
                <a href="<?= URL_SITE ?>/connexion.php">Se connecter</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
