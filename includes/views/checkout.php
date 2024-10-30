<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Chatchamp_WooCommerce_Checkout')):

    class Chatchamp_WooCommerce_Checkout
    {

        public function __construct()
        {
            add_action('woocommerce_checkout_update_order_meta', array(&$this, 'save_messenger_checkbox_fields'));
        }

        public function save_messenger_checkbox_fields($order_id)
        {
            update_post_meta($order_id, 'chatchamp_session_id', $_COOKIE['chatchamp_session_id']);
        }
    }

endif;

new Chatchamp_WooCommerce_Checkout;