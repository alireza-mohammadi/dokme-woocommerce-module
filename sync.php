<?php
require_once '../../../wp-load.php';
require_once 'includes/dokme_sendRequest.php';
include_once 'includes/dokme_productsList.php';
include_once 'includes/model/dokme_dbsync.php';
include_once 'includes/dokme_getCategories.php';

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

    $apiToken = get_site_option('DOKME_API_TOKEN');
    if (empty($apiToken)) {
        return;
    }

    $sendRequest = new Dokme_SendRequest();
    $result      = $sendRequest->syncProduct($items);
    if ($result['status']) {
        $time  = date('Y-m-d H:i:s');
        $items = implode(',', $items);
        $query = "UPDATE `$tblSynchronize` SET `date_sync`='$time' WHERE `product_id` IN ($items)";
        $wpdb->get_results($query);
    }

}