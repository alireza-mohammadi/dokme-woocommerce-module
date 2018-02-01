<?php
include_once 'dokme_product.php';
include_once 'model/dokme_dbsync.php';

class Dokme_SendRequest
{

    /**
     * <p> Find product with {@link getProductDetailById} and send request to {@link dokme.com}
     *     with {@link sendRequset} by <code> POST </code> method
     * </p>
     * @param array $ids
     * @return mixed|null
     */
    public function syncProduct($ids)
    {
        $products = array();

        if (empty($ids)) {
            return array('status' => false, 'message' => 'در ارسال خطایی وجود دارد.');
        }

        foreach ($ids as $id) {
            $result = Dokme_Product::getProductDetail($id);

            if (empty($result)) {
                continue;
            }

            $products[] = $result;
        }
        
        $result = array('status' => true, 'message' => 'ارسال به دکمه با موفقیت انجام شد.');
        if (!empty($products)) {
            $result = $this->sendRequset('products', 'POST', json_encode($products));
        }
        return $result;
    }

    /**
     * برای حذف محصولات از سایت دکمه
     *
     * @param $ids array $ids
     * @return mixed|null
     */
    public function deleteProducts($ids)
    {
        $body = array();
        $url = 'products';

        if (is_array($ids)) {
            $body = array('type' => 'selected', 'code' => $ids);
        } else {
            $url .= "/$ids";
        }

        $this->sendRequset($url, 'DELETE', json_encode($body));
    }

    /**
     * Called when need to send request to external server or site
     *
     * @param $url URL address of Server
     * @param $method GET or POST
     * @param $body content of request like product
     * @return mixed
     */
    public function sendRequset($url, $method, $body)
    {
        $apiToken = get_site_option('DOKME_API_TOKEN');
        if ($apiToken) {

            $args = array(
                'method' => $method,
                'body' => $body,
                'headers' => array(
                    'Authorization' => "Bearer $apiToken",
                    'User-Agent' => 'WordPress_Module_2.0.0'
                ),
            );
            $response = wp_remote_request("http://dokme.com/api/v1/public/$url", $args);

            switch ($response['response']['code']) {
                case 200:
                    return array('status' => true, 'message' => 'ارسال به دکمه با موفقیت انجام شد.');
                case 401:
                    return array('status' => false, 'message' => 'خطا! توکن وارد شده معتبر نمیباشد.');
                case 403:
                    return array('status' => false, 'message' => 'خطا! دسترسی  مجاز نمیباشد.');
                case 408:
                    return array('status' => false, 'message' => 'خطا! درخواست منقضی شد.');
                case 429:
                case 0:
                    return array('status' => false, 'code' => 429, 'message' => 'فرایند ارسال محصولات به طول می انجامد لطفا صبور باشید.');
                default:
                    return array('status' => false, 'message' => 'error: ' . $response['response']['code']);
            }
        }
        return array('status' => false, 'message' => 'ابتدا توکن را از سرور دکمه دریافت کنید');
    }

}
