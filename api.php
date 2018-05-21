<?php
require_once 'dokmeApi.php';
require_once 'dokmeApiProduct.php';
require_once 'dokmeApiProducts.php';
require_once 'dokmeApiCategories.php';

$api = new DokmeApi();
if (!$api->checkToken()) {
    return;
}

if (isset($_GET) && $_SERVER['QUERY_STRING'] === 'products') {
    $products = DokmeApiProducts::getProducts();
    return;
}

if (isset($_GET['product'])) {
    $products = DokmeApiProduct::getProduct($_GET['product']);
    return;
}

if (isset($_GET) && $_SERVER['QUERY_STRING'] === 'categories') {
    $categories = DokmeApiCategories::getCategories();
    return;
}