<?php

require 'vendor/autoload.php';
require '.auth.php';
require 'postSlug.php';
include_once '../wp-blog-header.php';

require 'imgSheetsImport.php';
require 'xlsCategoriesLinks.php';

if (count($argv) == 1) {
    echo 'uso: php foglio-import.php  foglio.xlsx'."[--test]\n\r";
    exit;
}

$foglioFull = $argv[1];
if ($foglioFull == '--test') {
    $foglioFull = 'xls/annick-goutal-brand/annick-goutal-brand.xlsx';
}

$pathName = substr($foglioFull, 0, strrpos($foglioFull, '/') + 1);
$pathImg = substr($foglioFull, 0, strrpos($foglioFull, '/') + 1).'img/';
$startPos = strrpos($foglioFull, '/') + 1;
$foglioName = substr($foglioFull, $startPos);
$lenght = strrpos($foglioFull, '.') - strlen($foglioFull);
$marchioId = substr($foglioFull, $startPos, $lenght);

$tableName = "foglio_".str_replace('-','_', $marchioId);


echo "\n\rOperazione: SheetImport";
echo "\n\r--------------------------------------------------------------------";
echo "\n\rPath       : ".$pathName;
echo "\n\rPath images: ".$pathImg;
echo "\n\rFoglio     : ".$foglioName;
echo "\n\rMarchio    : ".$marchioId;
echo "\n\rTable      : ".$tableName."\n\r";
echo "--------------------------------------------------------------------\n\r";
if (!file_exists($foglioFull)) {
    echo "Il foglio di calcolo: $foglioFull NON esiste! \n\r";
}

$pdo = new PDO($cnn, $user, $pass);
tableCreate($tableName);
foglioImport($foglioFull, $tableName);



// INIBITA IMPORTAZIONE
postInsertOrUpgradeAll($tableName);
categoryScan($tableName);
// Inizio funzioni


function postInsertOrUpgradeAll($tableName)
{
    global $pdo;
    $sql = "SELECT * FROM " .$tableName;
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $updateCounter = 0;
    $insertedCounter = 0;
    while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
        $codeEan = $row['code_ean'];
        if (!$codeEan == '') {
            if (postInsertOrUpgrade($tableName, $codeEan)) {
                ++$updateCounter;
            } else {
                ++$insertedCounter;
            }
        }
    }
    echo 'Aggiornati: '.$updateCounter."\r\n";
    echo 'Inseriti: '.$insertedCounter."\r\n";
}

