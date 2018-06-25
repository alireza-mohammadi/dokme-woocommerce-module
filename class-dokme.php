<?php
require_once 'includes/dokme_sendRequest.php';
include_once 'includes/dokme_productsList.php';
include_once 'includes/model/dokme_dbsync.php';
include_once 'includes/dokme_getCategories.php';

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
        add_action('save_post', array('Dokme', 'updateProduct'), 30, 2);
        add_action('woocommerce_product_set_stock', array('Dokme', 'updateStock'), 30, 1);
        add_action('trashed_post', array('Dokme', 'deleteProduct'), 30, 1);
        add_action('create_product_cat', array('Dokme', 'addCategories'), 10, 1);
        add_action('edited_product_cat', array('Dokme', 'editCategories'), 10, 2);
    }

    public static function plugin_activation()
    {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokme_sync`(
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `product_id` INT NOT NULL,
                  `status` INT NOT NULL DEFAULT 0,
                  `created_at` DATETIME NULL,
                  `updated_at` DATETIME NULL,
                  PRIMARY KEY(`id`),
                  UNIQUE INDEX `product_id_UNIQUE`(`product_id` ASC)
                  )";

        $wpdb->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokme_synchronize`(
                  `id` BIGINT NOT NULL AUTO_INCREMENT,
                  `product_id` BIGINT NOT NULL,
                  `date_sync` DATETIME NOT NULL,
                  PRIMARY KEY(`id`),
                  UNIQUE INDEX `product_id_UNIQUE`(`product_id` ASC)
                  )";
        $wpdb->query($query);

        $query = "INSERT IGNORE INTO `{$wpdb->prefix}dokme_sync`(`product_id`)
                  SELECT id AS `product_id` FROM `{$wpdb->prefix}posts` 
                  WHERE post_type = 'product' AND post_status = 'publish'";
        $wpdb->query($query);

        $query = "INSERT IGNORE INTO `{$wpdb->prefix}dokme_synchronize`(`product_id`)
                  SELECT id AS `product_id` FROM `{$wpdb->prefix}posts`
                  WHERE post_type = 'product' AND post_status = 'publish'";
        $wpdb->query($query);

        update_site_option('Dokme_API_TOKEN', '');

        if (empty(get_site_option("SELLER_TOKEN"))) {
            update_site_option('SELLER_TOKEN', bin2hex(static::randomBytes(20)));
        }

    }

    public static function plugin_deactivation()
    {
        global $wpdb;
        $query = "DROP TABLE {$wpdb->prefix}dokme_sync";
        $wpdb->query($query);

        $query = "DROP TABLE {$wpdb->prefix}dokme_synchronize";
        $wpdb->query($query);
    }

    public static function DokmeAdminMenu()
    {
        add_menu_page(__('Dokme', 'access'), __('دکمه', 'access'), 'manage_options', __('dokme-settings', 'vibe'), array('Dokme', 'loadView'), WP_PLUGIN_URL . '/dokme/assets/images/logo.gif');
        add_submenu_page('dokme-settings', __('dokme-products', 'access'), __('کالاها'), 'manage_options', __('dokme-products'), array('Dokme', 'dokmeProducts'));
        add_submenu_page('dokme-settings', __('dokme-category', 'access'), __('دسته‌بندی‌ها'), 'manage_options', __('dokme-category'), array('Dokme', 'dokmeCategory'));
    }

    public static function dokmeProducts()
    {
        $list = new Dokme_ProductsList();
        $list->prepare_items();

        echo "<form method='post'><input type='hidden' name='page' value='" . $_REQUEST['page'] . "'/>" . $list->display() . "</form>";
    }

    public static function dokmeCategory()
    {
        $categories = dokme_getCategories::getStoreCategories();
        ?>
        <br/>
        <div class="alert alert-dismissable" id="MessageBox" role="alert" hidden>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">دسته بندی های منتخب</div>
                <div class="panel-body">
                    <p>فقط کالاهایی به دکمه ارسال میشوند که در دسته بندی انتخاب شده باشد.</p>
                    <form class="save-category" action="">
                        <div class="dokme-tree">
                            <?php echo dokme_getCategories::traverse($categories) ?>
                        </div>
                        <button type="button" class="btn btn-success" id="saveCategory">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public static function loadView()
    {
        require_once 'includes/view.php';
    }

    public static function registerStyleScript($hook)
    {
        wp_enqueue_media();
        wp_register_style('dokme-style', plugins_url('/assets/css/dokme-style.css?h=f05f4', __FILE__));
        wp_enqueue_style('dokme-style');
        //wp_enqueue_script('ajax-script', plugins_url('/assets/js/dokme_ajax.js?h=f05f4', __FILE__), array('jquery'));
        wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
    }

    static function deleteProduct($postId)
    {
        $sendRequest = new Dokme_SendRequest();
        $sendRequest->deleteProducts($postId);
    }

    public static function updateProduct($postId, $post_after)
    {
        if ($post_after->post_type != 'product') {
            return;
        }

        $sendRequest = new Dokme_SendRequest();
        if ($post_after->post_status == 'publish') {
            $sendRequest->syncProduct(array($postId));
        } else {
            $sendRequest->deleteProducts($postId);
            Dokme_Dbsync::update($postId);
        }
    }

    public static function updateStock($product)
    {
        if ($product->post_type != 'product') {
            return;
        }

        $sendRequest = new Dokme_SendRequest();
        if ($product->status == 'publish') {
            $sendRequest->syncProduct(array($product->id));
        } else {
            $sendRequest->deleteProducts($product->id);
            Dokme_Dbsync::update($product->id);
        }
    }

    public static function updateToken()
    {
        $status = update_site_option("DOKME_API_TOKEN", sanitize_text_field($_POST['token']));
        $message = $status ? '<p>توکن با موفقیت به روز رسانی شد.</p>' : '<p>مقدار توکن جدید با توکن ذخیره شده برابر میباشد.</p>';

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
        $result = array('status' => false, 'code' => 401, 'message' => '<p>وارد کردن توکن الزامی میباشد.</p>');

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

    public static function selectedCategories()
    {
        $status = update_site_option("DOKME_SELECTED_CATEGORIES", $_POST['categories']);
        $message = $status ? '<p>با موفقیت ذخیره شد.</p>' : '<p>خطایی در ذخیره سازی وجود دارد.</p>';

        echo json_encode(array('status' => $status, 'message' => $message));
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

    public static function randomBytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strongSource);
            if (!$strongSource) {
                trigger_error(
                    'openssl was unable to use a strong source of entropy. ' .
                    'Consider updating your system libraries, or ensuring ' .
                    'you have more available entropy.',
                    E_USER_WARNING
                );
            }

            return $bytes;
        }

        trigger_error(
            'You do not have a safe source of random data available. ' .
            'Install either the openssl extension, or paragonie/random_compat. ' .
            'Falling back to an insecure random source.',
            E_USER_WARNING
        );

        return static::insecureRandomBytes($length);
    }

    public static function insecureRandomBytes($length)
    {
        $byteLength = 0;
        $length *= 2;
        $bytes = '';
        while ($byteLength < $length) {
            $bytes .= static::hash(uniqid('-') . uniqid(mt_rand(), true), 'sha512', true);
            $byteLength = strlen($bytes);
        }
        $bytes = substr($bytes, 0, $length);

        return pack('H*', $bytes);
    }

    public static function hash($string, $type = null, $salt = false)
    {
        return hash(strtolower($type), $salt . $string);
    }
}
