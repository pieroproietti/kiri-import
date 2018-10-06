<?php

require '../wp-config.php';
require('.auth.php');

$pdo = new PDO($cnn, $user, $pass);

if (!$link=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    die('Could not connect: '.mysqli_error($link));
}
if (!mysqli_select_db($link,DB_NAME)) {
    die('Could not connect: '.mysqli_error($link));
}

$sql="UPDATE wp_term_taxonomy SET count = (
        SELECT COUNT(*) FROM wp_term_relationships rel
        LEFT JOIN wp_posts po ON (po.ID = rel.object_id)
        WHERE
            rel.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id AND
              wp_term_taxonomy.taxonomy NOT IN ('link_category') AND
              po.post_status IN ('publish', 'future')
      )";

$stml = $pdo->prepare($sql);
$stml->execute();
