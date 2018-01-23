jQuery(function() {
    var messageBox = jQuery('#MessageBox');
    var pageNumber = 0;
    var pageCount = 0;
    var chunk = 50;

    jQuery('#updateToken').on('click', function() {
        updateToken();
    });

    jQuery('#syncAllCats').on('click', function() {
        syncAllCats();
    });

    jQuery('#syncAllProducts').on('click', function() {
        console.log('run-ajax');
        pageCount = Math.ceil(productsLenght / chunk);
        setPercentage(0);
        jQuery('#progress').show();
        syncAllProducts();
    });

    function updateToken() {
        var token = jQuery('#ApiTokenInput').val();
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
            }).done(function(data) {
                if (data.status) {
                    message(true, data.message);
                } else {
                    message(false, data.message);
                }
            }).fail(function() { });
        } else {
            message(false, 'فیلد توکن را وارد کنید.');
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
        }).done(function(data) {
            if (data.status) {
                message(true, data.message);
            } else {
                message(false, data.message);
            }
        }).fail(function() {});
    }

    function syncAllProducts() {
        if (productsLenght === 0) {
            message(true, 'همه محصولات ارسال شده است.');
            jQuery('#progress').hide();
            return;
        }

        if (pageNumber === pageCount) {
            message(true, 'همه محصولات به سایت شرینو ارسال شد.');
            jQuery('#progress').hide();
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
        }).done(function(data) {
            if (data.status === false) {
                if (data.code === 429) {
                    messageBox.show(500)
                            .html(data.message)
                            .removeClass('alert-danger')
                            .removeClass('alert-success')
                            .addClass('alert-warning');
                    setTimeout(syncAllProducts, 61 * 1000);
                } else {
                    message(data.status, data.message);
                    jQuery('#progress').hide();
                }
            } else {
                pageNumber++;
                var percentage = Math.round((100 * (pageNumber * chunk)) / productsLenght);
                setPercentage(percentage);
                syncAllProducts();
            }
        }).fail(function() {
            // pageNumber = 0;
        });
    }

    function setPercentage(percentage) {
        percentage = percentage > 100 ? 100 : percentage;
        percentage = percentage < 0 ? 0 : percentage;
        jQuery('#sync-progress')
                .css('width', percentage + "%")
                .attr('aria-valuemin', percentage + '%')
                .html(percentage + '%');
    }

    function message(status, message) {
        messageBox.show(500)
                .html(message)
                .removeClass('alert-danger')
                .removeClass('alert-success')
                .removeClass('alert-warning')
                .addClass(status ? 'alert-success' : 'alert-danger');
    }
});