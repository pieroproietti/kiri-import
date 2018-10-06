<?php

function categoryScan($tableName)
{
    global $pdo;
    $sql = "SELECT universe, category, sub_category, code_ean FROM " .$tableName;

    $stml = $pdo->prepare($sql);
    $stml->execute();

    $insertedCounter = 0;
    while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
        $codeEan = $row['code_ean'];
        $universe = $row['universe'];
        if ($universe == 'Fragranza') {
            $universe = 'Fragranze';
        }
        $category = $row['category'];
        $subCategory = $row['sub_category'];

        if (!$codeEan == '') {
            $Id = ean2postId($codeEan);
            if (!$Id == 0) {
                echo '.';
                $updated = true;
                categoryInsert($Id, $universe, $category, $subCategory);
            }
        }
    }
}

function categoryInsert($Id, $universe, $category, $subCategory)
{
    $universe = strtoupper(substr($universe, 0, 14));
    $category = strtoupper(substr($category, 0, 14));
    $subCategory = strtoupper(substr($subCategory, 0, 14));

    if($universe=="COSMETICA"){
      if($category=="VISO"){
        $subCategory="DA DEFINIRE";
      }
      if($category=="CORPO"){
        $subCategory="DA DEFINIRE";
      }
      if($category=="CAPELLI"){
        $subCategory="DA DEFINIRE";
      }

    }


    if (!$universe == '') {
        echo 'Id: '.$Id.', un.: '.$universe.', cat.: '.$category.', subCat.: '.$subCategory."\n\r";
        global $pdo;
        $parentId = 0;
        //echo "Cerco universe: $universe\n\r";
        $sql = "SELECT t.term_id term_id, t.term_taxonomy_id term_taxonomy_id, t.parent parent, upper(c.name) name, c.slug slug ";
        $sql .="  FROM `wp_term_taxonomy` t, `wp_terms` c ";
        $sql .="  WHERE t.term_id=c.term_id AND t.taxonomy='product_cat' AND ";
        $sql .="        upper(c.name)='$universe' AND t.parent=$parentId";
        //echo $sql."\n\r";
        $stml = $pdo->prepare($sql);
        $stml->execute();
        while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
            $universeId = $row['term_id'];
            $termTaxonomyId = $row['term_taxonomy_id'];
            break;
        }

        if (!$category == '') {
            //echo "Cerco category: $category\n\r";
            $sql = 'SELECT t.term_id term_id, t.term_taxonomy_id term_taxonomy_id, t.parent parent, upper(c.name) name, c.slug slug ';
            $sql .= ' FROM `wp_term_taxonomy` t, `wp_terms` c ';
            $sql .= " WHERE t.term_id=c.term_id AND t.taxonomy='product_cat' AND ";
            $sql .= "       upper(c.name)='$category' AND t.parent=$universeId";
            //echo $sql."\n\r";
            $stml = $pdo->prepare($sql);
            $stml->execute();
            while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
                $categoryId = $row['term_id'];
                $termTaxonomyId = $row['term_taxonomy_id'];
                break;
            }

            if (!$subCategory == '') {
                //echo "Cerco sub_category: $subCategory\n\r";
                $sql = "SELECT t.term_id term_id, t.term_taxonomy_id term_taxonomy_id, t.parent parent, upper(c.name) name, c.slug slug ";
                $sql .= 'FROM `wp_term_taxonomy` t, `wp_terms` c ';
                $sql .= "WHERE t.term_id=c.term_id AND t.taxonomy='product_cat'";
                $sql .= " AND upper(c.name) like('$subCategory%') AND t.parent=$categoryId";

                //echo $sql."\n\r";
                $stml = $pdo->prepare($sql);
                $stml->execute();
                while ($row = $stml->fetch(PDO::FETCH_ASSOC)) {
                    $subCategoryId = $row['term_id'];
                    $termTaxonomyId = $row['term_taxonomy_id'];
                    break;
                }
            }
        }

        $sql = "SELECT * FROM wp_term_relationships WHERE term_taxonomy_id=$$termTaxonomyId AND object_id=$Id";
        $stml = $pdo->prepare($sql);
        $stml->execute();
        if ($stml->num_rows > 0) {
            $sql = 'UPDATE wp_term_relationships SET ';
        } else {
            $sql = "INSERT INTO wp_term_relationships (term_taxonomy_id, term_order, object_id) VALUES   ($termTaxonomyId, 0, $Id)";
        }

        //echo $sql."\n\r";
        $stml = $pdo->prepare($sql);
        $stml->execute();
    }
}
