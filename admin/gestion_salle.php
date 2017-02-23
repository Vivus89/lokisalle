<?php
require_once('../inc/init.inc.php');

// redirection si pas admin
if(!userAdmin()){
	header('location:../connexion.php');
}



// Ajouter et modifier un produit
// Dans ces traitements on va à la fois être capable d'ajouter un nouveau produit, et à la fois modifier un produit existant.
if($_POST){

	// debug($_POST);
	// debug($_FILES);
	//$_FILES est une supergloble (array multidimentionnel) qui récupère les infos des fichiers uploadés. Pour chaque fichier on récupère le nom, le type, l'emplacement temporaire, erreur (BOOL), la taille en octets.

	//$nom_photo va quoi qu'il arrive contenir le nom de la photo a enregistrer en BDD.
	//Elle va soit contenir un nom par défaut, soit le nom de l'image uploadé (on modifiera le nom) soit le nom de la photo du produit en cours de modification.
	// Dans le cas où une nouvelle photo est ajoutée, en plus de renommer cette photo (pour éviter les collision) je l'enregistre dans le serveur (fonction copy())

	$nom_photo = 'default.jpg';

	if(isset($_POST['photo_actuelle'])){
		$nom_photo = $_POST['photo_actuelle'];
	}

	if(!empty($_FILES['photo']['name'])){
		// On renomme la photo (pour éviter les doublons sur notre serveur)
		$nom_photo = $_POST['titre'] . '_' . $_FILES['photo']['name'];

		//Enregistre la photo sur le serveur.
		$chemin_photo = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'photo/' . $nom_photo;
		//$chemin_photo est l'emplacement définitif de la photo depuis la base du serveur jusqu'au nom du fichier.


		copy($_FILES['photo']['tmp_name'], $chemin_photo); // On déplace la photo depuis son emplacement temporaire, vers son emplacement définitif. Emplacement temporaire : $_FILES['photo']['tmp_name']
	}

	// Enregistrement dans la BDD :
	// Depuis SQL 5.7, dans une requête REPLACE on ne peux plus mettre la clé primaire vide ou NULL. ON doit donc faire une requête pour l'ajout et une requete pour la modif. d'où le if/else ci-desous.

	if(isset($_GET['action']) && $_GET['action'] == 'modifier'){
		$resultat = $pdo -> prepare("REPLACE INTO salle (id_salle, titre, description, photo, pays, ville, adresse, categorie) VALUES (:id_salle, :titre, :description, '$nom_photo', :pays, :ville, :adresse, :categorie )");

		$resultat -> bindParam(':id_salle', $_POST['id_salle'], PDO::PARAM_INT);
	}
	else{
		$resultat = $pdo -> prepare("INSERT INTO salle (titre, description, photo, pays, ville, adresse, categorie) VALUES (:titre, :description, '$nom_photo', :pays, :ville, :adresse, :categorie)");
	}// !!!!!!! FERMETURE DU ELSE !!!!!!!!

	//STR
	$resultat -> bindParam(':titre', $_POST['titre'], PDO::PARAM_STR);
	$resultat -> bindParam(':description', $_POST['description'], PDO::PARAM_STR);
	$resultat -> bindParam(':pays', $_POST['pays'], PDO::PARAM_STR);
	$resultat -> bindParam(':ville', $_POST['ville'], PDO::PARAM_STR);
	$resultat -> bindParam(':adresse', $_POST['adresse'], PDO::PARAM_STR);
	$resultat -> bindParam(':categorie', $_POST['categorie'], PDO::PARAM_STR);

	if($resultat -> execute()){
		$_GET['action'] = 'affichage';
		$last_id = $pdo -> lastInsertId();
		$msg .= '<div class="validation">La salle N°' . $last_id . ' a bien été enregistré</div>';
	}
	// Pourquoi effectuer -> execute() dans le if ?
	// Après avoir executer ma requête, je souhaite lancer d'autres traitements (affichage d'un message, redirection etc...). le problème est que ces traitements ce lanceront quoi qu'il arrive, même si la requête echoue.
	// en effectuant ces traitements dans un if($resultat -> execute()) cela garantit qu'ils ne s'effectueront qu'en cas de succès de la requête.
	// ====> Si la requete echoue on fait rien !!

}


