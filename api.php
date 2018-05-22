<?php
require_once 'dokmeApi.php';

$api = new DokmeApi();
if ($api->checkToken()) {

    if (isset($_GET['products'])) {
        $data = $api->getProducts();
    } elseif (isset($_GET['product'])) {
        $data = $api->getProduct((int)$_GET['product']);
    } elseif (isset($_GET['categories'])) {
        $data = $api->getCategories();
    }

    echo(json_encode(
        array(
            'status' => true,
            'data' => $data
        )
    ));

}