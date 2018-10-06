<?php

require('.auth.php');

echo "inizio!\n";
$count=0;

$pdo = new PDO($cnn, $user, $pass);
$sql="SELECT ID, post_content, post_title, post_excerpt FROM wp_posts WHERE 1";
$stml = $pdo->prepare($sql);
$stml->execute();
echo $sql ."\n";
$apostrofo="'";
$slashApostrofo="\'";
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $id=$row['ID'];
  $postContent=str_replace($slashApostrofo, $apostrofo, $row['post_content']);
  $postTitle=str_replace($slashApostrofo, $apostrofo, $row['post_title']);
  $postExcerpt=str_replace($slashApostrofo, $apostrofo, $row['post_excerpt']);
  postUpdate($id, $postContent, $postTitle, $postExcerpt);
  //echo $postContent."\n";
  echo $count++ . "\n";
}
echo "fine!\n";


function postUpdate($id,$postContent,$postTitle,$postExcerpt){
  echo "postUpdate!\n";
  global $pdo;
  $sql="UPDATE wp_posts
        SET post_content=" .$pdo->quote($postContent) .",
            post_title=" .$pdo->quote($postTitle) .",
            post_excerpt=" .$pdo->quote($postExcerpt)."
        WHERE ID=$id;";
        $stml = $pdo->prepare($sql);
        $stml->execute();
  echo $sql ."\n";
}
