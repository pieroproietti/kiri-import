<?php

/*!
 * products
 *
 * Copyright 2015, Piero Proietti
 * Released under the MIT license
 */

require '../wp-blog-header.php';
require 'postSlug.php';
require '.auth.php';
$pdo = new PDO($cnn, $user, $pass);

echo "\n\rImportazione prodotti:\n\r";

$sql = 'SELECT distinct articolo_id FROM `sitart` WHERE 1 ORDER BY articolo_id';
$rows = $wpdb->get_results($sql);
$stml = $pdo->prepare($sql);
$stml->execute();
while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
    $articolo_id = $row['articolo_id'];
    productCopy($articolo_id);
    echo '.';
}

function productCopy($articoloId)
{
    global $pdo;

    $sql = "SELECT * FROM anarti WHERE articolo_id='$articoloId'";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
        $giacenza = findGiacenza($articoloId);

        $postId = postInsertAll($row, $giacenza);
        postInsertContent($postId, $row);
        postInsertMeta($postId, $row, $giacenza);
        laAdd($postId, $row);
        break;
    }
}

function findGiacenza($articoloId)
{
    global $pdo;
    $sql = "SELECT giacenza FROM sitart WHERE articolo_id='$articoloId'";
    $stml = $pdo->prepare($sql);
    $stml->execute();
    while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
        $giacenza = $row['giacenza'];
        break;
    }

    if ($giacenza == 1) {
        $giacenza = 1;
    } elseif ($giacenza == 2) {
        $giacenza = 2;
    } else {
        $giacenza = round($giacenza / 3 + 0.5);
    }
    return $giacenza;
}

