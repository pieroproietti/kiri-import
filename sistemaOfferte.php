<?php

require '../wp-blog-header.php';
require('.auth.php');

echo "inizio!\n";
$count=0;
$pdo = new PDO($cnn, $user, $pass);


$sql="SELECT meta_id, post_id, meta_key, cast(meta_value AS DECIMAL(10,2)) regular_price
      FROM wp_postmeta
      WHERE meta_key='_regular_price'";
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $regular_price=0;
  $sale_price=0;

  $post_id=$row['post_id'];
  $regular_price=$row['regular_price'];
  if($regular_price>0){
    $sql="SELECT meta_id, post_id, meta_key, cast(meta_value AS DECIMAL(10,2)) sale_price
          FROM wp_postmeta
          WHERE meta_key='_sale_price' AND post_id=" . $post_id;
    $stmlSale = $pdo->prepare($sql);
    $stmlSale->execute();
    while ($rowSale = $stmlSale->fetch(PDO::FETCH_ASSOC)) {
      $sale_price=$rowSale['sale_price'];
      echo "post_id: $post_id regular_price: $regular_price sale_price: $sale_price";
      if($sale_price==0){
        echo " Non in offerta. Non passa perchè rimuove _sale_price meta_value \n";
      }elseif ($sale_price>$regular_price) {
        echo " Offerta errata\n";
      }elseif ($sale_price==$regular_price) {
        echo  " Rimozione offerta\n";
        sistenaOfferta($post_id, $regular_price);
      }else {
        echo " Offerta OK\n";
    }
    $count++;
  }
  } else {
    // regular_price=0
    echo "post_id: $post_id regular_price: $regular_price Imposto sale_price=''\n";
    update_post_meta( $post_id, '_price','');
  }
}


echo $count ."\n";
echo "fine!\n";


function sistenaOfferta($post_id, $regular_price){
  global $pdo;
  $sql="SELECT meta_id, post_id, meta_key, cast(meta_value AS DECIMAL(10,2)) sale_price
        FROM wp_postmeta
        WHERE meta_key='_sale_price' AND post_id=$post_id
        ORDER BY meta_id";
  $stml = $pdo->prepare($sql);
  $stml->execute();

  while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
    $sale_price=$row['sale_price'];
    if($regular_price==$sale_price)
      delete_post_meta( $post_id, '_sale_price');
  }
}
