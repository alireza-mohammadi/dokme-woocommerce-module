<?php
/*
 * Plugin Name: Dokme
 * Plugin URI: https://dokme.com
 * Description: ارسال محصولات فروشگاه شما به <a href="https://dokme.com">دکمه</a>
 * Version: 2.0.6
 * Author: AliRezaMohammadi
 * Author URI: https://github.com/ialireza
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

// Ajax Request
add_action('wp_ajax_updateToken', array('Dokme', 'updateToken'));
add_action('wp_ajax_syncAllCategories', array('Dokme', 'syncAllCategories'));
add_action('wp_ajax_syncAllProducts', array('Dokme', 'syncAllProducts'));
add_action('wp_ajax_selectedCategories', array('Dokme', 'selectedCategories'));
