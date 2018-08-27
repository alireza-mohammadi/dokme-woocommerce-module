<?php
require_once 'dokmeApi.php';

$api = new DokmeApi();
if ($api->auth()) {
    $data = array('status' => true);

    if (isset($_GET['products'])) {
        $data = array_merge($data, $api->getProducts($_GET['page']));
    } elseif (isset($_GET['product'])) {
        $data['data'] = $api->getProduct((int)$_GET['product']);
    } elseif (isset($_GET['categories'])) {
        $data['data'] = $api->getCategories();
    } elseif (isset($_GET['status'])) {
        $data['status'] = $api->getStatus();
    } elseif (isset($_GET['empty'])) {
        $data['status'] = $api->setEmpty();
    } elseif (isset($_GET['reload'])) {
        $data['status'] = $api->reloadDb();
    }

    echo wp_send_json($data);
}