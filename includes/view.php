<?php
$wcRestProducts = new WC_REST_Products_V1_Controller();
$product = $wcRestProducts->prepare_item_for_response(5739, 'GET');
file_put_contents(__DIR__ . '/log/5739.txt', json_encode($product));

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
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">تنظیمات ماژول دکمه</div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <a href="https://www.dokme.com" target="_blank" title="دکمه -شبکه اجتماعی خرید">
                                <img class="logo" src="<?php echo plugins_url('../assets/images/logo.png', __FILE__) ?>" alt="دکمه - شبکه اجتماعی خرید" />
                            </a>
                        </div>
                        <div class="col-sm-8">
                            <ul>
                                <li>ابتدا توکن خود را از سایت <a href="https://dokme.com" target="_blank" title="دکمه - شبکه اجتماعی خرید">دکمه</a> دریافت کنید.</li>
                                <li>در صورت بروز هر گونه خطا ابتدا از صحت توکن خود اطمینان حاصل کنید</li>
                                <li>کالاهای شما بعد از دریافت تصاویر در سایت دکمه قابل مشاهده میباشند</li>
                                <li>در صورت بروز هر گونه مشکل یا ابهامی می&zwnj;توانید با کارشناسان ما در ارتباط باشید</li>
                            </ul>
                        </div>
                    </div><!-- ./هدر  -->
                    <hr style="clear: both"/>
                    <div class="form-group">
                        <div class="alert alert-dismissable" id="MessageBox" role="alert" hidden>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="text-center" id="progress" hidden>
                            <p class="label label-default" id="progressText"></p>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-success active" id="sync-progress" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <h2 class="text-right dokme-text">۱: توکن دریافتی از دکمه</h2>
                        <p class="text-right">
                            قبل از ارسال کالا های فروشگاه برای دکمه لازم است طبق راهنمای دکمه، توکن را از پنل خود در سایت دکمه دریافت کنید و در فرم زیر جای گذاری کنید.
                        </p>
                        <div class="row">
                            <div class="col-xs-2">
                                <button type="Save" class="btn btn-success" id="updateToken"> ذخیره</button>
                            </div>
                            <div class="col-xs-10">
                                <input type="text" class="form-control" name="api_token" id = "ApiTokenInput" placeholder = "توکن دریافتی خود از دکمه را وارد کنید" value = "<?php echo get_site_option("DOKME_API_TOKEN") ?>" >
                            </div>
                        </div>
                    </div><!-- ./توکن  -->
                    <hr/>
                    <div class="form-group">
                        <h2 class="text-right dokme-text">۲: ارسال دسته‌بندی‌ها</h2>
                        <div class="row">
                            <div class="col-xs-2">
                                <button type="button" class="btn btn-success" name="send_categories" id="syncAllCats">ارسال</button>
                            </div>
                            <div class="col-xs-10">
                                <p class="text-right">در این قسمت میتوانید دسته‌بندی‌های محصولات خود را به دکمه ارسال کنید.</p>
                            </div>
                        </div>
                    </div><!-- ./ارسال دسته بندی  -->
                    <hr/>
                    <div class="form-group">
                        <h2 class="text-right dokme-text">۳: ارسال کالاها</h2>
                        <div class="row">
                            <div class="col-xs-2">
                                <button type="button" class="btn btn-success" name="synchronize_all" id="syncAllProducts" data-operation="start">ارسال</button>
                            </div>
                            <div class="col-xs-10">
                                <p class="text-right">در این قسمت میتوانید کالاهای خود را به دکمه ارسال کنید.</p>
                            </div>
                        </div>
                    </div><!-- ./ ارسال محصولات -->
                    <hr/>
                    <div class="form-group dokme-tip">
                        <ul>
                            <li><span>در صورت به روز رسانی ماژول ارسال محصولات الزامی میباشد.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
