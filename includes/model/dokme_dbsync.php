<?php

class Dokme_Dbsync
{

    public $id;
    public $product_id;
    public $status;
    public $created_at;
    public $updated_at;

    /**
     * حذف یک کالا از جدول لوکال
     * @global type $wpdb
     * @param type $productids
     * @return null
     */
    public static function delete($id)
    {
        global $wpdb;

        if (empty($id)) {
            return;
        }

        $sql = "DELETE FROM {$wpdb->prefix}dokme_sync WHERE product_id=$id";

        $wpdb->query($sql);
    }

    /**
     * به روز رسانی وضعیت و تاریخ اپدیت جدول لوکال
     * @global type $wpdb
     * @param type $productid
     * @return null
     */
    public static function update($ids)
    {
        global $wpdb;

        if (empty($ids)) {
            return;
        }

        $query = "UPDATE {$wpdb->prefix}dokme_sync SET `status`='ارسال شد',`updated_at`='" . date("Y-m-d H:i:s") . "' WHERE product_id=$ids";

        $wpdb->query($query);
    }

}
