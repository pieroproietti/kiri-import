<?php

require '.auth.php';

echo "tagsLinks: ";

$pdo = new PDO($cnn, $user, $pass);
$sql = 'SELECT lp.post_id object_id, lc.term_taxonomy_id term_taxonomy_id
      FROM lp, lc
      WHERE lp.categoria_id=lc.categoria_id AND lp.sotto_categoria_id=lc.sotto_categoria_id;';

$count = 0;
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
    ++$count;
    $objectId = $row['object_id'];
    $termTaxonomyId = $row['term_taxonomy_id'];
    $termOrder = 0;
    termRelationshipsAdd($pdo, $objectId, $termTaxonomyId, $termOrder);
    echo ".";
}

echo "\n\rtags lincati: ".$count."\n\r";


function termRelationshipsAdd($pdo, $objectId, $termTaxonomyId, $termOrder)
{
    $sql = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES
  (
    $objectId,
    $termTaxonomyId,
    $termOrder
  )";
//    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}
