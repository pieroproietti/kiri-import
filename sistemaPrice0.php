<?php
// https://gist.github.com/klihelp/7810337#file-wc-pagination-functions-php

require '../wp-blog-header.php';
require('.auth.php');

echo "inizio!\n";
$count=0;

$pdo = new PDO($cnn, $user, $pass);

// Cancello gli zero in sell_price
$sql="SELECT meta_id, post_id, meta_key, meta_value + 0 AS meta_value
      FROM wp_postmeta
      WHERE meta_key='_sale_price'
      AND meta_value + 0 = 0";
$stml = $pdo->prepare($sql);
$stml->execute();
echo "Rimuove 0 in _sale_price\n";
$count=0;
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $post_id=$row['post_id'];
  delete_post_meta( $post_id, '_sale_price');
  $count++;
}
echo $count ."\n";


// Cancello gli zero in price
$sql="SELECT meta_id, post_id, meta_key, cast(meta_value AS DECIMAL) meta_value
      FROM wp_postmeta
      WHERE meta_key='_price'
      AND meta_value + 0 = 0";

$stml = $pdo->prepare($sql);
$stml->execute();
$count=0;
echo "Rimuove 0 in _price\n";
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $count++;
  $post_id=$row['post_id'];
  update_post_meta( $post_id, '_regular_price','0');
  delete_post_meta( $post_id, '_price','');
}
echo $count ."\n";
