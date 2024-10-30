<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_WooCommerce_Product
{

    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
        $show_on_product_page = (WC_Admin_Settings::get_option('chatchamp_fb_checkbox_show_on_product_page', 'yes') == 'yes' ? true : false);
        if ($show_on_product_page) {
            $position = WC_Admin_Settings::get_option('chatchamp_position_on_product_page', 'woocommerce_after_add_to_cart_button');
            add_action($position, array(&$this, 'messenger_checkbox_plugin'));
        }
    }

    public function messenger_checkbox_plugin()
    {
        Messenger_Marketing_Facebook::messenger_checkbox_plugin('div', 'large', 'addToCart');
    }
}

new Messenger_Marketing_WooCommerce_Product;