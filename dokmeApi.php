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

    public function getProducts($page)
    {
        global $wpdb;

        $total = 100;
        $data = array(
            'total' => $this->getTotal(),
            'per_page' => $total,
            'current_page' => (int)$page,
            'last_page' => ceil($this->getTotal() / $total)
        );

        $currentPage = ((int)$page - 1) * $total;
        $tblPost = $wpdb->prefix . 'posts';
        $tblSynchronize = $wpdb->prefix . 'dokme_synchronize';

        $query = "SELECT `$tblPost`.`ID` FROM `$tblPost` 
              LEFT JOIN `$tblSynchronize` ON  `$tblPost`.`ID` = `$tblSynchronize`.`product_id`
              WHERE `$tblPost`.`post_type` = 'product' AND `$tblPost`.`post_status` = 'publish' 
              AND `$tblPost`.`post_modified_gmt` > `$tblSynchronize`.`date_sync` 
              GROUP BY `$tblPost`.`ID` LIMIT $total  OFFSET $currentPage";

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

        $data['data'] = $products;
        return $data;
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

    public function setEmpty()
    {
        global $wpdb;

        $tblSynchronize = $wpdb->prefix . 'dokme_synchronize';

        $query = "UPDATE `$tblSynchronize` SET `date_sync`='0000-00-00 00:00:00'";
        $wpdb->get_row($query);

        return true;
    }

    public function reloadDb()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM `{$wpdb->prefix}dokme_synchronize`");
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}dokme_synchronize` AUTO_INCREMENT = 1");

        $query = "INSERT IGNORE INTO `{$wpdb->prefix}dokme_synchronize`(`product_id`)
                  SELECT id AS `product_id` FROM `{$wpdb->prefix}posts`
                  WHERE post_type = 'product' AND post_status = 'publish'";
        $wpdb->query($query);
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

    protected function getTotal()
    {
        $selectedCategories = get_site_option('DOKME_SELECTED_CATEGORIES');
        $selectedProducts = get_site_option('DOKME_SELECTED_PRODUCTS');

        if (empty($selectedCategories) || empty($selectedProducts)) {
            $args = array(
                'order' => 'ASC',
                'fields' => 'ids',
                'post_type' => 'product',
                'post_status' => 'publish'
            );
            $query = new WP_Query($args);
            return count($query->query($args));
        }

        return count($selectedProducts) + $this->getCategoryCount($selectedCategories);
    }

    protected function getCategoryCount(array $input)
    {
        global $wpdb;

        if (empty($input)) {
            return 0;
        }

        $items = implode(',', $input);
        $query = "SELECT $wpdb->term_taxonomy.count FROM $wpdb->terms, $wpdb->term_taxonomy WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id AND $wpdb->term_taxonomy.term_id IN ($items)";

        return $wpdb->get_var($query);
    }

}
