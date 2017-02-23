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
		$resultat = $pdo -> prepare("REPLACE INTO produit (id_produit, date_arrivee , date_depart, id_salle, prix, etat) VALUES (:id_produit, :date_arrivee, :date_depart, :id_salle, :prix, :etat)");

		$resultat -> bindParam(':id_produit', $_POST['id_produit'], PDO::PARAM_INT);
	}
	else{
		$resultat = $pdo -> prepare("INSERT INTO produit ( date_arrivee , date_depart, id_salle, prix, etat) VALUES (:date_arrivee, :date_depart, :id_salle, :prix, :etat)");
	}// !!!!!!! FERMETURE DU ELSE !!!!!!!!
if(!empty($_POST['date_arrivee'])) {
	$date = new DateTime($_POST['date_arrivee']);
	$date =  $date->format('Y-m-d H:i:s');
}

if(!empty($_POST['date_depart'])) {
	$date1 = new DateTime($_POST['date_depart']);
	$date1 =  $date1->format('Y-m-d H:i:s');
}

	//STR
	$resultat -> bindParam(':date_arrivee', $date, PDO::PARAM_STR);
	$resultat -> bindParam(':date_depart', $date1, PDO::PARAM_STR);
	$resultat -> bindParam(':id_salle', $_POST['salle'], PDO::PARAM_INT);
	$resultat -> bindParam(':prix', $_POST['prix'], PDO::PARAM_INT);
	$resultat -> bindParam(':etat', $_POST['etat'], PDO::PARAM_STR);


	if($resultat -> execute()){
		$_GET['action'] = 'affichage';
		$last_id = $pdo -> lastInsertId();
		$msg .= '<div class="validation">Le produit N°' . $last_id . ' a bien été enregistré</div>';
	}
	// Pourquoi effectuer -> execute() dans le if ?
	// Après avoir executer ma requête, je souhaite lancer d'autres traitements (affichage d'un message, redirection etc...). le problème est que ces traitements ce lanceront quoi qu'il arrive, même si la requête echoue.
	// en effectuant ces traitements dans un if($resultat -> execute()) cela garantit qu'ils ne s'effectueront qu'en cas de succès de la requête.
	// ====> Si la requete echoue on fait rien !!

}


// Supprimer un produit
// Il faut d'abord supprimer du serveur la photo correspondant au produit pour faire les choses "proprement".
if(isset($_GET['action']) && $_GET['action'] == 'supprimer'){ // SI une action de supprimer est passée dans l'url, on vérifie qu'il y a bien un ID et que cette ID est une valeur numérique.
	if(isset($_GET['id_produit']) && is_numeric($_GET['id_produit'])){
		//Si c'est OK au niveau de l'ID, puis que je dois supprimer la photo du produit je dois récupérer le nom de la photo dans la BDD. D'où la requete de selection ci-dessous :
		$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
		$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
		$resultat -> execute();

		if($resultat -> rowCount() > 0){
			// Si on a trouvé au moins un produit existant dans la BDD, c'est que l'ID était bien correcte. On vérifie cela au cas où l'ID transmis dans l'URL aurait été modifié ou erroné...
			$produit = $resultat -> fetch(PDO::FETCH_ASSOC);

			// Pour pouvoir supprimer une photo, il nous faut son chemin absolu, que l'on reconstitue depuis la racine du serveur ci-dessous:
			$chemin_de_la_photo_a_supprimer = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'photo/' . $salle['photo'];

			// Dernieres vérifs : Si le fichier existe et que ce n'est pas la photo par défault, alors la fonction unlink() supprime le fichier.
			if(file_exists($chemin_de_la_photo_a_supprimer) && $produit['photo'] != 'default.jpg'){
				unlink($chemin_de_la_photo_a_supprimer); // unlink : supprime un fichier de mon serveur.
			}

			//Après avoir supprimer la photo du produit on peut enfin supprimer le produit lui-même de notre BDD :
			$resultat = $pdo -> exec("DELETE FROM produit WHERE id_produit = $produit[id_produit]");

			if($resultat != FALSE){
				$_GET['action'] = 'affichage';
				$msg .= '<div class="validation">Le produit N°' . $produit['id_produit'] . ' a bien été supprimé !</div>';
			}
		}
	}
}


// Récupérer toutes les infos de tous les produits
// Afficher toutes les infos de tous les produits
if(isset($_GET['action']) && $_GET['action'] == 'affichage'){ // Si une action pour afficher les produits est demandée dans l'URL
	// Alors on récupère toutes les infos de tous les produits
	$resultat = $pdo -> query("SELECT * FROM produit");

	// On affiche ces infos via des boucles, dans un tableau HTML (stocké dans une variable $contenu
	$contenu .= '<table border="1">';
	$contenu .= '<tr>';
	for($i = 0; $i < $resultat -> columnCount(); $i++){
		$meta = $resultat -> getColumnMeta($i);
		$contenu .= '<th>' . $meta['name'] . '</th>';
	}
	$contenu .= '<th colspan="2">Actions</th>';
	$contenu .= '</tr>';
	while($produits = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu .= '<tr>';
		foreach($produits as $indice => $valeur){
			// Lorsqu'on parcourt un enregistrement on souhaite afficher la photo dans une balise IMG et non en texte. On fait donc une condition dans le foreach :
			if($indice == 'photo'){
				$contenu .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="100"/></td>';
			}
			else{
				$contenu .= ' <td>' . $valeur . '</td>';
			}
		}
		// En face de chaque enregistrement on ajoute deux actions : Modifie et supprimer en GET et précisant l'ID de chaque enregistrement.
		$contenu .= '<td><a href="?action=modifier&id_produit='. $produits['id_salle'] .'"><img src="' . RACINE_SITE . 'img/edit.png"/></a></td>';
		$contenu .= '<td><a href="?action=supprimer&id_produit='. $produits['id_salle'] .'"><img src="' . RACINE_SITE . 'img/delete.png"/></a></td>';
		$contenu .= '</tr>';
	}
	$contenu .= '</table>';
}

