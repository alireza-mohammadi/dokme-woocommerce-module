<?php
require_once 'includes/dokme_sendRequest.php';
include_once 'includes/dokme_productsList.php';
include_once 'includes/model/dokme_dbsync.php';

class Dokme
{

    private static $initiated = false;

    public function __construct($file)
    {
        self::init_hooks();
    }

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    public static function init_hooks()
    {
        add_action('admin_menu', array('Dokme', 'DokmeAdminMenu'));
        add_action('admin_enqueue_scripts', array('Dokme', 'registerStyleScript'));
        add_action('save_post_product', array('Dokme', 'updateProduct'), 30, 2);
        add_action('woocommerce_product_set_stock', array('Dokme', 'updateStock'), 30, 1);
        add_action('trashed_post', array('Dokme', 'deleteProduct'), 30, 1);
        add_action('create_product_cat', array('Dokme', 'addCategories'), 10, 1);
        add_action('edited_product_cat', array('Dokme', 'editCategories'), 10, 2);
    }

    public static function plugin_activation()
    {
        self::loadSQLFile(plugins_url('/includes/sql/install.sql', __FILE__));
        update_site_option('Dokme_API_TOKEN', '');
    }

    public static function plugin_deactivation()
    {
        global $wpdb;
        $query = "DROP TABLE {$wpdb->prefix}dokme_sync";
        $wpdb->query($query);
    }

    public static function loadSQLFile($sqlFile)
    {
        global $wpdb;

        $getContent = file_get_contents($sqlFile);
        $replaceContent = str_replace('PREFIX_', $wpdb->prefix, $getContent);
        $sqlRequests = preg_split('/;\s*[\r\n]+/', $replaceContent);

        foreach ($sqlRequests as $request) {
            if (mb_strlen($request) > 0) {
                $wpdb->query($request);
            }
        }
    }

    public static function DokmeAdminMenu()
    {
        add_menu_page(__('Dokme', 'access'), __('دکمه', 'access'), 'manage_options', __('dokme-settings', 'vibe'), array('Dokme', 'loadView'), WP_PLUGIN_URL . '/dokme/assets/images/logo.gif');
        add_submenu_page('dokme-settings', __('dokme-products', 'access'), __('کالاها'), 'manage_options', __('dokme-products'), array('Dokme', 'dokmeProducts'));
    }

    public static function dokmeProducts()
    {
        $list = new Dokme_ProductsList();
        $list->prepare_items();

        echo "<form method='post'><input type='hidden' name='page' value='" . $_REQUEST['page'] . "'/>" . $list->display() . "</form>";
    }

    public static function loadView()
    {
        require_once 'includes/view.php';
    }

    public static function registerStyleScript($hook)
    {
        wp_enqueue_media();

        wp_register_style('bootstrap-cu-dokme', plugins_url('/assets/css/bootstrap-cu-dokme.css?h=f05e4r', __FILE__));
        wp_enqueue_style('bootstrap-cu-dokme');

        wp_register_style('theme-bootstrap-cu-dokme', plugins_url('/assets/css/theme-bootstrap-cu-dokme.css?h=f05e4r', __FILE__));
        wp_enqueue_style('theme-bootstrap-cu-dokme');

        wp_register_style('dokme-style', plugins_url('/assets/css/dokme-style.css?h=f05e4r', __FILE__));
        wp_enqueue_style('dokme-style');

        wp_enqueue_script('ajax-script', plugins_url('/assets/js/dokme_ajax.js?h=f05e4r', __FILE__), array('jquery'));

        wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
    }

    static function deleteProduct($postId)
    {
        $sendRequest = new Dokme_SendRequest();
        $sendRequest->deleteProducts($postId);
    }

    public static function updateProduct($postId, $post_after)
    {
        $status = array('future', 'draft', 'pending', 'private', 'auto-draft', 'inherit');

        if ($post_after->post_type != 'product') {
            return;
        }

        if ($post_after->post_status == 'publish') {
            $sendRequest = new Dokme_SendRequest();
            $sendRequest->syncProduct(array($postId));

            return;
        }

        if (in_array($post_after->post_status, $status)) {
            $sendRequest = new Dokme_SendRequest();
            $sendRequest->deleteProducts($postId);
            Dokme_Dbsync::update($postId);
        }
    }

    public static function updateStock($product)
    {
        $sendRequest = new Dokme_SendRequest();
        $sendRequest->syncProduct(array($product->id));
    }

    public static function updateToken()
    {
        $status = update_site_option("DOKME_API_TOKEN", sanitize_text_field($_POST['token']));
        $message = $status ? 'توکن با موفقیت به روز رسانی شد.' : 'مقدار توکن جدید با توکن ذخیره شده برابر میباشد.';

        echo json_encode(array('status' => $status, 'message' => $message));
        wp_die();
    }

    public static function syncAllCategories()
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

        $categoryList = array();
        foreach ($categories as $category) {
            $categoryList[] = array(
                'id' => $category->cat_ID,
                'parent_id' => $category->parent,
                'name' => $category->name
            );
        }

        $sendRequests = new Dokme_SendRequest();
        $result = $sendRequests->sendRequset('categories/sync', 'POST', json_encode($categoryList));

        echo json_encode($result);
        wp_die();
    }

    public static function syncAllProducts()
    {
        $result = array('status' => false, 'code' => 401, 'message' => 'وارد کردن توکن الزامی میباشد.');

        $args = array(
            'order' => 'ASC',
            'fields' => 'ids',
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => sanitize_text_field($_POST['chunk']),
            'offset' => sanitize_text_field($_POST['pageNumber']) * sanitize_text_field($_POST['chunk'])
        );
        $query = new WP_Query($args);
        $ids = $query->query($args);

        $apiToken = get_site_option('DOKME_API_TOKEN');
        if (!empty($apiToken)) {
            $sendRequest = new Dokme_SendRequest();
            $result = $sendRequest->syncProduct($ids);
        }

        echo json_encode($result);
        wp_die();
    }

    public static function addCategories($term_id)
    {
        try {
            $term = get_term_by('id', $term_id, 'product_cat', 'ARRAY_A');

            $category = array(
                'id' => $term['term_id'],
                'parent_id' => $term['parent'],
                'name' => $term['name']
            );

            $sendRequest = new Dokme_SendRequest();
            $sendRequest->sendRequset('categories', 'POST', json_encode($category));
        } catch (Exception $e) {

        }
    }

    public static function editCategories($term_id, $taxonomy)
    {
        try {
            $term = get_term_by('id', $term_id, 'product_cat', 'ARRAY_A');

            $category = array(
                'id' => $term['term_id'],
                'parent_id' => $term['parent'],
                'name' => $term['name']
            );

            $sendRequest = new Dokme_SendRequest();
            $sendRequest->sendRequset('categories', 'POST', json_encode($category));
        } catch (Exception $e) {

        }
    }

}
