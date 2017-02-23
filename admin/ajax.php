<?php
$tab = array();
$tab['resultat']="";
if(!empty($_POST['pays']))
{
  if($_POST['pays']=='France')
  {
    $tab['resultat']="<option>Paris</option>
    <option>marseille</option>

    ";
  }
elseif ($_POST['pays']=='Italie') {
  $tab['resultat']="<option>Rome</option>
  <option>Milan</option>

  ";

  }
  elseif ($_POST['pays']=='Espagne') {
    $tab['resultat']="<option>barcelone</option>
    <option>Madrid</option>

    ";

    }

}
echo json_encode($tab);
