<?php
require_once('inc/init.inc.php');

//Redirection si pas connecté 
if(!userConnecte()){ // Si la fonction me retourne FALSE
	header('location:connexion.php');
}


//Pour afficher les infos
extract($_SESSION['membre']);

$page = 'Profil';
require_once('inc/header.inc.php'); 
?>

<!-- Contenu de la page -->
<h1>Profil de <?= $pseudo ?></h1>

<div class="profil">
	<p>Bonjour <?= $pseudo?> !</p><br/>
	
	<div class="profil_img">
		<img src="img/default.png"/>
	</div>
	<div class="profil_infos">
		<ul>
			<li>Pseudo : <b><?= $pseudo ?></b></li>
			<li>Prénom : <b><?= $prenom ?></b></li>
			<li>Nom: <b><?= $nom ?></b></li>
		</ul>
	</div>
	<div class="profil_adresse">
		<ul>
			<li>Adresse : <b><?= $adresse ?></b></li>
			<li>Code Postal : <b><?= $code_postal ?></b></li>
			<li>Ville : <b><?= $ville ?></b></li>
		</ul>
	</div>
</div>




<?php
require_once('inc/footer.inc.php');
?>