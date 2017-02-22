<!Doctype html>
<html>
    <head>
        <title>Lokisalle - <?= $page ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= RACINE_SITE ?>css/style.css"/>
          <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
        <header>
			<div class="conteneur">
				<span>
					<a href="" title="Lokisalle">Lokisalle</a>
                </span>
				<nav>
				<?php if(userConnecte()):?>
					<a <?= ($page == 'Profil') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>profil.php">Profil</a>
					<a <?= ($page == 'Boutique') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>boutique.php">Boutique</a>
					<a <?= ($page == 'Panier') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>panier.php">Panier<?php if(quantitePanier()) : ?><span class="bulle"><?= quantitePanier()?></span><?php endif ?></a>
					<a href="<?= RACINE_SITE ?>connexion.php?action=deconnexion">Deconnexion</a>
				<?php else : ?>
					<a <?= ($page == 'Inscription') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>inscription.php">Inscription</a>
					<a <?= ($page == 'Boutique') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>boutique.php">Boutique</a>
					<a <?= ($page == 'Panier') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>panier.php">Panier<?php if(quantitePanier()) : ?><span class="bulle"><?= quantitePanier()?></span><?php endif ?></a>


					<a <?= ($page == 'Connexion') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>connexion.php">Connexion</a>
				<?php endif; ?>
				<?php if(userAdmin()) : ?>
					<a <?= ($page == 'Gestion Boutique') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>admin/gestion_boutique.php">Gestion Boutique</a>
					<a <?= ($page == 'Gestion Membres') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>admin/gestion_membres.php">Gestion Membres</a>
					<a <?= ($page == 'Gestion Commandes') ? 'class="active"' : '' ?> href="<?= RACINE_SITE ?>admin/gestion_commandes.php">Gestion Commandes</a>
				<?php endif; ?>
				</nav>
			</div>
        </header>
        <section>
			<div class="conteneur">
