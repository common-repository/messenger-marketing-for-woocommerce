<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_WooCommerce_Checkout
{

    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
        $show_on_checkout_page = (WC_Admin_Settings::get_option('chatchamp_fb_checkbox_show_on_checkout_page', 'yes') == 'yes' ? true : false);
        if ($show_on_checkout_page) {
            $position = WC_Admin_Settings::get_option('chatchamp_position_on_checkout_page', 'woocommerce_after_order_notes');
            add_action($position, array(&$this, 'messenger_checkbox_plugin'));
            add_action('woocommerce_checkout_update_order_meta', array(&$this, 'save_messenger_checkbox_fields'));
        }
    }

    public function messenger_checkbox_plugin()
    {
        Messenger_Marketing_Facebook::messenger_checkbox_plugin('h3', 'large', 'precheckout');
    }

    public function save_messenger_checkbox_fields($order_id)
    {
        update_post_meta($order_id, 'chatchamp_user_ref', chatchamp_get_user_ref());
        update_post_meta($order_id, 'chatchamp_customer_identifier', chatchamp_get_customer_identifier());

        if (!empty($_POST['messenger_checkbox_user_checked'])) {
            $checkbox_checked = sanitize_text_field($_POST['messenger_checkbox_user_checked']);
            update_post_meta($order_id, 'messenger_marketing_for_woocommerce_user_checked', $checkbox_checked);
        }
    }
}

new Messenger_Marketing_WooCommerce_Checkout;