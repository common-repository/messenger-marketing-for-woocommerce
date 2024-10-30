<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_WooCommerce_Category
{

    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
        $show_on_category_page = (WC_Admin_Settings::get_option('chatchamp_fb_checkbox_show_on_category_page', 'yes') == 'yes' ? true : false);

        if ($show_on_category_page) {
            add_action('woocommerce_after_shop_loop_item', array(&$this, 'messenger_checkbox_plugin'));
        }
    }

    public function messenger_checkbox_plugin()
    {
        Messenger_Marketing_Facebook::messenger_checkbox_plugin('h4', 'small', 'addToCart');
    }
}

new Messenger_Marketing_WooCommerce_Category;