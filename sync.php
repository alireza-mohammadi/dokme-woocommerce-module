<?php
require_once '../../../wp-load.php';
require_once 'includes/dokme_product.php';

syncAllProducts();

function syncAllProducts()
{
    global $wpdb;

    $tblPost        = $wpdb->prefix . 'posts';
    $tblSynchronize = $wpdb->prefix . 'dokme_synchronize';

    $query = "SELECT `$tblPost`.`ID` FROM `$tblPost` 
              LEFT JOIN `$tblSynchronize` ON  `$tblPost`.`ID` = `$tblSynchronize`.`product_id`
              WHERE `$tblPost`.`post_type` = 'product' AND `$tblPost`.`post_status` = 'publish' 
              AND `$tblPost`.`post_modified_gmt` > `$tblSynchronize`.`date_sync` 
              GROUP BY `$tblPost`.`ID` LIMIT 100";

    $ids = $wpdb->get_results($query);

    if (empty($ids)) {
        return;
    }

    $items = array();
    foreach ($ids as $id) {
        $items[] = $id->ID;
    }

    $products = array();
    foreach ($items as $item) {
        $result = Dokme_Product::getProductDetail($item);

        if (empty($result)) {
            continue;
        }

        $products[] = $result;
    }

    print_r(json_encode($products));

    if ($result['status']) {
        $time  = date('Y-m-d H:i:s');
        $items = implode(',', $items);
        $query = "UPDATE `$tblSynchronize` SET `date_sync`='$time' WHERE `product_id` IN ($items)";
        $wpdb->get_results($query);
    }

}