<?php



require('.auth.php');

echo "inizio!\n";
$count=0;

$pdo = new PDO($cnn, $user, $pass);
$sql="SELECT ID, post_content
      FROM wp_posts
      WHERE post_type='product' AND
        ID IN(
              SELECT object_id FROM wp_term_relationships
              WHERE term_taxonomy_id IN(165,166,167) AND
                    term_taxonomy_id IN(SELECT term_taxonomy_id FROM wp_term_taxonomy
                                        WHERE taxonomy='product_cat' )
            )";

$stml = $pdo->prepare($sql);
$stml->execute();
echo $sql ."\n";
$cerca="<h4>Piramide olfattiva</h4>";
$sostituisci="<h4>Modo d'uso/Ingredienti</h4>";
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $id=$row['ID'];
  $postContent=str_replace($cerca, $sostituisci, $row['post_content']);
  postUpdate($id, $postContent);
  echo $count++ . "\n";
}
echo "fine!\n";


function postUpdate($id,$postContent){
  echo "postUpdate!\n";
  echo $postContent."\n";
  global $pdo;
  $sql="UPDATE wp_posts
        SET post_content=" .$pdo->quote($postContent) ."
        WHERE ID=$id;";
        $stml = $pdo->prepare($sql);
        $stml->execute();
  echo $sql ."\n";
}
