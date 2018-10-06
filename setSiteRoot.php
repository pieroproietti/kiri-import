<?php
if (count($argv) == 1){
  echo "uso: setSiteRoot --home --thesi --localhost "."\n\r";
  exit;
}

if ($argv[1]=="--home"){
  $site = 'http://pieroproietti.hopto.org';
  $url = '/kiri-dev';
  $blogname = 'kiri-dev';
}elseif ($argv[1]=="--thesi"){
  $site = 'http://kiri.sytes.net:8000';
  $url = '/kiri-dev';
  $blogname = 'kiri-dev';
}elseif ($argv[1]=="--localhost"){
  $site = 'http://localhost';
  $url = '/kiri-dev';
  $blogname = 'kiri-dev';
}else{
  echo "uso: setSiteRoot --home --thesi --kiki"."\n\r";
  exit;
}

require(".auth.php");

$pdo = new PDO($cnn, $user, $pass);

$blogDescription='Dal 1979 ...';

$sql="SELECT option_value FROM wp_options WHERE option_name='siteurl'";
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $oldSite=$row['option_value'];
  break;
}
$newSite=$site.$url;
echo "\n\r";
echo "Sposto il sito su: " .$argv[1] ."\n\r";
echo "Nuovo sito.......: [" . trim($newSite). "]\n\r";
echo "Vecchio sito.....: [" . trim($oldSite). "]\n\r";


$sql = "  UPDATE wp_options
          SET option_value='" . $site . $url . "'
          WHERE option_name='siteurl';";

          $stml = $pdo->prepare($sql);
          $stml->execute();

$sql = "UPDATE wp_options
        SET option_value='" . $site . $url . "'
        WHERE option_name='home';";

$stml = $pdo->prepare($sql);
$stml->execute();

$sql = "UPDATE wp_options
        SET option_value='$blogname'
        WHERE option_name='blogname';";

$stml = $pdo->prepare($sql);
$stml->execute();

$sql = "UPDATE wp_options
        SET option_value='$blogDescription'
        WHERE option_name='blogdescription';";

$stml = $pdo->prepare($sql);
$stml->execute();

// Modifiche a guid e post_content
$sql="SELECT ID, guid, post_content FROM wp_posts";
$stml = $pdo->prepare($sql);
$stml->execute();

while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
  $postId=$row['ID'];

  $lastSlash=strripos($row['guid'],"/");
  $url=substr($row['guid'],$lastSlash);
  $newGuid= $site . $url;

  $newPostContent=str_replace(addslashes($oldSite), addslashes($newSite), $row['post_content']);
  updatePosts($postId, $newGuid, $newPostContent);
}

function updatePosts($postId, $newGuid, $newPostContent){
  global $pdo;
  $sql="UPDATE wp_posts
          SET guid='$newGuid',
              post_content=" . $pdo->quote($newPostContent) . "
        WHERE ID=$postId";
  $stml = $pdo->prepare($sql);
  $stml->execute();
}
