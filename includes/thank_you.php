<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_WooCommerce_ThankYou
{
    private $user_ref;
    private $messenger_checkbox_user_checked;
    private $order_id;
    private $customer_identifier;

    function __construct()
    {
        add_filter('woocommerce_thankyou_order_received_text', array(&$this, 'woocommerce_thank_you_message'), 10, 2);
        add_action('chatchamp_after_fb_init', array(&$this, 'woocommerce_thank_you_script'), 10);
    }

    function woocommerce_thank_you_message($example, $order)
    {
        $this->order_id = $order->get_id();
        $this->messenger_checkbox_user_checked = get_post_meta($this->order_id, 'messenger_marketing_for_woocommerce_user_checked', true);
        $this->user_ref = chatchamp_get_user_ref();
        $this->customer_identifier = chatchamp_get_customer_identifier();
        return $example;
    }

    function woocommerce_thank_you_script()
    {
        if ('checked' == $this->messenger_checkbox_user_checked) {
            if (is_order_received_page()) {
                $fb_page_id = WC_Admin_Settings::get_option('chatchamp_settings_facebook_page_id', '');
                if (!$fb_page_id || !is_numeric($fb_page_id)) {
                    return;
                }

                echo "FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
		        'app_id':'" . CHATCHAMP_FACEBOOK_APP_ID . "',
		        'page_id':'{$fb_page_id}',
		        'ref':'{\"customerIdentifier\":\"" . $this->customer_identifier . "\", \"event\":\"checkout\",\"shoppingSystem\":\"wooCommerce\",\"orderId\":\"" . $this->order_id . "\"}',
		        'user_ref':'" . $this->user_ref . "'
		      });";
            }
        }
    }
}

new Messenger_Marketing_WooCommerce_ThankYou;