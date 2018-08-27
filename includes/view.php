<?php
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
                    <a href="https://dokme.com/" style="float: left">
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
            <li>ابتدا توکن خود را از سایت <a href="https://dokme.com" target="_blank" title="دکمه - شبکه اجتماعی خرید">دکمه</a>
                دریافت کنید.
            </li>
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
                                       placeholder="توکن دریافتی خود از دکمه را وارد کنید"
                                       value="<?php echo get_site_option("DOKME_API_TOKEN") ?>">
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
                    <hr>
                    <table width="100%" style="margin-bottom: 50px;">
                        <tr>
                            <td>۴: کلید فراخوانی کالاها توسط دکمه.</td>
                            <td>
                                <strong style="color: red">درصورت لزوم به شما اطلاع داده می‌شود کلید را برای ما در تیکت
                                    ارسال کنید.</strong>
                                <input type="text"
                                       readonly
                                       id="seller-token"
                                       placeholder="کلید فراخوانی کالاها توسط دکمه"
                                       value="<?php echo get_site_option("SELLER_TOKEN") ?>">
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
<style>
    .dokme-btn {
        display: inline-block;
        padding: 5px;
        background: #469A46;
        color: #fff;
        border: solid 1px #707070;
        text-decoration: none;
        text-align: center;
        min-width: 80px;
        border-radius: 50px;
        font-weight: 400;
        font-size: 12px;
        cursor: pointer;
    }

    #dokme-api-token, #seller-token {
        width: 100%;
        border-radius: 5px;
        padding: 10px;
    }

    .dokme-progress-bar {
        background-color: #ccc;
        height: 15px;
        padding: 1px;
        width: 100%;
        margin: 2px 0;

    }

    .dokme-progress-bar span {
        display: inline-block;
        float: right;
        height: 100%;
    }

    .blue span {
        background-color: #34c2e3;
    }
</style>
<script>
    jQuery(function () {
        var messageBox = jQuery('#MessageBox');
        var pageNumber = 0;
        var pageCount = 0;
        var chunk = 50;

        jQuery('#updateToken').on('click', function () {
            updateToken();
        });

        jQuery('#syncAllCats').on('click', function () {
            syncAllCats();
        });

        jQuery('#syncAllProducts').on('click', function () {
            pageCount = Math.ceil(productsLenght / chunk);
            //setPercentage(0);
            jQuery('#sync-progress').show();
            syncAllProducts();
        });

        jQuery('.dokme-tree .collapse').on('click', function (e) {
            $(this).parent().toggleClass('open');
        });

        jQuery('#saveCategory').on('click', function () {
            var categories = [];
            jQuery('input[type=checkbox]:checked').each(function (i) {
                categories[i] = jQuery(this).val();
            });

            selectedCategories(categories);
        });

        $('input').on('click', function (e) {
            var $checkbox = $(this).closest('li');
            if ($checkbox.has('ul')) {
                $checkbox.find(':checkbox').not(this).prop('checked', this.checked);
            }
        });

        function updateToken() {
            var token = jQuery('#dokme-api-token').val();
            if (token) {
                messageBox.hide();
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    action: 'updateToken',
                    data: {
                        ajax: true,
                        action: 'updateToken',
                        token: token
                    }
                }).done(function (data) {
                    if (data.status) {
                        message(true, data.message);
                    } else {
                        message(false, data.message);
                    }
                }).fail(function () {
                });
            } else {
                message(false, '<p>فیلد توکن را وارد کنید.</p>');
            }
        }

        function syncAllCats() {
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajaxurl,
                action: 'syncAllCategories',
                data: {
                    action: 'syncAllCategories'
                }
            }).done(function (data) {
                if (data.status) {
                    message(true, data.message);
                } else {
                    message(false, data.message);
                }
            }).fail(function () {
            });
        }

        function syncAllProducts() {
            if (productsLenght === 0) {
                message(true, '<p>همه محصولات ارسال شده است.</p>');
                jQuery('#sync-progress').hide();
                return;
            }

            if (pageNumber === pageCount) {
                message(true, '<p>همه محصولات به سایت شرینو ارسال شد.</p>');
                jQuery('#sync-progress').hide();
                return;
            }

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajaxurl,
                action: 'syncAllProducts',
                data: {
                    action: 'syncAllProducts',
                    pageNumber: pageNumber,
                    chunk: chunk
                }
            }).done(function (data) {
                if (data.status === false) {
                    if (data.code === 429) {
                        messageBox.show(500)
                            .html(data.message)
                            .removeClass('updated')
                            .removeClass('error')
                            .addClass('update-nag');
                        setTimeout(syncAllProducts, 61 * 1000);
                    } else {
                        message(data.status, data.message);
                        jQuery('#sync-progress').hide();
                    }
                } else {
                    pageNumber++;
                    var percentage = Math.round((100 * (pageNumber * chunk)) / productsLenght);
                    setPercentage(percentage);
                    syncAllProducts();
                }
            }).fail(function () {
                // pageNumber = 0;
            });
        }

        function selectedCategories(categories) {
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajaxurl,
                action: 'selectedCategories',
                data: {
                    action: 'selectedCategories',
                    categories: categories
                }
            }).done(function (data) {
                if (data.status) {
                    message(true, data.message);
                } else {
                    message(false, data.message);
                }
            }).fail(function () {

            });
        }

        function setPercentage(percentage) {
            percentage = percentage > 100 ? 100 : percentage;
            //percentage = percentage < 0 ? 0 : percentage;
            jQuery('#sync-progress span')
                .css('width', percentage + '%')
                .next().text(percentage + '%');
        }

        function message(status, message) {
            messageBox.show(5)
                .html(message)
                .removeClass('updated')
                .removeClass('error')
                .removeClass('update-nag')
                .addClass(status ? 'updated' : 'error');
        }
    });
</script>