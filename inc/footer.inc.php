			</div>
        </section>

        <footer>
			<div class="conteneur">
				<?= date('Y') ?> - Tous droits reservés.
			</div>
        </footer>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script
        src="https://code.jquery.com/jquery-3.1.1.js"
        integrity="sha256-16cdPddA6VdVInumRGo6IbivbERE8p7CQR3HzTBuELA="
        crossorigin="anonymous"></script>
        <script src="bootstrap/js/bootstrap.js"></script>

        <script>
        	var champsPays =  document.getElementById("pays")
        	champsPays.addEventListener("change",ajax);

        	function ajax(){
        		var xhttp = new XMLHttpRequest(); // instanciation de l'objet XMLHttpRequest.
        			var file = "ajax.php";
        			var valeur = champsPays.options[champsPays.selectedIndex].value;
        			console.log(valeur);
        			var parametres = "pays="+valeur;
        			console.log(parametres);

        		xhttp.open("POST",file,true);
        		xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded"); // cette ligne est obligatoire en mode post.
        		xhttp.onreadystatechange = function (){


        			console.log(xhttp);
        			console.log(xhttp.responseText);
        			if(xhttp.readyState==4 && xhttp.status ==200 ){

        				console.log(xhttp.responseText);

        				var result = JSON.parse(xhttp.responseText);
        				console.log(result);
        				document.getElementById("ville").innerHTML=result.resultat;
        			}

        		}
        		xhttp.send(parametres);
        	}

        </script>

    </body>
</html>
