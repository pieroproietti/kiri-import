<?php

require('.auth.php');
require('postSlug.php');

$pdo = new PDO($cnn, $user, $pass);
//$sql="SELECT DISTINCT marchio_id, marchio FROM `tablin`
      //WHERE marchio_id>1
      //ORDER BY marchio ASC";

//$sql="SELECT max(marchio_id) marchio_id, marchio FROM `tablin` GROUP BY marchio";

$sql="SELECT marchio_id, marchio FROM `tablin` GROUP BY marchio_id, marchio";

$count=0;
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  if (isFoundInLp($row['marchio_id'])){
    $marchioId=$row['marchio_id'];
    $marchio=$row['marchio'];

    $aId=brandCreate($pdo,$row);
    $parentId=$aId['0'];
  }

}

function isFoundInLp($marchioId){
  global $pdo;
  $isFound=false;
  $sql = "SELECT marchio_id FROM lp WHERE marchio_id='$marchioId'";
  $stml = $pdo->prepare($sql);
  $stml->execute();
  if($stml=$pdo->query($sql)){
    if ($stml->rowCount() >0){
      $isFound=True;
    }
  }
  return $isFound;
}

function brandCreate($pdo, $row){
  $name= $row['marchio'];
  $slug= postSlug($row['marchio']).'-brand';
  $termGroup=2;
  $termId=termCreate($pdo, $name, $slug, $termGroup);
  $termTaxonomyId=taxonomyCreate($pdo, $termId, 'berocket_brand', $name, 0, 0);

  $marchioId=$row['marchio_id'];
  $lineaId=NULL;
  $marchio=$row['marchio'];
  $linea=NULL;
  llAdd($pdo, $termId, $termTaxonomyId, $marchioId, $lineaId,$marchio, $linea);
  return [$termId,$termTaxonomyId];
}

function lineCreate($pdo,$row, $parentId){
  $name=$row['linea'];
  $slug= postSlug($row['linea'].'-tag'); //.'-in-'.$row['parent']);
  $term_group=2;
  $termId=termCreate($pdo, $name, $slug, $term_group);
  $termTaxonomyId=taxonomyCreate($pdo, $termId, 'product_tag', $name, $parentId, 0);

  $marchioId=$row['marchio_id'];
  $lineaId=$row['linea_id'];
  $marchio=$row['marchio'];
  $linea=$row['linea'];
  llAdd($pdo, $termId, $termTaxonomyId, $marchioId, $lineaId,$marchio, $linea);
  return [$termId,$termTaxonomyId];
}

function llAdd($pdo, $termId,$termTaxonomyId,$marchioId, $lineaId,$marchio, $linea){
  $sql="INSERT INTO ll (term_id, term_taxonomy_id,marchio_id,linea_id, marchio, linea) VALUES (
    $termId,
    $termTaxonomyId,
    '$marchioId',
    '$lineaId',
    '$marchio',
    '$linea'
  )";
  echo $sql ."\n\r";
  $stml = $pdo->prepare($sql);
  $stml->execute();
}

function termCreate($pdo, $name, $slug, $term_group){
  $sql="INSERT INTO `wp_terms` (`name`, `slug`, `term_group`) VALUES
      (
        '$name', -- name
        '$slug', -- slug
        $term_group -- term_group
      )";
      //echo $sql;
  $stml = $pdo->prepare($sql);
  $stml->execute();

  $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
  $stml->execute();
  $last_inserted= $pdo->lastInsertId();
  return $last_inserted;
}

function taxonomyCreate($pdo, $term_id, $taxonomy, $description, $parent, $count){
  $sql="INSERT INTO `wp_term_taxonomy` (`term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
    (
      $term_id, -- term_id
      '$taxonomy', -- taxonomy
      '$description', -- description
      $parent, -- parent
      $count -- count
    )";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $last_inserted= $pdo->lastInsertId();
    return $last_inserted;
}
