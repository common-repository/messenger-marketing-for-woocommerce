<?php

if (!defined('ABSPATH')) {
    exit();
}

use GuzzleHttp\Client;

function chatchamp_woocommerce_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    try {
        if (!isset($_COOKIE['chatchamp_session_id'])) {
            return;
        }
        $client = new Client();
        $chatchamp_id = sanitize_text_field(get_option('chatchamp_settings_chatchamp_id', ''));
        $client->post(CHATCHAMP_CHATCHAMP_API_URL . '/carts', [
            GuzzleHttp\RequestOptions::JSON => ['chatchampSessionId' => $_COOKIE['chatchamp_session_id'],
                'products' => [['id' => $product_id]]],
            'auth' => [
                $chatchamp_id,
                ''
            ]
        ]);
    } catch (Exception $e) {
    }
}

add_action('woocommerce_add_to_cart', 'chatchamp_woocommerce_add_to_cart', 10, 6);