// Supprimer un produit
// Il faut d'abord supprimer du serveur la photo correspondant au produit pour faire les choses "proprement".
if(isset($_GET['action']) && $_GET['action'] == 'supprimer'){ // SI une action de supprimer est passée dans l'url, on vérifie qu'il y a bien un ID et que cette ID est une valeur numérique.
	if(isset($_GET['id_salle']) && is_numeric($_GET['id_salle'])){
		//Si c'est OK au niveau de l'ID, puis que je dois supprimer la photo du produit je dois récupérer le nom de la photo dans la BDD. D'où la requete de selection ci-dessous :
		$resultat = $pdo -> prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
		$resultat -> bindParam(':id_salle', $_GET['id_produit'], PDO::PARAM_INT);
		$resultat -> execute();

		if($resultat -> rowCount() > 0){
			// Si on a trouvé au moins un produit existant dans la BDD, c'est que l'ID était bien correcte. On vérifie cela au cas où l'ID transmis dans l'URL aurait été modifié ou erroné...
			$salle = $resultat -> fetch(PDO::FETCH_ASSOC);

			// Pour pouvoir supprimer une photo, il nous faut son chemin absolu, que l'on reconstitue depuis la racine du serveur ci-dessous:
			$chemin_de_la_photo_a_supprimer = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'photo/' . $salle['photo'];

			// Dernieres vérifs : Si le fichier existe et que ce n'est pas la photo par défault, alors la fonction unlink() supprime le fichier.
			if(file_exists($chemin_de_la_photo_a_supprimer) && $salle['photo'] != 'default.jpg'){
				unlink($chemin_de_la_photo_a_supprimer); // unlink : supprime un fichier de mon serveur.
			}

			//Après avoir supprimer la photo du produit on peut enfin supprimer le produit lui-même de notre BDD :
			$resultat = $pdo -> exec("DELETE FROM salle WHERE id_salle = $salle[id_salle]");

			if($resultat != FALSE){
				$_GET['action'] = 'affichage';
				$msg .= '<div class="validation">Le produit N°' . $salle['id_salle'] . ' a bien été supprimé !</div>';
			}
		}
	}
}


// Récupérer toutes les infos de tous les produits
// Afficher toutes les infos de tous les produits
if(isset($_GET['action']) && $_GET['action'] == 'affichage'){ // Si une action pour afficher les produits est demandée dans l'URL
	// Alors on récupère toutes les infos de tous les produits
	$resultat = $pdo -> query("SELECT * FROM salle");

	// On affiche ces infos via des boucles, dans un tableau HTML (stocké dans une variable $contenu
	$contenu .= '<table border="1">';
	$contenu .= '<tr>';
	for($i = 0; $i < $resultat -> columnCount(); $i++){
		$meta = $resultat -> getColumnMeta($i);
		$contenu .= '<th>' . $meta['name'] . '</th>';
	}
	$contenu .= '<th colspan="2">Actions</th>';
	$contenu .= '</tr>';
	while($salles = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu .= '<tr>';
		foreach($salles as $indice => $valeur){
			// Lorsqu'on parcourt un enregistrement on souhaite afficher la photo dans une balise IMG et non en texte. On fait donc une condition dans le foreach :
			if($indice == 'photo'){
				$contenu .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="100"/></td>';
			}
			else{
				$contenu .= ' <td>' . $valeur . '</td>';
			}
		}
		// En face de chaque enregistrement on ajoute deux actions : Modifie et supprimer en GET et précisant l'ID de chaque enregistrement.
		$contenu .= '<td><a href="?action=modifier&id_salle='. $salles['id_salle'] .'"><img src="' . RACINE_SITE . 'img/edit.png"/></a></td>';
		$contenu .= '<td><a href="?action=supprimer&id_salle='. $salles['id_salle'] .'"><img src="' . RACINE_SITE . 'img/delete.png"/></a></td>';
		$contenu .= '</tr>';
	}
	$contenu .= '</table>';
}

$page = 'Gestion Salle';
require_once('../inc/header.inc.php');
?>
<!-- Contenu de la page -->

<h1>Gestion de la boutique</h1>
<ul>
	<!-- Les deux liens ci-dessous (sous-menu) permettent de lancer 2 actions : Affichage de tous les produits et Affichage du formulaire d'ajout de produit. -->
	<li><a href="?action=affichage">Afficher les salles</a></li>
	<li><a href="?action=ajout">Ajouter une salle</a></li>
</ul><hr/>
<?= $msg ?>
<?= $contenu ?>

