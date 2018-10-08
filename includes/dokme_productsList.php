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
            case 'product_id':
            case 'name':
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
            'product_id' => __('ایدی کالا'),
            'name' => __('نام محصول')
        );
    }

    /**
     *  نوع مرتب سازی ستون ها را برگشت میدهد.
     * @return array
     */
    function get_sortable_columns()
    {
        return array(
            'id' => array(
                'id', true
            )
        );
    }

    /**
     * Retrieve Synced’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_lists($per_page, $page_number)
    {
        global $wpdb;

        $tp = $wpdb->prefix . 'posts';

        $query = "SELECT `ID` AS `product_id` ,`post_title` AS `name` FROM " . $tp . " WHERE " . $tp . ".`post_type` = 'product' AND " . $tp . ".`post_status` = 'publish'";
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
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE `post_type` = 'product' AND `post_status` = 'publish'");
    }

    function prepare_items()
    {
        $per_page = 50;

        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns()
        );

        //
        $current_page = $this->get_pagenum();
        $this->items = $this->get_lists($per_page, $current_page);

        //
        $total_items = $this->record_count();
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

}
