<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_WooCommerce_Cart
{

    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
//        woocommerce_before_cart_table
//        woocommerce_before_cart_contents
//        woocommerce_cart_contents
//        woocommerce_cart_coupon
//        woocommerce_after_cart_contents
//        woocommerce_after_cart_table
//        woocommerce_cart_collaterals
//        woocommerce_before_cart_totals
//        woocommerce_cart_totals_before_shipping
//        woocommerce_before_shipping_calculator
//        woocommerce_after_shipping_calculator
//        woocommerce_cart_totals_after_shipping
//        woocommerce_cart_totals_before_order_total
//        woocommerce_cart_totals_after_order_total
//        woocommerce_proceed_to_checkout
//        woocommerce_after_cart_totals
//        woocommerce_after_cart
//        add_action('woocommerce_proceed_to_checkout', 'messenger_checkbox_plugin');
    }

}

new Messenger_Marketing_WooCommerce_Cart;