<!-- Affichage du formulaire (ajouter ou pour modifier) -->
<?php if(isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'modifier')) :
// Si une action d'ajout ou de modification est demandée via l'URL, dans ce cas, on affiche le formulaire ci-dessous.
?>
<?php
if(isset($_GET['id_salle']) && is_numeric($_GET['id_salle'])){ // Dans le cas où l'action est de modifier un produit, alors j'ai un ID dans l'URL, qui va me permettre de récupérer toutes les infos du produits à modifier (requête ci-dessous) :

	$resultat = $pdo -> prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
	$resultat -> bindParam(':id_salle', $_GET['id_salle'], PDO::PARAM_INT);
	if($resultat -> execute()){
		$salle_actuel = $resultat -> fetch(PDO::FETCH_ASSOC);
		//$produit_actuel est un array qui contient toutes les infos du produit à modifier.
	}
}
// Si produit actuel existe (je suis dans le cadre d'une modif), alors je stocke les valeurs du produit dans des variables (plus simples pour les afficher dans le champs) sinon je stocke une valeur vide.
// Les lignes ci-dessous servent simplement à éviter de mettre trop de PHP dans notre formulaire.
$titre = (isset($salle_actuel)) ? $salle_actuel['titre'] : '';
$description = (isset($salle_actuel)) ? $salle_actuel['description'] : '';
//$photo = (isset(salle_actuel)) ? salle_actuel['photo'] : '';
$pays = (isset($salle_actuel)) ? $salle_actuel['pays'] : '';
$ville = (isset($salle_actuel)) ? $salle_actuel['ville'] : '';
$adresse = (isset($salle_actuel)) ? $salle_actuel['adresse'] : '';
$categorie = (isset($salle_actuel)) ? $salle_actuel['categorie'] : '';



$action = (isset($salle_actuel)) ? 'Modifier' : 'Ajouter';
$id_salle = (isset($salle_actuel)) ? $salle_actuel['id_salle'] : '';
?>


<h2><?= $action ?> Gestion des salles</h2>

<form method="post" action="" enctype="multipart/form-data">
<!-- L'attribut enctype permet de gérer les fichiers uploadés et de mes traiter grâce à la superglobale $_FILES -->
<div class="form-group">
	<div class="col-sm-offset-3 col-sm-4">

<label>Titre </label>
<input class="form-control" type="text" name="titre" placeholder="Titre de la salle" value="<?= $titre ?>"/>
</div>
</div>
<br/>

<div class="form-group">
	<div class=" col-sm-offset-3 col-sm-4">
	<input type="hidden" name="id_salle" value="<?= $id_salle ?>" />
</div>
</div>
	<div class="form-group">
		<div  class="col-sm-offset-3 col-sm-4">
	<label>description: </label>
	<textarea  name="description"  class="form-control" value="<?= $description ?>" placeholder="Description de la salle" > </textarea>
</div>
</div>
<br/>
	<div class="form-group">

		<?php if(isset($produit_actuel)) : ?>
		<input type="hidden" name="photo_actuelle" value="<?= $photo ?>" />
		<img src="<?= RACINE_SITE ?>photo/<?= $photo ?>" width="100" /><br/>
		<?php endif; ?>
		<div class="col-sm-offset-3 col-sm-4">
	<label> Photo </label>
	<label for="exampleInputFile">File input</label>
    <input class="form-control" type="file" id="exampleInputFile" name="photo"/>
</div>
</div>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-4">
	<label>Adresse: </label>
	<textarea type="text"   class="form-control" name="adresse" placeholder="Adresse de la salle" value="<?= $adresse ?>"></textarea>
</div>
</div>
<br/>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-4">
	<label>categorie: </label>
	<select name="categorie" class="form-control">
		<option>-- Selectionnez --</option>
		<option <?= ($categorie == 'reunion') ? 'selected' : '' ?> value="reunion">reunion</option>
		<option <?= ($categorie == 'bureau') ? 'selected' : '' ?> value="bureau">bureau</option>
<option <?= ($categorie == 'formation') ? 'selected' : '' ?> value="formation">formation</option>
	</select>
	</div>
	</div>
<br/>

	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-4">
	<label>Pays: </label>
	<select name="pays" class="form-control" id="pays" >
		<option>-- Selectionnez --</option>
		<option <?= ($pays == 'france') ? 'selected' : '' ?> value="France">france</option>
		<option <?= ($pays == 'Italie') ? 'selected' : '' ?> value="Italie">Italie</option>
		<option <?= ($pays == 'Espagne') ? 'selected' : '' ?> value="Espagne">Espagne</option>
	</select>
	</div>
	</div>
	<br/>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-4">
			<label>Ville: </label>
	<select class="form-control" id="ville" name="ville">
		<option></option>
		<option></<option>
	</select>
</div>
</div>
<br/>
<br/>
<div class="form-group">

	<input type="submit" class="btn btn-primary" value="<?= $action ?>"/>
	<br/>
</div>
</form>

<?php endif;?>

<?php
require_once('../inc/footer.inc.php');
?>
