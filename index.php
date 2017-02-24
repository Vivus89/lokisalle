<?php
require_once('inc/init.inc.php');


// traitement pour récupérer toutes les catégories
$resultat = $pdo -> query("SELECT DISTINCT categorie FROM salle"); 
$categorie = $resultat -> fetchAll(PDO::FETCH_ASSOC); 
// Grâce à fetchAll(), $categorie est un array multidimentionnel avec les infos de chaque categorie. A l'indice categorie, je trouve le nom de ma categorie. 

// debug($categorie);

$resultat = $pdo -> query("SELECT DISTINCT ville FROM salle"); 
$ville = $resultat -> fetchAll(PDO::FETCH_ASSOC); 

$req = $pdo -> query(
"SELECT * FROM salle 
LEFT JOIN produit 
ON salle.id_salle = produit.id_salle"
);
$resultat = $req ;
 $produits = $resultat -> fetchAll(PDO::FETCH_ASSOC);
debug($produits); 
// Traitement pour récupérer tous produits par catégorie (ou par default tous les produits du site)
if(isset($_GET['categorie']) && $_GET['categorie'] != ''){
	$resultat = $pdo -> prepare("SELECT * FROM salle WHERE categorie = :categorie");
	$resultat -> bindParam(':categorie', $_GET['categorie'], PDO::PARAM_STR);
	$resultat -> execute(); 
	
	if($resultat -> rowCount() > 0){
		$produits = $resultat -> fetchAll(PDO::FETCH_ASSOC);
	}
	else{
		$resultat = $pdo -> query("SELECT * FROM salle"); 
		$produits = $resultat -> fetchAll(PDO::FETCH_ASSOC);
		// Si on est dans ce ELSE cela signifie que notre requête n'a rien trouvé concernant cette catégorie... oupsss ! L'utilisateur a certainement modifié l'URL (cas exeptionnel entre l'arrivée sur cette page et le clic, on a plus de stock dans cette catégorie)
		// Dans ce cas, on peut soit recharger la page, soit rediriger vers une 404, soit effectuer une requête générique avec tous les produits
	}
}
else{
	$resultat = $pdo -> query("SELECT * FROM salle"); 
	$produits = $resultat -> fetchAll(PDO::FETCH_ASSOC);
	// On est dans ce ELSE, s'il n'y a pas de paramètre catégorie dans l'URL (quand on arrive sur cette page) ou alors si le paramètre catégorie est vide. 
}
// debug($produits);
// Qu'il y ait une catégorie dans l'URL ou pas je sors de cette condition avec $produit étant un array multidimentionnel avec les infos de plusieurs produits. 
$page="Accueil";
require_once('inc/header.inc.php');

?>


//-----------------------------------------------------------------------------

  <!-- Page Content -->
    <div class="container">

        <div class="row">

            <div class="col-md-3">
                <p class="lead">Shop Name</p>
                <?php foreach($categorie as $valeur) : ?>
                <div class="list-group">
                    <a href="?categorie=<?= $valeur['categorie'] ?>" class="list-group-item"><?= $valeur['categorie'] ?></a>
                    
                </div>
            </div>
            <?php endforeach; ?>
            <div class="col-md-9">

                <div class="row carousel-holder">

                    <div class="col-md-12">
                    
                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                            </ol>
                            
                            <div class="carousel-inner">
                            <?php
                            $counter = 1;
								$resultat = $pdo->query('SELECT * FROM salle');
								while ($salle = $resultat->fetch())
								{
									?>
                                <div class="item <?php if($counter <= 1){echo " active"; } ?>">
                                    <img class="slide-image" src="<?= RACINE_SITE ?>photo/<?= $salle['photo'] ?>" alt="">
                                </div>
                                <?php
								    $counter++;
								    }
								   
								?>  
                            </div>
                      
                            <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                        
                    </div>

                </div>
<?php foreach($produits as $valeur) : ?>
                <div class="row">

                    <div class="col-sm-4 col-lg-4 col-md-4">
                    
                        <div class="thumbnail">
                            <img src="<?= RACINE_SITE ?>photo/<?= $valeur['photo'] ?>" alt="">
                            <div class="caption">
                                <h4 class="pull-right"><?= $valeur['prix'] ?></h4>
                                <h4><a href="#"><?= $valeur['titre'] ?></a>
                                </h4>
                                <p><?= substr($valeur['description'], 0, 40) ?>...</p>
                            </div>
                            <div class="ratings">
                                <p class="pull-right">15 reviews</p>
                                <p>
                                    <span class="glyphicon glyphicon-star"></span>
                                    <span class="glyphicon glyphicon-star"></span>
                                    <span class="glyphicon glyphicon-star"></span>
                                    <span class="glyphicon glyphicon-star"></span>
                                    <span class="glyphicon glyphicon-star"></span>
                                </p>
                            </div>
                        </div>
                    
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>

    </div>






<?php
require_once('inc/footer.inc.php');
?>