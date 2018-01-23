<?php
include 'dokme_functions.php';

class Dokme_Product
{

    /**
     * در این تابع جزییات یک محصول برگشت داده میشود
     *
     * @param int ProductId
     * @return Array
     */
    public static function getProductDetail($productId)
    {
        $wcRestProducts = new WC_REST_Products_V1_Controller();
        $product = $wcRestProducts->prepare_item_for_response($productId, 'GET');

        $price = dokme_array_get($product->data, 'regular_price');
        if (empty($price)) {
            $price = dokme_array_get($product->data, 'price');
        }

        $image = dokme_array_selected($product->data['images'], 'src');

        $productArray = array(
            'name' => dokme_array_get($product->data, 'name'),
            'code' => dokme_array_get($product->data, 'id'),
            'sku' => dokme_array_get($product->data, 'sku'),
            'price' => $price,
            'sale_price' => dokme_array_get($product->data, 'sale_price'),
            'discount' => self::_getDiscounts($product->data),
            'quantity' => (int)dokme_array_get($product->data, 'stock_quantity', 0),
            'weight' => dokme_array_get($product->data, 'weight'),
            'original_url' => dokme_array_get($product->data, 'permalink'),
            'brand_id' => '',
            'categories' => dokme_array_selected($product->data['categories'], 'id'),
            'short_content' => dokme_array_get($product->data, 'short_description'),
            'long_content' => dokme_array_get($product->data, 'description'),
            'meta_keywords' => '',
            'meta_description' => '',
            'image' => array_pop($image),
            'images' => $image,
            'attributes' => self::_getAttributes($product->data),
            'variants' => self::_getVariations($product->data),
            'available_for_order' => 1,
            'out_of_stock' => dokme_array_get($product->data, 'in_stock'),
            'tags' => dokme_array_selected($product->data['tags'], 'name')
        );

        return $productArray;
    }

    public static function _getDiscounts(array $data)
    {
        $start_date = dokme_array_get($data, 'date_on_sale_from');
        $end_date = dokme_array_get($data, 'date_on_sale_to');

        $regular_price = dokme_array_get($data, 'regular_price');
        $sale_price = dokme_array_get($data, 'sale_price');

        if (empty($sale_price)) {
            return array();
        }

        $discount = array(
            'amount' => max($regular_price - $sale_price, 0),
            'start_date' => !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : '0000-00-00 00:00:00',
            'end_date' => !empty($end_date) ? date('Y-m-d H:i:s', strtotime($end_date)) : '0000-00-00 00:00:00',
            'quantity' => 0,
            'tax' => 0,
            'type' => 0
        );

        return $discount;
    }

    public static function _getAttributes(array $data)
    {
        if (empty(dokme_array_get($data, 'attributes'))) {
            return array();
        }

        $dataset = dokme_array_get($data, 'attributes');

        $attributes = array();
        foreach ($dataset as $data) {
            $attributes [] = array(
                'label' => $data['name'],
                'value' => implode(', ', $data['options'])
            );
        }

        return $attributes;
    }

    public static function _getVariations(array $data)
    {
        if (empty(dokme_array_get($data, 'variations'))) {
            return array();
        }

        try {
            $variations = dokme_array_get($data, 'variations');
            $attributes = dokme_array_get($data, 'attributes');

            $listVariations = array();
            foreach ($variations as $i => $data) {

                $discount = array();
                if (!empty($data['sale_price'])) {
                    $start_date = $data['date_on_sale_from'];
                    $end_date = $data['date_on_sale_to'];

                    $discount = array(
                        'amount' => max($data['regular_price'] - $data['sale_price'], 0),
                        'start_date' => !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : '0000-00-00 00:00:00',
                        'end_date' => !empty($end_date) ? date('Y-m-d H:i:s', strtotime($end_date)) : '0000-00-00 00:00:00',
                        'quantity' => 0,
                        'tax' => 0,
                        'type' => 0
                    );
                }

                if (count($data['attributes'])) {
                    foreach ($attributes as $attribute) {
                        $label = urldecode($data['attributes'][0]['name']);
                        $value = $data['attributes'][0]['option'];

                        if (count($attributes)) {
                            foreach ($attribute['options'] as $option) {

                                if ($value !== $option) {
                                    continue;
                                }

                                $listVariations [] = array(
                                    'code' => $data['id'],
                                    'quantity' => $data['stock_quantity'],
                                    'sku' => $data['sku'],
                                    'price' => $data['regular_price'],
                                    'discount' => $discount,
                                    'default_value' => $i === 0 ? '1' : '0',
                                    'variation' => array($data['id'] => array('label' => $attribute['name'], 'value' => $option))
                                );
                            }
                        } else {
                            if (($label === $attribute['name'])) {
                                continue;
                            }

                            foreach ($attribute['options'] as $option) {
                                $listVariations [] = array(
                                    'code' => $data['id'],
                                    'quantity' => $data['stock_quantity'],
                                    'sku' => $data['sku'],
                                    'price' => $data['regular_price'],
                                    'discount' => $discount,
                                    'default_value' => $i === 0 ? '1' : '0',
                                    'variation' => array(
                                        array('label' => $attribute['name'], 'value' => $option),
                                        array('label' => $label, 'value' => $value)
                                    )
                                );
                            }
                        }
                    }
                } else {
                    $temp = array();
                    $values = dokme_array_get($data, 'attributes');

                    foreach ($values as $key => $value) {
                        $temp [] = array(
                            'label' => urldecode($value['name']),
                            'value' => urldecode($value['option'])
                        );
                    }

                    $listVariations [] = array(
                        'code' => $data['id'],
                        'quantity' => $data['stock_quantity'],
                        'sku' => $data['sku'],
                        'price' => $data['regular_price'],
                        'discount' => $discount,
                        'default_value' => $i === 0 ? '1' : '0',
                        'variation' => $temp
                    );
                }
            }

            return $listVariations;
        } catch (Exception $exc) {

        }
    }

}
