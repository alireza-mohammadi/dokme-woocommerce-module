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
        pageCount = Math.ceil(productsLenght / chunk);
        //setPercentage(0);
        jQuery('#sync-progress').show();
        syncAllProducts();
    });

    jQuery('.dokme-tree .collapse').on('click', function(e) {
        $(this).parent().toggleClass('open');
    });

    jQuery('#saveCategory').on('click', function() {
        var categories = [];
        jQuery('input[type=checkbox]:checked').each(function(i) {
            categories[i] = jQuery(this).val();
        });

        selectedCategories(categories);
    });

    $('input').on('click', function(e) {
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
            }).done(function(data) {
                if (data.status) {
                    message(true, data.message);
                } else {
                    message(false, data.message);
                }
            }).fail(function() { });
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
        }).done(function(data) {
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
        }).fail(function() {
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
        }).done(function(data) {
            if (data.status) {
                message(true, data.message);
            } else {
                message(false, data.message);
            }
        }).fail(function() {

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