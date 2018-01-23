<?php
/*
 * Plugin Name: Dokme
 * Plugin URI: https://dokme.com
 * Description: ارسال محصولات فروشگاه شما به <a href="https://dokme.com">دکمه</a>
 * Version: 2.0.0
 * Author: AliRezaMohammadi
 * Author URI: https://github.com/alireza-mohammadi
 * License: GPL
 * Text Domain: Dokme
 */
if (!defined('ABSPATH')) {
    die;
}

// path plugin
define('DOKME_PLUGIN_DIR', plugin_dir_path(__FILE__));

// hook
register_activation_hook(__FILE__, array('Dokme', 'plugin_activation'));
register_deactivation_hook(__FILE__, array('Dokme', 'plugin_deactivation'));

// class dokme
global $dokme;
require_once(DOKME_PLUGIN_DIR . 'class-dokme.php');
$dokme = new Dokme(__FILE__);

//function showMessage($class, $message)
//{
//    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
//}

// Ajax Request
add_action('wp_ajax_updateToken', array('Dokme', 'updateToken'));
add_action('wp_ajax_syncAllCategories', array('Dokme', 'syncAllCategories'));
add_action('wp_ajax_syncAllProducts', array('Dokme', 'syncAllProducts'));
