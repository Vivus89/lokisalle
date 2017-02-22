<?php
require_once('inc/init.inc.php');

// Redirection si l'utilisateur est déjà connecté
if(userConnecte()){
	header('location:profil.php');
}

//TRAITEMENT DE L'INSCRIPTION
if($_POST){
	debug($_POST);

	// Vérifications des infos pour le pseudo :
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#' , $_POST['pseudo']); // preg_match() est une fonciton qui nous permet de vérifier les caractères d'une chaîne de caractères. Le 1er arg : les caractères autorisés (expression régulière ou REGEX) et le 2eme arg : la chaine de caractères qu'on vérifie).
	// Cette fonction nous retourne soit TRUE soit FALSE.

	if(!empty($_POST['pseudo'])){
		if($verif_caractere){
			if(strlen($_POST['pseudo']) < 3 || strlen($_POST['pseudo']) > 20){
				$msg .= '<div class="erreur">Veuillez renseigner un pseudo de 3 à 20 caractères ! </div>';
			}
		}
		else{
			$msg .= '<div class="erreur">Pseudo : Caractères acceptés : A à Z, 0 à 9 et ".", "-" et "_" </div>';
		}
	}
	else{
		$msg .= '<div class="erreur">Veuillez renseigner un pseudo !</div>';
	}


	// Insertion du nouveau membre dans la BDD
	if(empty($msg)){ // Tout est OK, aucune erreur dans le formulaire si $msg est vide.
		// Avant d'insérer le nx membre on doit vérifier si le pseudo est disponible.
		$resultat = $pdo -> prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
		$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
		$resultat-> execute();

		if($resultat -> rowCount() > 0 ){ // Il y a au moins un résultat avec ce pseudo

			$msg .= '<div class="erreur">Ce pseudo ' . $_POST['pseudo'] . ' n\'est pas disponible, veuillez choisir un autre pseudo.</div>';

		}
		else{ // Tout est OK le pseudo est disponible on peut enregistrer le membre. Notons que nous aurions du vérifer la disponibilité de l'adresse email. En sachant que ce serait certainement une perte de MDP.

		$resultat = $pdo -> prepare("INSERT INTO membre (pseudo, mdp, nom, prenom, email, civilite, statut) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, 0)");

		//STR
		$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
		$mdp_crypte = md5($_POST['mdp']);
		// la fonction MD5() me permet de crypter une chaine de caractères selon le protocole MD5 (clé de hashage md5). C'est le plus simple, il en existe d'autres.
		$resultat -> bindParam(':mdp', $mdp_crypte , PDO::PARAM_STR);
		$resultat -> bindParam(':nom', $_POST['nom'], PDO::PARAM_STR);
		$resultat -> bindParam(':prenom', $_POST['prenom'], PDO::PARAM_STR);
		$resultat -> bindParam(':email', $_POST['email'], PDO::PARAM_STR);
		$resultat -> bindParam(':civilite', $_POST['civilite'], PDO::PARAM_STR);


		//$resultat -> execute();

		// Redirection vers accueil ou vers connexion.php
		//header('location:connexion.php');

		if($resultat -> execute()){
			header('location:connexion.php');
		}


		// $msg .= '<div class="validation">L\'inscription est réussie !</div>';

		}
	}
}

$pseudo = (isset($_POST['pseudo'])) ? $_POST['pseudo'] : '';
$prenom = (isset($_POST['prenom'])) ? $_POST['prenom'] : '';
$nom = (isset($_POST['nom'])) ? $_POST['nom'] : '';
$email = (isset($_POST['email'])) ? $_POST['email'] : '';
$civilite = (isset($_POST['civilite'])) ? $_POST['civilite'] : '';

// Ces lignes correspondent à des If() + Affectation de manière très contractée. C'est l'équivalent de :
//if(isset($_POST['pseudo'])){$pseudo = $_POST['pseudo'];}else{$pseudo = '';}


$page="Inscription";
require_once('inc/header.inc.php');
?>
<h1>Inscription</h1>
<style>
@import url(http://fonts.googleapis.com/css?family=Roboto);

/****** LOGIN MODAL ******/
.loginmodal-container {
padding: 30px;
max-width: 350px;
width: 100% !important;
background-color: #F7F7F7;
margin: 0 auto;
border-radius: 2px;
box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
overflow: hidden;
font-family: roboto;
}

.loginmodal-container h1 {
text-align: center;
font-size: 1.8em;
font-family: roboto;
}

.loginmodal-container input[type=submit] {
width: 100%;
display: block;
margin-bottom: 10px;
position: relative;
}

.loginmodal-container input[type=text], input[type=password] {
height: 44px;
font-size: 16px;
width: 100%;
margin-bottom: 10px;
-webkit-appearance: none;
background: #fff;
border: 1px solid #d9d9d9;
border-top: 1px solid #c0c0c0;
/* border-radius: 2px; */
padding: 0 8px;
box-sizing: border-box;
-moz-box-sizing: border-box;
}

.loginmodal-container input[type=text]:hover, input[type=password]:hover {
border: 1px solid #b9b9b9;
border-top: 1px solid #a0a0a0;
-moz-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.loginmodal {
text-align: center;
font-size: 14px;
font-family: 'Arial', sans-serif;
font-weight: 700;
height: 36px;
padding: 0 8px;
/* border-radius: 3px; */
/* -webkit-user-select: none;
user-select: none; */
}

.loginmodal-submit {
/* border: 1px solid #3079ed; */
border: 0px;
color: #fff;
text-shadow: 0 1px rgba(0,0,0,0.1);
background-color: #4d90fe;
padding: 17px 0px;
font-family: roboto;
font-size: 14px;
/* background-image: -webkit-gradient(linear, 0 0, 0 100%,   from(#4d90fe), to(#4787ed)); */
}

.loginmodal-submit:hover {
/* border: 1px solid #2f5bb7; */
border: 0px;
text-shadow: 0 1px rgba(0,0,0,0.3);
background-color: #357ae8;
/* background-image: -webkit-gradient(linear, 0 0, 0 100%,   from(#4d90fe), to(#357ae8)); */
}

.loginmodal-container a {
text-decoration: none;
color: #666;
font-weight: 400;
text-align: center;
display: inline-block;
opacity: 0.6;
transition: opacity ease 0.5s;
}

.login-help{
font-size: 12px;
}
</style>
<a href="#" data-toggle="modal" data-target="#login-modal">Login</a>
<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="loginmodal-container">
			<h1>Login to Your Account</h1><br>
<form method="post" action="">
	<?= $msg ?>
	<label>Pseudo :</label>
	<input type="text" name="pseudo" value="<?= $pseudo ?>"/><br/>

	<label>Mot de passe :</label>
	<input type="password" name="mdp"/><br/>

	<label>Nom :</label>
	<input type="text" name="nom" value="<?= $nom ?>"/><br/>

	<label>Prénom :</label>
	<input type="text" name="prenom" value="<?= $prenom ?>"/><br/>

	<label>Email :</label>
	<input type="text" name="email" value="<?= $email ?>"/><br/>

	<label>Civilité :</label>
	<select name="civilite">
		<option>-- Selectionnez -- </option>
		<option value="m" <?= ($civilite == 'm') ? 'selected' : '' ?>>Homme</option>
		<option value="f" <?= ($civilite == 'f') ? 'selected' : '' ?>>Femme</option>
	</select><br/>


	<input type="submit" value="Inscription" />
</form>
<div class="login-help">
<a href="#">Register</a> - <a href="#">Forgot Password</a>
</div>
</div>
</div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<?php
require_once('inc/footer.inc.php');
?>
