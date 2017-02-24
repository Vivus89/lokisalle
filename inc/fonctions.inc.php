<?php

// Fonction Debug (amélioration du print_r())
function debug($arg){
	echo '<div style="color: white; font-weight: bold; padding: 10px; background:#' . rand(111111, 999999) . '">';
	$trace = debug_backtrace(); // debug_backtrace me retourne des infos sur l'emplacement où est EXECUTER cette fonction. Nous retourne un array multidimentionnel.
	echo 'Le debug a été demandé dans le fichier : ' . $trace[0]['file'] . ' à la ligne : ' . $trace[0]['line'] . '<hr/>';

	echo '<pre>';
	print_r($arg);
	echo '</pre>';

	echo '</div>';
}

// Fonction pour voir si l'utilisateur est connecté
function userConnecte(){
	if(isset($_SESSION['membre'])){
		return TRUE;
	}
	else{
		return FALSE;
	}
	// S'il existe une session/membre, c'est que l'utilisateur est connecté. Je retourne TRUE, sinon, je retourne ELSE.
}

// Fonction pour voir si l'utilisteur est admin
function userAdmin(){
	if(userConnecte() && $_SESSION['membre']['statut'] == 1){
		return TRUE;
	}
	else{
		return FALSE;
	}
	// Si l'utilisateur est connecté et que son statut c'est "1" alors je retourne TRUE. Sinon je retourne FALSE.
}

// Fonction pour créer un panier

function creationReservation(){
	if(!isset($_SESSION['reservation'])){
		$_SESSION['reservation'] = array();
		$_SESSION['reservation']['id_produit'] = array();
		$_SESSION['reservation']['date_arrivee'] = array();
		$_SESSION['reservation']['date_depart'] = array();
		$_SESSION['reservation']['photo'] = array();
		$_SESSION['reservation']['titre'] = array();
		$_SESSION['reservation']['prix'] = array();
	}
	return true;
}

// Fonction pour ajouter un produit au panier
function ajouterProduit($id_produit, $date_arrivee, $date_depart, $titre, $photo, $prix){
	creationReservation();

	// Nous devons vérifier que le produit en cours d'ajout n'éxiste pas déjà dans notre panier :
	$positionPdt = array_search($id_produit, $_SESSION['reservation']['id_produit']);
	//array_seach est une fonction qui me permet de chercher une info dans un array. Si elle trouve, elle me retourne son emplacement sinon, elle me retourne FALSE.

	if($positionPdt !== FALSE){
		$_SESSION['reservation']['date_arrivee'][$positionPdt] += $date_arrivee;
	}
	else {
		$_SESSION['reservation']['date_arrivee'][] = $date_arrivee;
		$_SESSION['reservation']['id_produit'][] = $id_produit;
		$_SESSION['reservation']['photo'][] = $photo;
		$_SESSION['reservation']['titre'][] = $titre;
		$_SESSION['reservation']['prix'][] = $prix;
		$_SESSION['reservation']['date_depart']= $date_depart;
	}
}

// Fonction pour calculer le nombre de produit dans le panier

function quantiteReservation(){
	$date_arrivee = 0;
	if(isset($_SESSION['reservation']) && !empty($_SESSION['reservation']['date_arrivee'])){
		for($i = 0; $i < count($_SESSION['reservation']['date_arrivee']); $i++){
			$date_arrivee += $_SESSION['reservation']['date_arrivee'][$i];
		}
	}
	if($date_arrivee != 0){
		return $date_arrivee;
	}
}

// Fonction pour calculer le montant total d'un panier
function montantTotal(){
	$total = 0;

	if(isset($_SESSION['reservation']) && !empty($_SESSION['reservation']['prix'])){
		for($i=0; $i < count($_SESSION['reservation']['prix']); $i++){
			$total += $_SESSION['reservation']['prix'][$i] * $_SESSION['reservation']['date_arrivee'][$i];
		}
	}

	if($total != 0){
		return $total;
	}
}

// Fonction pour retirer un produit du tableau :
function retirerReservation($id_produit){

	$position_pdt_a_supprimer = array_search($id_produit, $_SESSION['reservation']['id_produit']);
	// Je cherche la position du produit à supprimer grâce à son id dans la liste de tous les id des produit du reservation.

	if($position_pdt_a_supprimer !== FALSE){
		array_splice($_SESSION['reservation']['id_produit'], $position_pdt_a_supprimer, 1);
		array_splice($_SESSION['reservation']['prix'], $position_pdt_a_supprimer, 1);
		array_splice($_SESSION['reservation']['date_arrivee'], $position_pdt_a_supprimer, 1);
		array_splice($_SESSION['reservation']['date_depart'], $position_pdt_a_supprimer, 1);
		array_splice($_SESSION['reservation']['titre'], $position_pdt_a_supprimer, 1);
		array_splice($_SESSION['reservation']['photo'], $position_pdt_a_supprimer, 1);
	}
}