$page = 'Gestion Salle';
require_once('../inc/header.inc.php');
?>
<!-- Contenu de la page -->

<h1>Gestion des Produits </h1>
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

	$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
	$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
	if($resultat -> execute()){
		$produit_actuel = $resultat -> fetch(PDO::FETCH_ASSOC);
		//$produit_actuel est un array qui contient toutes les infos du produit à modifier.
	}
}
// Si produit actuel existe (je suis dans le cadre d'une modif), alors je stocke les valeurs du produit dans des variables (plus simples pour les afficher dans le champs) sinon je stocke une valeur vide.
// Les lignes ci-dessous servent simplement à éviter de mettre trop de PHP dans notre formulaire.
$date_arrivee = (isset($produit_actuel)) ? $produit_actuel['date_arrivee'] : '';

$date_depart = (isset($produit_actuel)) ? $produit_actuel['date_depart'] : '';
$id_produit = (isset($produit_actuel)) ? $produit_actuel['id_produit'] : '';
$prix = (isset($produit_actuel)) ? $produit_actuel['prix'] : '';
$etat = (isset($produit_actuel)) ? $produit_actuel['etat'] : '';




$action = (isset($produit_actuel)) ? 'Modifier' : 'Ajouter';
$id_salle = (isset($produit_actuel)) ? $produit_actuel['id_salle'] : '';
?>


<h2><?= $action ?> Gestion des produits</h2>

<form method="post" action="" enctype="multipart/form-data">
<!-- L'attribut enctype permet de gérer les fichiers uploadés et de mes traiter grâce à la superglobale $_FILES -->
<div class="form-group">
	<div class=" col-sm-offset-3 col-sm-4">

	<input type="hidden"  name="id_produit" value="<?= $id_produit ?>" />
</div>
</div>
<br/>
<div class="form-group">
  <div class=" col-sm-offset-3 col-sm-4">
    <label for="datetime">Date arrivée </label>
    <div class="input-group">
      <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span><span class="glyphicon glyphicon-time"></span></span>
      <input class="form-control datetime" name="date_arrivee" id="datetime" type="text" value="" >
    </div>
    </div>
  </div>
  <br/>
  <div class="form-group">
    <div class=" col-sm-offset-3 col-sm-4">
      <label for="datetime1"> Date départ </label>
      <div class="input-group">
        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span><span class="glyphicon glyphicon-time"></span></span>
        <input class="form-control datetime" name="date_depart" id="datetime1" type="text" value="" >
      </div>
    </div>
    </div>
<br/>



<div class="form-group">
  <div class="col-sm-offset-3 col-sm-4">
<label> Salle: </label>
<select name="salle" class="form-control">
<?php
$resultat = $pdo->query('SELECT * FROM salle');
while ($salle = $resultat->fetch())
{
?>
           <option value=" <?php echo $salle['id_salle']; ?>"> <?php echo $salle['id_salle']; ?></option>
           <?php
           }

           ?>

</select>
</div>
</div>


		<?php if(isset($produit_actuel)) : ?>
		<input type="hidden" name="photo_actuelle" value="<?= $photo ?>" />
		<img src="<?= RACINE_SITE ?>photo/<?= $photo ?>" width="100" /><br/>
		<?php endif; ?>


	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-4">
      <label>Prix </label>
        <input type='text' class=" col-sm-3" name="prix" value="<?= $prix ?>" />
        <span class="  input-group-addon col-sm-3">
              <span class="  glyphicon glyphicon-euro  "></span>
        </span>
      </div>
    </div>
<br/>
<br/>

<div class="form-group">
	<div class="col-sm-offset-3 col-sm-4">
<label>etat: </label>
<select name="etat" class="form-control">
	<option>-- Selectionnez --</option>
	<option <?= ($etat == 'libre') ? 'selected' : '' ?> value="libre">libre</option>
	<option <?= ($etat == 'occupation') ? 'selected' : '' ?> value="occupation">occupation</option>

</select>
</div>
</div>
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

<script type="text/javascript">
  $('#datetime').datetimepicker({
  todayBtn:"true",
  format:"dd-mm-yyyy hh:ii",
  autoclose:"true",
  pickerPosition:"bottom-left",
  startView:"year",
  minView:"hour",
  language:"fr"
  });
  $('#datetime1').datetimepicker({
  todayBtn:"true",
  format:"dd-mm-yyyy hh:ii",
  autoclose:"true",
  pickerPosition:"bottom-left",
  startView:"year",
  minView:"hour",
  language:"fr"
  });
</script>
