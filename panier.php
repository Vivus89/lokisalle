<?php

require_once('inc/init.inc.php');


$page= 'panier';

require_once('inc/header.inc.php');

?>
<?php
//Traitement pour vider le panier
if (isset($_GET['action']) && $_GET['action'] == 'vider') {
	unset($_SESSION['reservation']);
}

//Si l'action de vider le reservation est demandée dans l'URL alors on unset() la partie reservation de la session. Si l'utilisateur etait connecté, il reste connecté car la partie membre de SESSION existe toujours.

//Traitement pour supprimer un produit du reservation
if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
	if (isset($_GET['id_produit']) && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
		retirerProduit ($_GET['id_produit']);
	}
}
// Si une action de supprimer un produit du reservation est demandée dans l'URL on vérifie que l'id est bien présent, non vide et qu'il correspond bien a une valeur numérique.
//Dans ce cas, on éxecute une fonction retirerProduit() qui va supprimer le produit de SESSION['reservation']

//TRAITEMENT POUR INCREMENTER UN PRODUIT
//Je peux incrémenter tant qu'il y a du stock. je dois donc aller chercher le stock dispo pour ce produit.

if (isset($_GET['action']) && $_GET['action'] == 'incrementation') {
	if (isset($_GET['id_produit']) && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {

		//S'il y a une action d'incrémentation demandée dans l'url et que l'id est correct (non vide, et numérique), on va chercher dans la BDD le stock disponible pour ce produit.

		$resultat = $pdo -> prepare("SELECT stock FROM produit WHERE id_produit = :id_produit");
		$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
		$resultat -> execute();

		if ($resultat -> rowCount() > 0) {// Si le produit existe bien, dans la BDD, je peux comparer son stock avec le stock actuellement dans le reservation, et ainsi ajouter une unitée au reservation si disponible. Pour ce faire il me faut l'emplacement du produit dans mon array reservation, array_search () me permet de le trouver.
			$produit = $resultat -> fetch(PDO::FETCH_ASSOC);

			debug($produit);

			$position = array_search($_GET['id_produit'], $_SESSION['reservation']['id_produit']);
			if ($position !== FALSE) {
				if ($produit['stock'] >= $_SESSION['reservation']['date_arrivee'][$position] +1 ){
					$_SESSION['reservation']['quantite'][$position] ++;
					header('location:panier.php');
				}
				else{// Si le stock dispo n'est pas supérieur a la quantité actuelle dans le reservation , plus une unitée, on préviens que le stock est limité et donc on n'incrémente pas.
					$msg .= '<div class="erreur">Le stock du produit' . $_SESSION['reservation']['titre'][$position] . ' est limité ! </div>';
				}
			}

		}
	}
}
//TRAITEMENT POUR LA DECREMENTATION
// Attention, on peut décrémenter la quantité d'un produit dans le reservation tant que la quantité est supérieure a 0 .Ensuite il est préférable de supprimer entièrement la ligne.
if (isset($_GET['action']) && $_GET['action'] == 'decrementation') {
	if (isset($_GET['id_produit']) && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
		// Pour agir sur la quantité du produit dans le reservation, il nous faut son emplacement dans le reservation, Il nous faut son emplacement dans le reservation. Pour ce faire , array_search() nous retourne sa position.

		$position = array_search($_GET['id_produit'], $_SESSION['reservation']['id_produit']);

		if ($position !== FALSE) {
			if ($_SESSION['reservation']['date_arrivee'][$position] > 1) {
				//Si le produit existe dans le reservation, et que sa quantité est supérieure a 1, je peux retirer une unité

				$_SESSION['reservation']['date_arrivee'][$position] --;
			}
			else{//Si sa quantité est inférieure a 1 dans ce cas, je supprime tout simplement la ligne.
				retirerProduit($_GET['id_produit']);
				header('location:panier.php');
			}
		}
	}
}
//TRAITEMENT DU PAIEMENT:
 //Vérifie que le stock est toujours dispo (boucle)
 	//Si c'est NON, il existe deux cas de figure :
		//stock inférieur a la demande : remplace la quantité
		//Le stock est nul : Retire le produit
	//Enregistrer dans la BDD les infos de la commande,pour  chaque commande on modifie le stock et on enregistre les infos dans details commande.
	//Envoyer un email de confirmation a nos clients $_SESSION ['membre']['email']

	if(isset($_POST['paiement']) && !empty($_SESSION['reservation']['id_produit'])){
		for ($i=0; $i < sizeof ($_SESSION['reservation']['id_produit']) ; $i++) {
			$id_produit = $_SESSION['reservation']['id_produit'][$i];
			$resultat = $pdo -> query("SELECT stock FROM produit WHERE id_produit = $id_produit ");

			$produit = $resultat -> fetch(PDO::FETCH_ASSOC);


			if($produit['stock'] < $_SESSION['reservation']['quantite'][$i]){
				$msg .='<div class="erreur">' . $_SESSION['reservation']['titre'][$i] . ': stock restant :' . $produit['stock'] . 'Quantité demandée :' . $_SESSION['reservation']['quantite'][$i] . '</div>';

			if ($produit['stock'] > 0) {
				$msg .= '<div class="erreur">Le stock du produit' . $_SESSION['reservation']['titre'][$i] . 'n\'est pas suffisant, votre commande a été modifié. Veuillez vérifier la nouvelle quantité avant de valider ! </div>';
			$_SESSION['reservation']['date_arrivee'][$i] = $produit['stock'];
			}
			else{
				$msg .= '<div class="erreur">Le produit ' . $_SESSION['reservation']['titre'][$i] . 'n\'est plus disponible . Nous avons supprimé ce produit de votre commande. </div>';
				retirerProduit($_SESSION['reservation']['id_produit'][$i]);
				$i--;
				}
			}

		}

	if (empty($msg)) {//Si $msg est vide, cela signigie qu'il a pas de probleme de stcok on peut poursuivre le traitement pour le paiement.
		//Enregistrement dans la BDD
		//Envoyer un email
		// Supprimer le reservation

		// Enregistrement dans la table commande
	$id_membre = $_SESSION['membre']['id_membre'];
	$montant = montantTotal();
		$resultat = $pdo -> exec(" INSERT INTO commande (id_membre, montant, date_enregistrement, etat) VALUES ('$id_membre', $montant, NOW(), 'en cours de traitement' )");

		$id_commande = $pdo -> lastInsertId();

		//Modification des stocks dans la table produit et enregistrement dans la table details_commande (boucle car opération a effectuer pour chaque reservation)

		for ($i = 0; $i < sizeof($_SESSION['reservation']['id_produit']) ; $i++) {

			$id_produit = $_SESSION['reservation']['id_produit'][$i];
			$date_arrivee = $_SESSION['reservation']['date_arrivee'][$i];
			$prix= $_SESSION['reservation']['prix'][$i];

			// enregistrement des details
			$resultat = $pdo -> exec("INSERT INTO details_commande (id_commande, id_produit, quantite, prix) VALUES ('$id_commande', '$id_produit', '$quantite', '$prix') ");

			//modification du stock
			$resultat = $pdo -> exec("UPDATE produit set stock = (stock - $date_arrivee) ");
		}

		unset($_SESSION['reservation']);
		$msg .= '<div class="validation">Félicitation ! Votre nyméro de commande est : ' . $id_commande . '</div>';
		// mail(); //Cf le fichier formulaire5.php dans POST.
	}
}
?>

<h1>reservation</h1>
<?= $msg ?>
<table border="1" style="border-collapse: collapse; cellpadding: 7;">
	<tr>
		<th colspan="7">reservation <?= (quantiteReservation()) ? quantiteReservation() . ' Produit(s) dans le reservation ' : ''?></th>
	</tr>
	<tr>
		<th>Photo</th>
		<th>Titre</th>
		<th>date_arrivee</th>
		<th>date_depart</th>
		<th>Prix unitaire</th>
		<th>Total</th>
		<th>Supprimer</th>
	</tr>
	<?php if(empty($_SESSION['reservation']['id_produit'])) : ?>
	<tr>
		<td colspan="7">Votre reservation est vide</td>
	</tr>
	<?php else : ?>
		<?php for($i = 0; $i < count($_SESSION['reservation']['id_produit']); $i++) : ?>
			<tr>
				<td><img src="<?= RACINE_SITE ?>photo/<?= $_SESSION['reservation']['photo'][$i] ?>" height="30" /></td>
				<td><?= $_SESSION ['reservation']['titre'][$i] ?></td>
				<td>
				<a href="?action=decrementation&id_produit=<?= $_SESSION['reservation']['id_produit'][$i] ?>"><img src="img/moins.png" width="15"></a>

				<span style="padding: 3px; border: solid 1px black; text-align: center; width: 20px; display: inline-block;">
				<?= $_SESSION ['reservation']['quantite'][$i] ?>
				</span>

				<a href="?action=incrementation&id_produit=<?= $_SESSION['reservation']['id_produit'][$i] ?>"><img src="img/plus.png" width="15"></a>
				</td>
				<td><?= $_SESSION ['reservation']['prix'][$i] ?></td>
				<td><?= $_SESSION ['reservation']['prix'][$i] * $_SESSION ['reservation']['quantite'][$i] ?> €</td>
				<td>
					<a href="?action=supprimer&id_produit=<?= $_SESSION['reservation']['id_produit'][$i] ?>"><img src="img/delete.png" height="22" /></a>
				</td>

			</tr>

	<?php endfor; ?>
	<tr>
		<td colspan="4">Montant Total</td>
		<td colspan="2"><?= montantTotal() ?> €</td>
	</tr>
	<tr>
		<!-- si user connecté btn paiement-->
		<?php if (userConnecte()) : ?>
		<td colspan="6">
			<form method="post" action="">
			<input type="hidden" name="montant" value="<?= montantTotal() ?>"/>

			<input type="submit" value="payer" name="paiement"/>

			</form>
		</td>
		<!-- sinon btn connexion -->
	</tr>
	<?php else : ?>
		<tr>
			<td colspan="6">Veuillez vous <a href="connexion.php">Connecter</a>ou vous <a href="inscription.php">inscrire</a> pour payer votre reservation</td>
		</tr>
	<?php endif; ?>

	</tr>
	<tr>
		<td colspan="6"><a href="?action=vider">Vider votre reservation</a></td>
	</tr>
<?php endif; ?>
</table>


<?php
require_once('inc/footer.inc.php');
?>
