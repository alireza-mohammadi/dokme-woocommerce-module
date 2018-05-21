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

        $tblSynchronize = $wpdb->prefix . 'dokme_synchronize';

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

