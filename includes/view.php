<?php
//$wcRestProducts = new WC_REST_Products_V1_Controller();
//$product = $wcRestProducts->prepare_item_for_response(5739, 'GET');
//file_put_contents(__DIR__ . '/log/5739.txt', json_encode($product));

//$this->define('WC_ABSPATH', dirname(WC_PLUGIN_FILE) . '/');
//    $args = array(
//        'posts_per_page' => -1,
//        'post_type' => 'product',
//        'post_status' => 'publish'
//    );
//    $query = new WP_Query($args);
//    $count = $query->post_count;

$countPost = wp_count_posts('product', '');
$count = $countPost->publish;

file_put_contents(__DIR__ . '/log/count.txt', json_encode($countPost));
?>
<script>
    var productsLenght =<?php echo $count; ?>;
</script>
<br/>
<div style="background: #fff; padding: 10px; width: 90%; margin: 0 auto;">
    <div class="container" style="width: 100%;margin: auto;max-width: 100%;">
        <table style="width: 100%;border-spacing: 0;">
            <tr>
                <td width="50%" style="padding-right:35px">
                    تنظیمات ماژول دکمه
                </td>
                <td width="50%" style="padding-left:35px">
                    <a href="https://dokme.ir/" style="float: left">
                        <img src="<?php echo plugins_url('../assets/images/logo.png', __FILE__) ?>">
                    </a>
                </td>
            </tr>
        </table>
    </div>
</div>
<div style="margin-bottom: 30px;">
    <div style="width: 90%; margin: auto; max-width: 100%;">
        <ul>
            <li>ابتدا توکن خود را از سایت <a href="https://dokme.com" target="_blank" title="دکمه - شبکه اجتماعی خرید">دکمه</a> دریافت کنید.</li>
            <li>در صورت بروز هر گونه خطا ابتدا از صحت توکن خود اطمینان حاصل کنید.</li>
            <li>کالاهای شما بعد از دریافت تصاویر در سایت دکمه قابل مشاهده میباشند.</li>
            <li>در صورت بروز هر گونه مشکل یا ابهامی می‌توانید با کارشناسان ما در ارتباط باشید.</li>
        </ul>
        <hr>
        <div class="notice" id="MessageBox" hidden></div>
        <div class="dokme-progress-bar blue" id="sync-progress" hidden>
            <span style="width:0%"></span>
            <div></div>
        </div>
        <hr>
        <table style="width: 100%;border-spacing: 0;background: #fff; border-radius:8px">
            <tr>
                <td class="contentdetail" style="padding: 0 35px 0 35px;">
                    <br>
                    <table width="100%" style="margin-bottom: 50px;">
                        <tr>
                            <td>۱: توکن دریافتی از دکمه</td>
                            <td>
                                <input type="text"
                                       id="dokme-api-token"
                                       placeholder = "توکن دریافتی خود از دکمه را وارد کنید"
                                       value = "<?php echo get_site_option("DOKME_API_TOKEN") ?>">
                            </td>
                            <td align="left">
                                <button type="button" id="updateToken" class="dokme-btn">ذخیره</button>
                            </td>
                        </tr>
                    </table>
                    <hr>
                    <table width="100%" style="margin-bottom: 50px;">
                        <tr>
                            <td>۲: ارسال دسته‌بندی‌ها</td>
                            <td>در این قسمت میتوانید دسته‌بندی‌های محصولات خود را به دکمه ارسال کنید.</td>
                            <td align="left">
                                <button class="dokme-btn"
                                        type="button"
                                        id="syncAllCats">
                                    ارسال
                                </button>
                            </td>
                        </tr>
                    </table>
                    <hr>
                    <table width="100%" style="margin-bottom: 50px;">
                        <tr>
                            <td>۳: ارسال کالاها</td>
                            <td>در این قسمت میتوانید کالاهای خود را به دکمه ارسال کنید.</td>
                            <td align="left">
                                <button class="dokme-btn"
                                        type="button"
                                        id="syncAllProducts">
                                    ارسال
                                </button>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div style="background: #fff; padding: 10px; width:90%; margin: auto; max-width: 100%;">
    <table style="width: 100%; border-spacing: 0;">
        <tr align="center">
            <td>
                در صورت به روز رسانی ماژول ارسال محصولات الزامی میباشد.
            </td>
        </tr>
    </table>
</div>