function postInsertOrUpgrade($tableName, $codeEan)
{
    global $pdo;
    $sql = "SELECT * FROM ".$tableName." WHERE code_ean=". $pdo->quote($codeEan);

    $stml = $pdo->prepare($sql);
    $stml->execute();

    $updated = false;
    $row = $stml->fetch(PDO::FETCH_ASSOC);
    $universe = $row['universe'];
    $category = $row['category'];
    $subCategory = $row['sub_category'];
    $brand = $row['brand'];
    $linea = $row['linea'];
    $productName = $row['product_name'];
    $codeSku = $row['code_sku'];
    $subtitles = $row['subtitles'];
    $descrition = $row['description'];
    $piramideOlfattiva = $row['piramide_olfattiva'];
    $ingredients = $row['ingredients'];
    $size = $row['size'];

    $postAuthor = 1;
    $postDate = date('Y-m-d H:i:s');
    $postDateGmt = date('Y-m-d H:i:s');
    $postContent = addslashes('<h1>'.htmlentities($productName).'</h1>'.
                 '<p>'.htmlentities($descrition).'</p>');
    $postContent .= ($piramideOlfattiva == '' ? '' : '<h4>Piramide olfattiva</h4><p>'.addslashes(htmlentities($piramideOlfattiva).'</p>'));
    $postContent .= ($ingredients == '' ? '' : '<h4>Ingredienti<h4/><p>'.addslashes(htmlentities($ingredients).'</p>'));

    $postTitle = addslashes(htmlentities($productName).' '.htmlentities($size));
    $postExcerpt = addslashes(htmlentities($productName).' '.htmlentities($size));
    $postStatus = 'pending';
    $commentStatus = 'open';
    $pingStatus = 'closed';
    $post_password = '';
    $postName = postSlug($productName.'-'.$size);
    $toPing = '';
    $pinged = '';
    $postModified = date('Y-m-d H:i:s');
    $postModifiedGmt = date('Y-m-d H:i:s');
    $postContentFiltered = '';
    $postParent = 0;
    $guid = '';
    $menuOrder = 0;
    $postType = 'product';
    $post_mime_type = '';
    $commentCount = 0;
    $Id = ean2postId($codeEan);

    if (!$Id == 0) {
        $updated = true;
        productUpdate($codeEan, $Id, $postContent, $postTitle, $postExcerpt, $postName);
        $lastInserted = $Id;
    } else {
        $lastInserted = productInsert($codeEan, $postAuthor, $postDate, $postDateGmt, $postContent, $postTitle, $postExcerpt, $postStatus, $commentStatus, $pingStatus, $postPassowrd, $postName, $toPing, $pinged, $postModified, $postModifiedGmt, $postContentFiltered, $postParent, $guid, $menuOrder, $postType, $postMimeType, $commentCount);
    }
    echo '.';

    $guid = get_site_url()."?post_type=product&#038;p=$lastInserted";
    $sql = "UPDATE wp_posts
                SET guid='$guid'
                WHERE id=$lastInserted";
    $stml = $pdo->prepare($sql);
    $stml->execute();
    postInsertMeta($lastInserted, $codeEan);

    global $marchioId;
    global $categoryId;
    taxonomyInsert($lastInserted, $marchioId);

    global $pathImg;
    $fileImg = $pathImg.$codeSku;
    $description = htmlentities($productName.' '.$size);
    $productId = $lastInserted;
    addImage($productId, $fileImg, $description);

    return $updated;
}

function productInsert($codeEan, $postAuthor, $postDate, $postDateGmt, $postContent, $postTitle, $postExcerpt, $postStatus, $commentStatus, $pingStatus, $postPassowrd, $postName, $toPing, $pinged, $postModified, $postModifiedGmt, $postContentFiltered, $postParent, $guid, $menuOrder, $postType, $postMimeType, $commentCount)
{
    global $pdo;

    $sql = "INSERT INTO wp_posts ( ";
    $sql .="  post_author, ";
    $sql .="  post_date, ";
    $sql .="  post_date_gmt, ";
    $sql .="  post_content, ";
    $sql .="  post_title, ";
    $sql .="  post_excerpt, ";
    $sql .="  post_status, ";
    $sql .="  comment_status, ";
    $sql .="  ping_status,";
    $sql .="  post_password, ";
    $sql .="  post_name, ";
    $sql .="  to_ping, ";
    $sql .="  pinged, ";
    $sql .="  post_modified, ";
    $sql .="  post_modified_gmt, ";
    $sql .="  post_content_filtered, ";
    $sql .="  post_parent, ";
    $sql .="  guid, ";
    $sql .="  menu_order, ";
    $sql .="  post_type, ";
    $sql .="  post_mime_type, ";
    $sql .="  comment_count ) VALUES (";
    $sql .= $pdo->quote($postAuthor).", ";
    $sql .= $pdo->quote($postDate).", ";
    $sql .= $pdo->quote($postDateGmt).", ";
    $sql .= $pdo->quote($postContent).", ";
    $sql .= $pdo->quote($postTitle).", ";
    $sql .= $pdo->quote($postExcerpt).", ";
    $sql .= $pdo->quote($postStatus).", ";
    $sql .= $pdo->quote($commentStatus).", ";
    $sql .= $pdo->quote($pingStatus).", ";
    $sql .= $pdo->quote($postPassowrd).", ";
    $sql .= $pdo->quote($postName).", ";
    $sql .= $pdo->quote($toPing).", ";
    $sql .= $pdo->quote($pinged).", ";
    $sql .= $pdo->quote($postModified).", ";
    $sql .= $pdo->quote($postModifiedGmt).", ";
    $sql .= $pdo->quote($postContentFiltered).", ";
    $sql .= $pdo->quote($postParent).", ";
    $sql .= $pdo->quote($guid).", ";
    $sql .= $pdo->quote($menuOrder).", ";
    $sql .= $pdo->quote($postType).", ";
    $sql .= $pdo->quote($postMimeType).", ";
    $sql .= $pdo->quote($commentCount). ")";

    $stml = $pdo->prepare($sql);
    $stml->execute();

    $stml = $pdo->prepare('SELECT LAST_INSERT_ID();');
    $stml->execute();
    $lastInserted = $pdo->lastInsertId();

    return $lastInserted;
}

