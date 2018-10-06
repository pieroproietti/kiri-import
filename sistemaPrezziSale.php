<?php
// https://gist.github.com/klihelp/7810337#file-wc-pagination-functions-php

require '../wp-blog-header.php';
require('.auth.php');

echo "inizio!\n";
$count=0;

$pdo = new PDO($cnn, $user, $pass);
// SELECT meta_id, post_id, meta_key, meta_value + 0 AS meta_value FROM wp_postmeta WHERE post_id=13835 AND (meta_key='_regular_price' OR meta_key='_price' OR meta_key='_sale_price')

// Controllo prodotti con zero in price
$sql="SELECT meta_id, post_id, meta_key, meta_value + 0 AS meta_value
      FROM wp_postmeta
      WHERE (meta_key='_regular_price' AND meta_value='0')";
$stml = $pdo->prepare($sql);
$stml->execute();
echo "Controllo se esiste _sale_price\n";
$count=0;
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $post_id=$row['post_id'];
  $sql="SELECT meta_id, post_id, meta_key, meta_value + 0 AS meta_value
        FROM wp_postmeta
        WHERE meta_key='_sale_price' AND post_id=$post_id";

  $stmlSale = $pdo->prepare($sql);
  $stmlSale->execute();
  while ($rowSale = $stmlSale->fetch(PDO::FETCH_ASSOC)) {
    if($rowSale['meta_value']>0){
      echo "<a href='http://www.kiriprofumi.it/banco/wp-admin/post.php?post=" . $post_id . "&action=edit'>" . $post_id . "</a>\n";
      break;
    }
  $count++;
  }
}
echo "record: " . $count ."\n";
