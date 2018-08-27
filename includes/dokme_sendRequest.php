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
            return array('status' => false, 'message' => '<p>در ارسال خطایی وجود دارد.</p>');
        }

        foreach ($ids as $id) {
            $result = Dokme_Product::getProductDetail($id);

            if (empty($result)) {
                continue;
            }

            $products[] = $result;
        }

        $result = array('status' => true, 'message' => '<p>ارسال به دکمه با موفقیت انجام شد.</p>');
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

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_URL, "http://dokme.test/api/v1/public/$url");

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization:Bearer $apiToken",
                    "User-Agent:WordPress_Module_2.0.8"
                )
            );

            curl_exec($curl);

            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($httpcode) {
                case 200:
                    return array('status' => true, 'message' => '<p>ارسال به دکمه با موفقیت انجام شد.</p>');
                case 401:
                    return array('status' => false, 'message' => '<p>خطا! توکن وارد شده معتبر نمیباشد.</p>');
                case 403:
                    return array('status' => false, 'message' => '<p>خطا! دسترسی مجاز نمیاشد.</p>');
                case 408:
                    return array('status' => false, 'message' => '<p>خطا! درخواست منقضی شده است.</p>');
                case 429:
                    return array('status' => false, 'code' => 429, 'message' => '<p>فرایند ارسال محصولات به طول می انجامد لطفا صبور باشید.</p>');
                case 0:
                    return array('status' => false, 'code' => 0, 'message' => '<p>فراید ارسال محصولات با خطا روبه‌رو شده است.</p>');
                default:
                    return array('status' => false, 'message' => '<p>error: ' . $httpcode . '</p>');
            }
        }
        return array('status' => false, 'message' => '<p>وارد کردن توکن الزامی میباشد.</p>');
    }

}