function productUpdate($codeEan, $Id, $postContent, $postTitle, $postExcerpt, $postName)
{

    global $pdo;
    $sql = 'UPDATE `wp_posts` ';
    $sql .= "SET post_status='publish', ";
    $sql .= 'post_content='.$pdo->quote($postContent).', ';
    $sql .= 'post_title='.$pdo->quote($postTitle).', ';
    $sql .= 'post_excerpt='.$pdo->quote($postExcerpt).', ';
    $sql .= 'post_name='.$pdo->quote($postName).' ';
    $sql .= "WHERE ID=$Id";


    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function ean2postId($codeEan)
{
    global $pdo;
    $retval = 0;
    $sql = "SELECT post_id FROM wp_postmeta WHERE meta_value=" . $pdo->quote($codeEan) ." AND meta_key='_sku'";

    $rows = $pdo->prepare($sql);
    $rows->execute();
    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        $retval = $row['post_id'];
        break;
    }
    return $retval;
}

function tableCreate($tableName)
{
    global $pdo;
    $sql = 'DROP TABLE fogli;';
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $sql = "
  CREATE TABLE IF NOT EXISTS `".$tableName."` (
    `id` int(11) NOT NULL,
    `universe` varchar(9) DEFAULT NULL,
    `category` varchar(6) DEFAULT NULL,
    `sub_category` varchar(14) DEFAULT NULL,
    `brand` varchar(36) DEFAULT NULL,
    `linea` varchar(36) DEFAULT NULL,
    `product_name` varchar(36) DEFAULT NULL,
    `code_sku` varchar(13) DEFAULT NULL,
    `subtitles` varchar(18) DEFAULT NULL,
    `description` varchar(1388) DEFAULT NULL,
    `piramide_olfattiva` varchar(300) DEFAULT NULL,
    `ingredients` varchar(300) DEFAULT NULL,
    `size` varchar(6) DEFAULT NULL,
    `code_ean` varchar(13) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  ";
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $sql = 'ALTER TABLE ` '.$tableName.' ` ADD UNIQUE KEY `id` (`id`);';
    $stml = $pdo->prepare($sql);
    $stml->execute();

    $sql = 'ALTER TABLE ` '.$tableName.'` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT';
    $stml = $pdo->prepare($sql);
    $stml->execute();
}

function postInsertMeta($postId, $codeEan)
{
    global $pdo;
    $backorders = '0';
    $crosssell_ids = 'a:0:{}';
    $downloadable = 'no';
    $editLast = '1';
    $editLock = '0';
    $featured = 'no';
    $height = '';
    $length = '';
    $manageStock = 'yes';
    $price = '0';
    $productAttributess = '0';
    $productImageGallery = '';
    $productVersion = '2.6.8';
    $purchaseNote = '';
    $regularPrice = '0';
    $salePrice = '0';
    $salePriceDatesFrom = '';
    $salePriceDatesTo = '';
    $sku = $codeEan;
    $sold_individually = '';
    $stock = '0';
    $stockStatus = 'instock';
    $taxClass = '0';
    $taxStatus = '0';
    $upsellIds = 'a:0:{}';
    $virtual = 'no';
    $visibility = 'visible';
    $weight = '';
    $width = '';

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

function taxonomyInsert($postId, $slugId)
{
    global $pdo;
    $sql = "SELECT term_id FROM wp_terms WHERE slug='$slugId';";
  //echo $sql ."\n\r";
  $stml = $pdo->prepare($sql);
    $stml->execute();
    while ($term_row = $stml->fetch(PDO::FETCH_ASSOC)) {
        $termId = $term_row['term_id'];

        $sql = "UPDATE wp_term_taxonomy SET count=count+1 WHERE term_id=$termId;";
        $stml = $pdo->prepare($sql);
        $stml->execute();

        $sql = "SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id=$termId;";
        $stml = $pdo->prepare($sql);
        $stml->execute();

        break;
    }

    while ($term_tax = $stml->fetch(PDO::FETCH_ASSOC)) {
        $termTaxonomyId = $term_tax['term_taxonomy_id'];
        $sql = "INSERT INTO wp_term_relationships ( object_id, term_taxonomy_id, term_order) VALUES
    (
      $postId,
      $termTaxonomyId,
      0
    )";
        $stml = $pdo->prepare($sql);
        $stml->execute();
    //echo $sql ."\n\r";
    }
}

function foglioImport($foglioFull, $tableName)
{
    global $pdo;
    echo "\n\r".$foglioFull."\n\r";
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    $objReader->setReadDataOnly(true);

    $objPHPExcel = $objReader->load($foglioFull);
    $objWorksheet = $objPHPExcel->getActiveSheet();

    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();

    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    for ($row = 2; $row <= $highestRow; ++$row) {
        $objWorksheet->getCellByColumnAndRow();

        $universe = $objWorksheet->getCellByColumnAndRow(0, $row);
        $category = $objWorksheet->getCellByColumnAndRow(1, $row);
        $subCategory = $objWorksheet->getCellByColumnAndRow(2, $row);
        $brand = $objWorksheet->getCellByColumnAndRow(3, $row);
        $linea = removeAccent($objWorksheet->getCellByColumnAndRow(4, $row));
        $productName = $objWorksheet->getCellByColumnAndRow(5, $row);
        $codeSku = $objWorksheet->getCellByColumnAndRow(6, $row);
        $subtitles = $objWorksheet->getCellByColumnAndRow(7, $row);
        $description = $objWorksheet->getCellByColumnAndRow(8, $row);
        $piramideOlfattiva = $objWorksheet->getCellByColumnAndRow(9, $row);
        $ingredients = $objWorksheet->getCellByColumnAndRow(10, $row);
        $size = $objWorksheet->getCellByColumnAndRow(11, $row);
        $codeEan = $objWorksheet->getCellByColumnAndRow(12, $row);
        if ($codeEan == '0000000000000') {
            return;
        }
        if ($codeEan == '') {
            return;
        }
        $codeEan = str_pad($codeEan, 13, '0', STR_PAD_LEFT);


        $sql = "INSERT INTO ".$tableName." ( ";
        $sql .= "universe, ";
        $sql .= "category, ";
        $sql .= "sub_category, ";
        $sql .= "brand, ";
        $sql .= "linea, ";
        $sql .= "product_name, ";
        $sql .= "code_sku, ";
        $sql .= "subtitles, ";
        $sql .= "description, ";
        $sql .= "piramide_olfattiva, ";
        $sql .= "ingredients, ";
        $sql .= "size, ";
        $sql .= "code_ean ";
        $sql .= ") VALUES ( ";
        $sql .= $pdo->quote($universe).", ";
        $sql .= $pdo->quote($category).", ";
        $sql .= $pdo->quote($subCategory).", ";
        $sql .= $pdo->quote($brand).", ";
        $sql .= $pdo->quote($linea).", ";
        $sql .= $pdo->quote($productName).", ";
        $sql .= $pdo->quote($codeSku).", ";
        $sql .= $pdo->quote($subtitles).", ";
        $sql .= $pdo->quote($description).", ";
        $sql .= $pdo->quote($piramideOlfattiva).", ";
        $sql .= $pdo->quote($ingredients).", ";
        $sql .= $pdo->quote($size).", ";
        $sql .= $pdo->quote($codeEan);
        $sql .= ");";

        $stml = $pdo->prepare($sql);
        $stml->execute();
    }
}
