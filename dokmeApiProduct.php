<?php
require_once '../../../wp-load.php';
require_once 'includes/dokme_product.php';

class DokmeApiProduct
{
    public static function getProduct($id)
    {
        global $wpdb;

        if (!is_numeric($id)) {
            return;
        }

        $tblPost = $wpdb->prefix . 'posts';
        $tblSynchronize = $wpdb->prefix . 'dokme_synchronize';

        $query = "SELECT `$tblPost`.`ID` FROM `$tblPost` 
                  WHERE `$tblPost`.`post_type` = 'product' 
                  AND `$tblPost`.`post_status` = 'publish'
                  AND `$tblPost`.`ID` = '$id'";

        $result = $wpdb->get_row($query);
        if (empty($result)) {
            return;
        }

        $product = Dokme_Product::getProductDetail($id);
        echo(json_encode(
            array(
                'status' => true,
                'data' => $product
            )
        ));

        $time = date('Y-m-d H:i:s');
        $query = "UPDATE `$tblSynchronize` SET `date_sync`='$time' WHERE `product_id` = $id";
        $wpdb->get_row($query);
    }
}

