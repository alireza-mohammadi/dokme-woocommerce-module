<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once 'model/dokme_dbsync.php';
require_once 'dokme_sendRequest.php';

class Dokme_ProductsList extends WP_List_Table
{

    function __construct()
    {
        parent::__construct(
            array(
                'singular' => 'sync',
                'plural' => 'synchronization',
                'ajax' => false
            )
        );
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'product_id':
            case 'status':
            case 'updated_at':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function column_title($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete' => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );

        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s', $item['title'], $item['ID'], $this->row_actions($actions));
    }

    function column_product_name($item)
    {
        /*
        $actions = array(
            'send' => sprintf('<a href="?page=%s&paged=%s&action=%s&product=%s">ارسال</a>', $_REQUEST['page'], '', 'send', $item['product_id']),
            'delete' => sprintf('<a href="?page=%s&paged=%s&action=%s&product=%s">حذف</a>', $_REQUEST['page'], '', 'delete', $item['product_id'])
        );

        return sprintf('%1$s %2$s', $item['product_name'], $this->row_actions($actions));
        */
    }

    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['product_id']);
    }

    /**
     * نام ستون های جدول را برمیگرداند
     * @return array
     */
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ایدی',
            'product_id' => 'شماره کالا',
            'product_name' => 'نام کالا',
            //'status' => 'وضعیت ارسال',
            //'updated_at' => 'تاریخ به‌روز‌رسانی'
        );
    }

    /**
     *  نوع مرتب سازی ستون ها را برگشت میدهد.
     * @return array
     */
    function get_sortable_columns()
    {
        return array(
            'id' => array('id', true),
            'status' => array('status', true),
            'updated_at' => array('updated_at', false)
        );
    }

    function get_bulk_actions()
    {
        /*
        $actions = array(
            'synchronize' => 'ارسال کالاها',
            'delete' => 'حذف کالاها',
        );
        return $actions;
        */
    }

    function process_bulk_action()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        if (!isset($_GET['product'])) {
            return;
        }

        $productId = $_GET['product'];

        if ($this->current_action() === 'send') {
            $sendRequest = new Dokme_SendRequest();
            $result = $sendRequest->syncProduct(array($productId));

            if ($result['status']) {
                Dokme_Dbsync::update($productId);
            }
        }

        if ($this->current_action() === 'delete') {
            $sendRequest = new Dokme_SendRequest();
            $sendRequest->deleteProducts($productId);

            Dokme_Dbsync::delete($productId);
        }
    }

    /**
     * Retrieve Synced’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_syncd($per_page, $page_number)
    {
        global $wpdb;

        $query = "SELECT SQL_CALC_FOUND_ROWS a.* , pl.`post_title` as product_name FROM {$wpdb->prefix}dokme_sync a LEFT JOIN {$wpdb->prefix}posts pl ON (pl.`id` = a.`product_id`)";

        if (!empty($_REQUEST['orderby'])) {
            $query .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $query .= $_REQUEST['order'] ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $query .= " LIMIT $per_page OFFSET " . ($page_number - 1) * $per_page;

        return $wpdb->get_results($query, 'ARRAY_A');
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count()
    {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}dokme_sync";

        return $wpdb->get_var($query);
    }

    function prepare_items()
    {
        $per_page = 50;

        //
        $hidden = array();
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        //
        $this->process_bulk_action();

        //
        $current_page = $this->get_pagenum();
        $this->items = $this->get_syncd($per_page, $current_page);

        //
        $total_items = $this->record_count();
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

}
