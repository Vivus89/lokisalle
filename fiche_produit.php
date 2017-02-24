<?php
require_once('inc/init.inc.php');


require_once('inc/init.inc.php');

if(!userAdmin()){
	header('location:connexion.php');
}

if(isset($_GET['id_produit']) && $_GET['id_produit'] != ''){
	if(is_numeric($_GET['id_produit'])){
		$resultat = $pdo -> prepare(
		"SELECT *
		FROM produit p, salle s
		WHERE p.id_salle = s.id_salle
		AND id_produit = $_GET[id_produit]");

		$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
		$resultat -> execute();


		if($resultat -> rowCount() > 0){
			// Si tout est OK, je récupère les infos du produit dans un array $produit
			$produit = $resultat -> fetch(PDO::FETCH_ASSOC);
			// debug($produit);
			extract($produit);
			//debug($produit);
      /*
      $resultat = $pdo -> query("SELECT ROUND(AVG(avis), 0) as note_moyenne FROM avis WHERE id_produit = $id_produit");
      $result = $resultat -> fetch(PDO::FETCH_ASSOC);
      extract($result); // $note_moyenne nous donne la note moyenne

      $resultat = $pdo -> query("SELECT * FROM avis WHERE id_produit = $id_produit");
      $nbr_de_note = $resultat -> rowCount();
      */
		}
		else{
			// Si l'ID ne correspond à aucun produit en BBD = REDIRECTION
			header('location:index.php');
		}
	}
	else{
		// Si l'ID dans l'URL n'est pas un chiffre = REDIRECTION
		header('location:index.php');
	}
}
else{
	// S'il n'y a pas d'ID dans l'URL ou qu'il est vide = REDIRECTION
	header('location:index.php');
}

require_once('inc/header.inc.php');



//traitement pour ajouter le produit au panier
if($_POST && $_POST['date_arrivee'] > 0){
	ajouterProduit($id_salle, $_POST['date_arrivee'],$date_depart, $titre, $photo, $prix);
}


// traitement pour récupérer toutes les suggestions de produit
$resultat = $pdo -> query("SELECT * FROM salle ");
$suggestions = $resultat -> fetchAll(PDO::FETCH_ASSOC);



// TRAITEMENT POUR LES NOTATIONS

// Enregistrer la note
// On va afficher le bloc "Note" que si l'utilisateur est connecté
// On va afficher le bloc "Note" que si l'utilisateur n'a pas encore noté
// Récupérer la note moyenne du produit
// Afficher la note du produit


$note_valide = array('1', '2', '3', '4','5');
if(isset($_GET['note']) && !empty($_GET['note']) && in_array($_GET['note'], $note_valide)){
	// on vérifie que l'utilisateur est connecte et qu'il n'a pas déjà donné une note à ce produit.
	if(userConnecte()){
		$id_membre = $_SESSION['membre']['id_membre'];
		$resultat = $pdo -> query("SELECT * FROM note WHERE id_membre = $id_membre AND id_salle = $id_salle");

		if($resultat -> rowCount() == 0){
			$resultat = $pdo -> prepare("INSERT INTO note (id_membre, id_salle, note, date_enregistrement) VALUES ($id_membre, $id_salle, :note, NOW())");
			$resultat -> bindParam(':note', $_GET['note'], PDO::PARAM_STR);
			if($resultat -> execute()){
				header('location:fiche_produit.php?id_salle=' . $id_salle);
			}
		}
	}
}

/*if(userConnecte()){
	$id_membre = $_SESSION['membre']['id_membre'];
	$resultat = $pdo -> query("SELECT * FROM note WHERE id_membre = $id_membre AND id_salle = $id_salle");

	if($resultat -> rowCount() > 0){
		$note = $resultat -> fetch(PDO::FETCH_ASSOC);
		$note_user = $note['note'];
	}
}*/

require_once('inc/header.inc.php');
?>

<h1><?= $titre ?></h1>

<!-- Portfolio Item Row -->
<div class="row">

    <div class="col-md-8">
        <img class="img-responsive" src="<?= RACINE_SITE ?>photo/<?= $photo ?>" alt="">
    </div>

    <div class="col-md-4">
        <h3>Project Description</h3>
        <p><?= $description ?></p>
        <h3>Project Details</h3>
        <ul>
            <li><?= $date_arrivee ?></li>
            <li> <?= $date_depart ?></li>
            <li> <?= $adresse ?></li>
            <li> <?= $prix ?></li>
        </ul>
    </div>

</div>
<!-- /.row -->

<!-- Related Projects Row -->
<div class="row">

    <div class="col-lg-12">
        <h3 class="page-header">Related Projects</h3>
    </div>
<?php foreach($suggestions as $valeur) : ?>
    <div class="col-sm-3 col-xs-6">
        <a href="#">
            <img class="img-responsive portfolio-item" src="<?= RACINE_SITE ?>photo/<?= $valeur['photo'] ?>" alt="">
        </a>
    </div>

    <div class="col-sm-3 col-xs-6">
        <a href="#">
            <img class="img-responsive portfolio-item" src="<?= RACINE_SITE ?>photo/<?= $valeur['photo'] ?>" alt="">
        </a>
    </div>

    <div class="col-sm-3 col-xs-6">
        <a href="#">
            <img class="img-responsive portfolio-item" src="<?= RACINE_SITE ?>photo/<?= $valeur['photo'] ?>" alt="">
        </a>
    </div>

    <div class="col-sm-3 col-xs-6">
        <a href="#">
            <img class="img-responsive portfolio-item" src="<?= RACINE_SITE ?>photo/<?= $valeur['photo'] ?>" alt="">
        </a>
    </div>
<?php endforeach; ?>
</div>



<?php
$page="Fiche Produit";
require_once('inc/footer.inc.php');
?>