function laAdd($postId, $row)
{
    global $pdo;
    $articoloId = $row['articolo_id'];
    $categoriaId = $row['categoria_id'];
    $sottoCategoriaId = $row['sotto_categoria_id'];
    $marchioId = $row['marchio_id'];
    $lineaId = $row['linea_id'];
    $ean = $row['ean'];
    $descrizione = $row['descrizione'];

    $sql = "INSERT INTO lp (post_id, articolo_id, categoria_id, sotto_categoria_id, marchio_id, linea_id, ean, descrizione) VALUES
    (
      $postId, -- post_id
      '$articoloId',
      '$categoriaId',
      '$sottoCategoriaId',
      '$marchioId',
      '$lineaId',
      '$ean',
      '$descrizione'
    )";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function postInsertCategory($postId, $row)
{
    global $pdo;
    $categoriaId = $row['categoria_id'];
    $sottoCategoriaId = $row['sotto_categoria_id'];
    $marchioId = $row['marchio_id'];
    $lineaId = $row['linea_id'];
}

function postInsertContent($postId, $row)
{
    global $pdo;
    $marchio = findMarchio($row['marchio_id']);
    $q = $marchio.' '.$row['descrizione'];
    $q = strtolower($q);
    $q = spacesRemove($q);
    $q = urlencode($q);

    $descrizione = buttonFindImage($q);
    $descrizione .= buttonFindDescrizione($q);
    $descrizione .= buttonModify($postId);
    $descrizione .= addslashes($row['descrizione']);

    $sql = "UPDATE wp_posts SET post_content='".$descrizione."' WHERE ID=$postId";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function postInsertAll($row, $giacenza)
{
    global $pdo;

    $author = 1;
    $postDate = $row['data_immissione'];
    $postDateGmt = $row['data_immissione'];
    $postContent = $row['descrizione'];
    $postTitle = $row['descrizione'];
    $postExcerpt = $row['descrizione'];
    $postStatus = 'draft'; // draft / pending / publish
    $commentStatus = 'open';
    $pingStatus = 'open';
    $postPassword = '';
    $postName = postSlug($row['descrizione']);
    $toPing = '';
    $pinged = '';
    $postModified = 'NOW()';
    $postModifiedGmt = '(NOW() - INTERVAL 2 HOUR)';
    $postContentFiltered = '';
    $postParent = 0;
    $guid = '';
    $menuOrder = 0;
    $postType = 'product';
    $postMimeType = '';
    $commentCount = 0;

    $sql = '
      INSERT INTO wp_posts (
        post_author,
        post_date,
        post_date_gmt,
        post_content,
        post_title,
        post_excerpt,
        post_status,
        comment_status,
        ping_status,
        post_password,
        post_name,
        to_ping,
        pinged,
        post_modified,
        post_modified_gmt,
        post_content_filtered,
        post_parent,
        guid,
        menu_order,
        post_type,
        post_mime_type,
        comment_count) VALUES
      ('.
        $pdo->quote($author).', '.
        $pdo->quote($postDate).', '.
        $pdo->quote($postDateGmt).', '.
        $pdo->quote($postContent).', '.
        $pdo->quote($postTitle).', '.
        $pdo->quote($postExcerpt).', '.
        $pdo->quote($postStatus).', '.
        $pdo->quote($commentStatus).', '.
        $pdo->quote($pingStatus).', '.
        $pdo->quote($postPassword).', '.
        $pdo->quote($postName).', '.
        $pdo->quote($toPing).', '.
        $pdo->quote($pinged).', '.
        $pdo->quote($postModified).', '.
        $pdo->quote($postModifiedGmt).', '.
        $pdo->quote($postContentFiltered).', '.
        $pdo->quote($postParent).', '.
        $pdo->quote($guid).', '.
        $pdo->quote($menuOrder).', '.
        $pdo->quote($postType).', '.
        $pdo->quote($postMimeType).', '.
        $pdo->quote($commentCount).
      ')';
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $last_inserted = $pdo->lastInsertId();

    $guid = get_site_url()."?post_type=product&#038;p=$last_inserted";

    $sql = "UPDATE wp_posts
              SET guid='$guid'
              WHERE id=$last_inserted";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    return $last_inserted;
}

function postInsertMeta($postId, $row, $giacenza)
{
    global $pdo;

    $backorders = '0';
    $crosssell_ids = 'a:0:{}';
    $downloadable = 'no';
    $editLast = '1';
    $editLock = '0';
    $featured = 'no';
    $height = $row['altezza'];
    $length = $row['lunghezza'];
    $manageStock = 'yes';
    $price = $row['prezzo_listino'];
    $productAttributess = '0';
    $productImageGallery = '';
    $productVersion = '2.6.6';
    $purchaseNote = '';
    $regularPrice = $row['prezzo_listino'];
    $salePrice = $row['prezzo_listino'];
    $salePriceDatesFrom = '';
    $salePriceDatesTo = '';
    $sku = $row['ean'];
    $sold_individually = '';
    $stock = $giacenza;
    $stockStatus = 'instock';
    $taxClass = '0';
    $taxStatus = '0';
    $upsellIds = 'a:0:{}';
    $virtual = 'no';
    $visibility = 'visible';
    $weight = $row['lordo'];
    $width = $row['larghezza'];

    if ($giacenza > 0) {
        $stockStatus = 'instock';
    } else {
        $stockStatus = 'outofstock';
    }

    postInsertMetaRow($postId, '_backorders',             $backorders);
    postInsertMetaRow($postId, '_crosssell_ids',          $crosssellIds);
    postInsertMetaRow($postId, '_downloadable',           $downloadable);
    postInsertMetaRow($postId, '_edit_last',              $editLast);
    postInsertMetaRow($postId, '_edit_lock',              $editLock);
    postInsertMetaRow($postId, '_featured',               $featured);
    postInsertMetaRow($postId, '_height',                 $height);
    postInsertMetaRow($postId, '_length',                 $length);
    postInsertMetaRow($postId, '_manage_stock',           $manageStock);
    postInsertMetaRow($postId, '_price',                  $price);
    postInsertMetaRow($postId, '_product_attributess',    $productAttributess);
    postInsertMetaRow($postId, '_product_image_gallery',  $productImageGallery);
    postInsertMetaRow($postId, '_product_version',        $productVersion);
    postInsertMetaRow($postId, '_purchase_note',          $purchaseNote);
    postInsertMetaRow($postId, '_regular_price',          $regularPrice);
    postInsertMetaRow($postId, '_sale_price',             $salePrice);
    postInsertMetaRow($postId, '_sale_price_dates_from',  $salePriceDatesFrom);
    postInsertMetaRow($postId, '_sale_price_dates_to',    $salePriceDatesTo);
    postInsertMetaRow($postId, '_sku',                    $sku);
    postInsertMetaRow($postId, '_sold_individually',      $soldIndividually);
    postInsertMetaRow($postId, '_stock',                  $stock);
    postInsertMetaRow($postId, '_stock_status',           $stockStatus);
    postInsertMetaRow($postId, '_tax_class',              $taxClass);
    postInsertMetaRow($postId, '_tax_status',             $taxStatus);
    postInsertMetaRow($postId, '_upsell_ids',             $upsellIds);
    postInsertMetaRow($postId, '_virtual',                $virtual);
    postInsertMetaRow($postId, '_visibility',             $visibility);
    postInsertMetaRow($postId, '_weight',                 $weight);
    postInsertMetaRow($postId, '_width',                  $width);
}

function postStatusSet($postId, $status)
{
    global $pdo;

    $sql = "UPDATE wp_posts SET post_status='$status' WHERE ID=$postId";
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function postInsertMetaRow($post_id, $meta_key, $meta_value)
{
    global $pdo;

    $sql = "INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES
  (
      -- meta_id
      $post_id ,    -- post_id
      '$meta_key',    -- meta_key
      '$meta_value'    -- meta_value
  )";

    $stml = $pdo->prepare($sql);
    $stml->execute();
  //echo $sql . "\n\r";
}

function findMarchio($marchioId)
{
    global $pdo;

    $sql = "SELECT marchio FROM linee WHERE marchio_id='$marchioId'";
    $stml = $pdo->prepare($sql);
    $stml->execute();
    $row = $stml->fetch(PDO::FETCH_ASSOC);
    $marchio = strtolower($row['marchio']);

    return $marchio;
}

function buttonFindImage($q)
{
    $q = "https://www.google.it/?q=$q&tbm=isch";

    return "<br/><a href=\'$q\' target=\'_blank\'><button type=\'button\'>Cerca immagine</button></a>";
}

function buttonFindDescrizione($q)
{
    $q = "https://www.google.it/?q=$q&lr=lang_it&cr=countryIT";

    return "<a href=\'$q\' target=\'_blank\'><button type=\'button\'>Cerca descrizione</button></a>";
}

function buttonModify($postId)
{
    $var = "<a href=\'".get_site_url().'/wp-admin/post.php?post='.$postId."&action=edit\'><button type=\'button\'>Modifica</button></a><br/>";

    return $var;
}
