<?php
  require '../wp-blog-header.php';

    $get_all_products_query = "SELECT `ID`
                FROM `". $wpdb->prefix . "posts`
                WHERE `post_type` LIKE 'product'
                AND `post_status` = 'publish'
                ORDER BY `ID` DESC";

     $get_all_products_result = $wpdb->get_results($get_all_products_query);

     $regular_price = '';

     if(!empty($get_all_products_result))
     {
     foreach($get_all_products_result as $single_product)
     {
         $product_parent_id = $single_product->ID;

        //Get all variations of single product

        $query = "SELECT `post_id`
              FROM `" . $wpdb->prefix . "postmeta`
              WHERE `meta_key` = 'attribute_pa_product'
              AND `post_id`
              IN (
              SELECT `ID`
              FROM `" . $wpdb->prefix . "posts`
              WHERE `post_parent` = " . $product_parent_id . "
              )";

        $variation_result = $wpdb->get_results($query);

        if(!empty($variation_result))
        {
            //As one product may have multiple variation

            foreach($variation_result as $single_variation)
            {
              $post_id = $single_variation->post_id;

                          update_post_meta( $post_id, '_regular_price', $regular_price);
            }
        }

}
}
?>
