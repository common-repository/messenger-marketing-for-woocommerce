<?php
if (!defined('ABSPATH')) {
    exit();
}

function chatchamp_webhook_exists($webhook_id)
{
    $webhook = new WC_Webhook($webhook_id);
    $post_data = $webhook->get_post_data();

    if ($post_data) {
        return true;
    }

    return false;
}

function chatchamp_init_webhook()
{
    WC()->api->includes();

    $chatchamp_order_created_webhook_id = WC_Admin_Settings::get_option('chatchamp_order_created_webhook_id', NULL);
    $order_created_webhook_data = chatchamp_order_created_webhook_data();
    if ($chatchamp_order_created_webhook_id == NULL || !chatchamp_webhook_exists($chatchamp_order_created_webhook_id)) {
        chatchamp_create_webhook('chatchamp_order_created_webhook_id', $order_created_webhook_data);
    }
    chatchamp_update_webhook($chatchamp_order_created_webhook_id, $order_created_webhook_data);

    $chatchamp_order_updated_webhook_id = WC_Admin_Settings::get_option('chatchamp_order_updated_webhook_id', NULL);
    $order_updated_webhook_data = chatchamp_order_updated_webhook_data();
    if ($chatchamp_order_updated_webhook_id == NULL || !chatchamp_webhook_exists($chatchamp_order_updated_webhook_id)) {
        chatchamp_create_webhook('chatchamp_order_updated_webhook_id', $order_updated_webhook_data);
    }
    chatchamp_update_webhook($chatchamp_order_updated_webhook_id, $order_updated_webhook_data);

    # the following webhook is currently not used
    $chatchamp_add_to_cart_webhook_id = WC_Admin_Settings::get_option('chatchamp_add_to_cart_webhook_id', NULL);
    $add_to_cart_webhook_data = chatchamp_add_to_cart_webhook_data();
    if ($chatchamp_add_to_cart_webhook_id == NULL || !chatchamp_webhook_exists($chatchamp_add_to_cart_webhook_id)) {
        chatchamp_create_webhook('chatchamp_add_to_cart_webhook_id', $add_to_cart_webhook_data);
    }
    chatchamp_update_webhook($chatchamp_add_to_cart_webhook_id, $add_to_cart_webhook_data);
}

function chatchamp_pause_webhooks()
{
    $chatchamp_order_created_webhook_id = WC_Admin_Settings::get_option('chatchamp_order_created_webhook_id', NULL);
    chatchamp_pause_webhook($chatchamp_order_created_webhook_id);

    $chatchamp_order_updated_webhook_id = WC_Admin_Settings::get_option('chatchamp_order_updated_webhook_id', NULL);
    chatchamp_pause_webhook($chatchamp_order_updated_webhook_id);

    $chatchamp_add_to_cart_webhook_id = WC_Admin_Settings::get_option('$chatchamp_add_to_cart_webhook_id', NULL);
    chatchamp_pause_webhook($chatchamp_add_to_cart_webhook_id);
}

function chatchamp_order_updated_webhook_data()
{
    return array(
        "webhook" => array(
            'name' => 'Messenger Marketing Webhook',
            'topic' => 'order.updated',
            "delivery_url" => CHATCHAMP_CHATCHAMP_API_URL . "/woocommerce/order/update"
        )
    );
}

function chatchamp_order_created_webhook_data()
{
    return array(
        "webhook" => array(
            'name' => 'Messenger Marketing Webhook',
            'topic' => 'order.created',
            "delivery_url" => CHATCHAMP_CHATCHAMP_API_URL . "/woocommerce/order/update"
        )
    );
}

function chatchamp_add_to_cart_webhook_data()
{
    return array(
        "webhook" => array(
            'name' => 'Messenger Marketing Webhook',
            'topic' => 'action.woocommerce_add_to_cart',
            "delivery_url" => CHATCHAMP_CHATCHAMP_API_URL . "/woocommerce/cart/update"
        )
    );
}

function chatchamp_update_webhook($webhook_id, $webhook_data)
{
    $webhooks = new WC_API_Webhooks(new WC_API_Server('/'));
    $result = $webhooks->edit_webhook($webhook_id, $webhook_data);
    $webhook = new WC_Webhook($webhook_id);
    $webhook->update_status('active');
    # the next line has to come after update_status
    $webhook->set_api_version('wp_api_v2');
}

function chatchamp_pause_webhook($webhook_id)
{
    $webhook = new WC_Webhook($webhook_id);
    $webhook->update_status('paused');
}

function chatchamp_create_webhook($webhook_option, $webhook_data)
{
    $webhooks = new WC_API_Webhooks(new WC_API_Server('/'));
    $result = $webhooks->create_webhook($webhook_data);
    chatchamp_save_option($webhook_option, $result['webhook']['id']);
}

function chatchamp_save_option($option_name, $value)
{
    $settings = array(
        $option_name => array(
            'type' => 'text',
            'id' => $option_name,
        )
    );
    $data = array(
        $option_name => $value
    );

    woocommerce_update_options($settings, $data);
}

add_filter('woocommerce_webhook_payload', 'chatchamp_add_facebook_page_id_to_woocommerce_webhook_payload', 10, 4);
function chatchamp_add_facebook_page_id_to_woocommerce_webhook_payload($payload, $resource, $resource_id, $this_id)
{
    $payload['chatchampId'] = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_chatchamp_id', ''));
    return $payload;
}