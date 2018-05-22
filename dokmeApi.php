<?php
require_once '../../../wp-load.php';
require_once 'includes/dokme_product.php';

class DokmeApi
{
    public function auth()
    {
        if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
            echo $this->_response(403);
            return false;
        }

        $authorization = $this->_readToken($_SERVER['HTTP_AUTHORIZATION']);
        if (!$authorization) {
            echo $this->_response(401);
            return false;
        }

        if ($authorization !== get_site_option('SELLER_TOKEN')) {
            echo $this->_response(401);
            return false;
        }

        return true;
    }

    public function getProduct($id)
    {
        global $wpdb;

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

        $time = date('Y-m-d H:i:s');
        $query = "UPDATE `$tblSynchronize` SET `date_sync`='$time' WHERE `product_id` = $id";
        $wpdb->get_row($query);

        return $product;
    }

    public function getProducts()
    {
        global $wpdb;

        $tblPost = $wpdb->prefix . 'posts';
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

        $time = date('Y-m-d H:i:s');
        $items = implode(',', $items);
        $query = "UPDATE `$tblSynchronize` SET `date_sync`='$time' WHERE `product_id` IN ($items)";
        $wpdb->get_results($query);

        return $products;
    }

    public function getCategories()
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'show_count' => 0,
            'pad_counts' => 0,
            'hierarchical' => 1,
            'title_li' => '',
            'hide_empty' => 0
        );
        $categories = get_categories($args);

        $data = array();
        foreach ($categories as $category) {
            $data[] = array(
                'id' => $category->cat_ID,
                'parent_id' => $category->parent,
                'name' => $category->name
            );
        }

        return $data;
    }

    public function getStatus()
    {
        $plugins = get_option('active_plugins');
        return array_search('dokme/dokme.php', $plugins) !== false;
    }

    protected function _response($status = null)
    {
        $message = array(
            401 => 'Invalid authorization token.',
            403 => 'No authorization token was found.'
        );

        return json_encode(array('status' => false, 'error_code' => $status, 'message' => $message[$status]));
    }

    protected function _readToken($authorization)
    {
        if ($this->_startsWith($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return false;
    }

    protected function _startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

}
