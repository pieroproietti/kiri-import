<?php

require '.auth.php';
require 'postSlug.php';

$pdo = new PDO($cnn, $user, $pass);

// LIVELLO I
$fragranzeId = addCategory('Fragranze', 0);
  $casaId = addCategory('Casa', $fragranzeId);
    addCategory('Profumatori ambiente', $casaId);

  $donnaId = addCategory('Donna', $fragranzeId);
    addCategory('Deodoranti', $donnaId);
    addCategory('Prodotti bagno', $donnaId);
    addCategory('Prodotti corpo', $donnaId);
    addCategory('Profumi', $donnaId);

  $unisexId = addCategory('Unisex', $fragranzeId);
    addCategory('Deodoranti', $unisexId);
    addCategory('Prodotti bagno', $unisexId);
    addCategory('Prodotti corpo', $unisexId);
    addCategory('Profumi', $unisexId);

  $uomoId = addCategory('Uomo', $fragranzeId);
    addCategory('Deodoranti', $uomoId);
    addCategory('Prodotti bagno', $uomoId);
    addCategory('Prodotti rasatura', $uomoId);
    addCategory('Prodotti corpo', $uomoId);
    addCategory('Profumi', $uomoId);

// LIVELLO I
$cosmeticaId = addCategory('Cosmetica', 0);
  $capelliId = addCategory('Capelli',$cosmetica);
    //addCategory('Da definire', $capelliId);

  $corpoId=addCategory('Corpo', $cosmeticaId);
    //addCategory('Da definire', $corpoId);

  $visoId=addCategory('Viso', $cosmeticaId);
    //addCategory('Da definire', $visoId);


function addCategory($name, $parent)
{
    $order = '0';
    $slug = postSlug($name);
    $termId = addTerms($name, $parent);
    addCategoryTermMeta($termId);
    addCategoryTaxonomy($termId, $parent);

    return $termId;
}

function addTerms($name, $parent)
{
    global $pdo;
    $slug = postSlug($name);


    $sql = "SELECT name, slug FROM wp_terms WHERE name='$name'";
    if ($rows = $pdo->query($sql)) {
        if ($rows->rowCount() > 0) {
            $sql = "SELECT name FROM wp_terms WHERE term_id=$parent";
            $stml = $pdo->prepare($sql);
            $stml->execute();
            while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
                $slug .= "-" . postSlug($row["name"]);
                break;
            }
        }
    }
    echo 'name: '.$name."\r\n";

    $termGroup = '0';
    $sql = "INSERT INTO `wp_terms` (`name`, `slug`, `term_group`) VALUES('$name', '$slug', $termGroup);";
    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $lastInserted = $pdo->lastInsertId();

    return $lastInserted;
}

function addCategoryTermMeta($termId)
{
    global $pdo;
    $metaKey = 'order';
    $metaValue = '0';
    $sql = "INSERT INTO `wp_termmeta` (`term_id`, `meta_key`, `meta_value`) VALUES   ($termId, '$metaKey', '$metaValue');";
    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $metaKey = 'display_type';
    $metaValue = '0';
    $sql = "INSERT INTO `wp_termmeta` (`term_id`, `meta_key`, `meta_value`) VALUES   ($termId, '$metaKey', '$metaValue');";
    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $metaKey = 'thumbnail_id';
    $metaValue = '0';
    $sql = "INSERT INTO `wp_termmeta` (`term_id`, `meta_key`, `meta_value`) VALUES   ($termId, '$metaKey', '$metaValue');";
    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function addCategorytaxonomy($termId, $parent)
{
    global $pdo;
    $count = 0;
    $taxonomy = 'product_cat';
    $description = '';
    $sql = "INSERT INTO `wp_term_taxonomy` (`term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES ($termId, '$taxonomy', '$description', $parent, $count);";
    echo $sql."\n\r";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function addTermRelationships()
{
    global $pdo;
    $sql = "INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ($objectId, $termTaxonomyId, $order);";
    echo $sql."\n\r";
}

/*
wp_termmeta
wp_terms
wp_term_relationships
wp_term_taxonomy
*/
