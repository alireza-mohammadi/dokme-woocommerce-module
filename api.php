<?php
require_once 'dokmeApi.php';

$api = new DokmeApi();
if ($api->auth()) {

    $data = array('status' => true);

    if (isset($_GET['products'])) {
        $data['data'] = $api->getProducts();
    } elseif (isset($_GET['product'])) {
        $data['data'] = $api->getProduct((int)$_GET['product']);
    } elseif (isset($_GET['categories'])) {
        $data['data'] = $api->getCategories();
    } elseif (isset($_GET['status'])) {
        $data['status'] = $api->getStatus();
    } elseif (isset($_GET['empty'])) {
        $data['status'] = $api->setEmpty();
    }

    echo wp_send_json($data);

}