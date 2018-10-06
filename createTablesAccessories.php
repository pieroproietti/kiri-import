<?php
require '../wp-blog-header.php';
require('postSlug.php');

require '.auth.php';
$pdo = new PDO($cnn, $user, $pass);
$sql="DROP TABLE lp;";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="CREATE TABLE IF NOT EXISTS `lp` (
        `lp_id` bigint(20) NOT NULL DEFAULT '0',
        `post_id` bigint(20) DEFAULT NULL,
        `articolo_id` varchar(15) DEFAULT NULL,
        `categoria_id` varchar(3) DEFAULT NULL,
        `sotto_categoria_id` int(2) DEFAULT NULL,
        `marchio_id` varchar(5) DEFAULT NULL,
        `linea_id` varchar(3) DEFAULT NULL,
        `ean` varchar(14) DEFAULT NULL,
        `descrizione` varchar(31) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

      $stml=$pdo->prepare($sql);
      $stml->execute();

$sql="ALTER TABLE `lp` ADD PRIMARY KEY (`lp_id`);";
$stml=$pdo->prepare($sql);
$stml->execute();

$sql="ALTER TABLE `lp` MODIFY `lp_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;";
$stml=$pdo->prepare($sql);
$stml->execute();

$sql="DROP TABLE lc;";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="CREATE TABLE IF NOT EXISTS `lc` (
      `lc_id` bigint(20) NOT NULL DEFAULT '0',
      `term_id` bigint(20) DEFAULT NULL,
      `term_taxonomy_id` bigint(20) DEFAULT NULL,
      `categoria_id` varchar(3) DEFAULT NULL,
      `sotto_categoria_id` varchar(2) DEFAULT NULL,
      `descrizione_categoria` varchar(30) DEFAULT NULL,
      `descrizione_parent` varchar(19) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="ALTER TABLE `lc` ADD PRIMARY KEY (`lc_id`);";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="ALTER TABLE `lc` MODIFY `lc_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;";
$stml=$pdo->prepare($sql);
$stml->execute();


$sql="DROP TABLE ll;";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="CREATE TABLE IF NOT EXISTS `ll` (
      `ll_id` bigint(20) NOT NULL DEFAULT '0',
      `term_id` bigint(20) DEFAULT NULL,
      `term_taxonomy_id` bigint(20) DEFAULT NULL,
      `marchio_id` varchar(5) DEFAULT NULL,
      `linea_id` varchar(3) DEFAULT NULL,
      `marchio` varchar(30) DEFAULT NULL,
      `linea` varchar(30) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $stml=$pdo->prepare($sql);
    $stml->execute();
$sql="ALTER TABLE `ll` ADD PRIMARY KEY (`ll_id`);";
$stml=$pdo->prepare($sql);
$stml->execute();
$sql="ALTER TABLE `ll` MODIFY `ll_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;";
$stml=$pdo->prepare($sql);
$stml->execute();
