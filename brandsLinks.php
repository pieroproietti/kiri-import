<?php

require '.auth.php';
$pdo = new PDO($cnn, $user, $pass);
$sql = "
      SELECT lp.post_id object_id, ll.term_taxonomy_id term_taxonomy_id
      FROM lp, ll
      WHERE lp.marchio_id=ll.marchio_id;";

echo "\n\rLineeLinks:\r\n";

$count = 0;
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
    // inserisce in wp_term_relationships
    //object_id, term_taxonomy_id, term_order
    ++$count;

    $objectId = $row['object_id'];
    $termTaxonomyId = $row['term_taxonomy_id'];
    $termOrder = 0;
    termRelationshipsAdd($objectId, $termTaxonomyId, $termOrder);
    echo '.';
}

echo 'Categorie: record inseriti: '.$count."\n\r";

function termRelationshipsAdd($objectId, $termTaxonomyId, $termOrder)
{
    global $pdo;
    $sql = "
          INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES
          (
              $objectId,
              $termTaxonomyId,
              $termOrder
          )";

    $stml = $pdo->prepare($sql);
    $stml->execute();
}
