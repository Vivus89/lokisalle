<?php
require_once('../inc/init.inc.php');
if(!userAdmin()){
	header('location:../connexion.php');
}
if ($_POST){

debug($_POST);
    if(isset($_GET['action']) && $_GET['action'] == 'modifier'){

      $resultat = $pdo -> prepare("UPDATE  commande set etat =:etat WHERE id_commande =:id_commande");

      $resultat -> bindParam(':id_commande', $_POST['id_commande'], PDO::PARAM_INT);
				$resultat -> bindParam(':etat', $_POST['etat'], PDO::PARAM_STR);
		}




    if($resultat -> execute()){
debug ($resultat);
      $msg .= '<div class="validation">Le membre N°' . $last_id . ' a bien été enregistré</div>';
    }



}

if(isset($_GET['action']) && $_GET['action'] == 'affichage'){ // Si une action pour afficher les produits est demandée dans l'URL
	// Alors on récupère toutes les infos de tous les produits
	$resultat = $pdo -> query("SELECT * FROM commande");

	// On affiche ces infos via des boucles, dans un tableau HTML (stocké dans une variable $contenu
	$contenu .= '<table border="1">';
	$contenu .= '<tr>';
	for($i = 0; $i < $resultat -> columnCount(); $i++){
		$meta = $resultat -> getColumnMeta($i);
		$contenu .= '<th>' . $meta['name'] . '</th>';
	}
	$contenu .= '<th colspan="4">Actions</th>';
	$contenu .= '</tr>';
	while($commandes = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu .= '<tr>';
		foreach($commandes as $indice => $valeur){
			// Lorsqu'on parcourt un enregistrement on souhaite afficher la photo dans une balise IMG et non en texte. On fait donc une condition dans le foreach :
			if($indice == 'photo'){
				$contenu .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="100"/></td>';
			}
			else{
				$contenu .= ' <td>' . $valeur . '</td>';
			}
		}

		// En face de chaque enregistrement on ajoute trosi actions : Modifie voir produit et voir membre en GET et précisant l'ID de chaque enregistrement.
		$contenu .= '<td><a href="?action=modifier&id_commande='. $commandes['id_commande'] .'"><img src="' . RACINE_SITE . 'img/edit.png"/></a></td>';

    $contenu .= '<td><a href="?action=voir_produit">Voir produit</a></td>';
    $contenu .= '<td><a href="?action=voir_membre">Voir membre </a></td>';
    $contenu .= '</tr>';
	}
	$contenu .= '</table>';
}
if(isset($_GET['action']) && $_GET['action'] == 'voir_produit'){ // Si une action pour afficher les produits est demandée dans l'URL
	// Alors on récupère toutes les infos de tous les produits
	$resultat = $pdo -> query("SELECT p.id_produit, p.titre, p.photo ,c.quantite FROM produit p, details_commande c WHERE p.id_produit= c.id_produit");

	// On affiche ces infos via des boucles, dans un tableau HTML (stocké dans une variable $contenu
	$contenu2 .= '<table border="1">';
	$contenu2 .= '<tr>';
	for($i = 0; $i < $resultat -> columnCount(); $i++){
		$meta = $resultat -> getColumnMeta($i);
		$contenu2 .= '<th>' . $meta['name'] . '</th>';
	}

	$contenu2 .= '</tr>';
	while($commandes = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu2 .= '<tr>';
		foreach($commandes as $indice => $valeur){
			// Lorsqu'on parcourt un enregistrement on souhaite afficher la photo dans une balise IMG et non en texte. On fait donc une condition dans le foreach :
			if($indice == 'photo'){
				$contenu2 .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="100"/></td>';
			}
			else{
				$contenu2 .= ' <td>' . $valeur . '</td>';
			}
		}


    $contenu2 .= '</tr>';
	}
	$contenu2 .= '</table>';
}

if(isset($_GET['action']) && $_GET['action'] == 'voir_membre'){ // Si une action pour afficher les membres est demandée dans l'URL
	// Alors on récupère toutes les infos de tous les produits
	$resultat = $pdo -> query("SELECT m.id_membre, m.pseudo , m.nom, m.prenom, m.email, m.civilite , m.ville, m.code_postal, m.adresse
    FROM membre m , commande c , details_commande d
    WHERE c.id_membre = m.id_membre
    AND c.id_commande = d.id_commande");

	// On affiche ces infos via des boucles, dans un tableau HTML (stocké dans une variable $contenu2
	$contenu2 .= '<table border="1">';
	$contenu2 .= '<tr>';
	for($i = 0; $i < $resultat -> columnCount(); $i++){
		$meta = $resultat -> getColumnMeta($i);
		$contenu2 .= '<th>' . $meta['name'] . '</th>';
	}

	$contenu2 .= '</tr>';
	while($commandes = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu2 .= '<tr>';
		foreach($commandes as $indice => $valeur){
			// Lorsqu'on parcourt un enregistrement on souhaite afficher la photo dans une balise IMG et non en texte. On fait donc une condition dans le foreach :
			if($indice == 'photo'){
				$contenu2 .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="100"/></td>';
			}
			else{
				$contenu2 .= ' <td>' . $valeur . '</td>';
			}
		}


    $contenu2 .= '</tr>';
	}
	$contenu2 .= '</table>';
}



require_once('../inc/header.inc.php');
?>

<!-- Mon contenu HTML -->
<h1>Gestion commande</h1>
<ul>
	<!-- un lien ci-dessous (sous-menu) permettent de lancer 1 actions : Affichage de toutes les commandes  -->
	<li><a href="?action=affichage">Afficher les commandes</a></li>

</ul><hr/>
<?= $msg ?>
<?= $contenu ?>
<?= $contenu2 ?>
<?php if(isset($_GET['action']) &&  $_GET['action'] == 'modifier') :
?>
<?php
if(isset($_GET['id_commande']) && is_numeric($_GET['id_commande'])){

	$resultat = $pdo -> prepare("SELECT * FROM commande WHERE id_commande = :id_commande");
	$resultat -> bindParam(':id_commande', $_GET['id_commande'], PDO::PARAM_INT);
	if($resultat -> execute()){
		$commande_actuel = $resultat -> fetch(PDO::FETCH_ASSOC);
	}
}
$id_commande = (isset($commande_actuel)) ? $commande_actuel['id_commande'] : '';
$etat = (isset($commande_actuel)) ? $commande_actuel['etat'] : '';

$action = (isset($commande_actuel)) ? 'modifier' : 'ajouter';

?>


<form method ="post" action="">

  <label> ETAT: </label>
	<input type="hidden" name="id_commande" value="<?= $id_commande ?>"/>
  <select name="etat">
    <option>-- Selectionnez --</option>
    <option <?= ($etat == 'en cours de traitement') ? 'selected' : '' ?> value="en cours de traitement">en cours de traitement</option>
    <option <?= ($etat == 'envoyé') ? 'selected' : '' ?> value="envoyé">envoyé</option>
    <option <?= ($etat == 'livré') ? 'selected' : '' ?> value="livré">livré</option>
  </select><br/>
  <input type="submit" name="modifier" value="<?= $action ?>"/><br/>

</form>
<?php endif;?>
<?php
require_once('../inc/footer.inc.php');
?>
