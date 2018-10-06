<?php

require 'postSlug.php';
require '.auth.php';
$pdo = new PDO($cnn, $user, $pass);
$sql = "SELECT categoria_id, sotto_categoria_id, descrizione_parent, descrizione_categoria
        FROM tabmer
        WHERE descrizione_parent<>'' ORDER BY categoria_id, sotto_categoria_id;";

echo "\n\rtagsInsert: " ;
$count = 0;
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
    ++$count;
    if (is_numeric($row['categoria_id']) || ($row['categoria_id'] == '')) {
        categoryCreate($row);
    }
}
echo "\n\r";

function categoryCreate($row)
{
    global $pdo;

    $name = ucfirst(strtolower($row['descrizione_parent'])) . "/". ucfirst(strtolower($row['descrizione_categoria']));
    $slug=postSlug($row['descrizione_parent'] . "-". $row['descrizione_categoria']);
    $termGroup = 2;
    $termId = termCreate($name, $slug, $termGroup);

    $termTaxonomyId = taxonomyCreate($termId, 'product_tag', $name, 0, 0);
    $categoriaId = $row['categoria_id'];
    $sottoCategoriaId = $row['sotto_categoria_id'];
    $descrizioneCategoria = $row['descrizione_categoria'];
    $descrizioneParent = $row['descrizione_parent'];
    lcAdd($termId, $termTaxonomyId, $categoriaId, $sottoCategoriaId, $descrizioneCategoria, $descrizioneParent);
    echo ".";
}

function lcAdd($termId, $termTaxonomyId, $categoriaId, $sottoCategoriaId, $descrizioneCategoria, $descrizioneParent)
{
    global $pdo;
    $sql = "INSERT INTO lc (term_id, term_taxonomy_id,categoria_id,sotto_categoria_id, descrizione_categoria, descrizione_parent) VALUES (
    $termId,
    $termTaxonomyId,
    '$categoriaId',
    '$sottoCategoriaId',
    '$descrizioneCategoria',
    '$descrizioneParent'
  )";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function termCreate($name, $slug, $term_group)
{
    global $pdo;
    $sql = "INSERT INTO `wp_terms` (`name`, `slug`, `term_group`) VALUES
      (
        '$name', -- name
        '$slug', -- slug
        $term_group -- term_group
      )";
      //echo $sql;
  $stml = $pdo->prepare($sql);
    $stml->execute();
    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $last_inserted = $pdo->lastInsertId();

    return $last_inserted;
}

function taxonomyCreate($term_id, $taxonomy, $description, $parent, $count)
{
    global $pdo;
    $sql = "INSERT INTO `wp_term_taxonomy` (`term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
    (
      $term_id, -- term_id
      '$taxonomy', -- taxonomy
      '$description', -- description
      $parent, -- parent
      $count -- count
    )";
    $stml = $pdo->prepare($sql);
    $stml->execute();
    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $last_inserted = $pdo->lastInsertId();

    return $last_inserted;
}
