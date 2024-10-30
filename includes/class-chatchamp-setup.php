<?php
if (!defined('ABSPATH')) {
    exit();
}

function chatchamp_deactivation_webhook()
{
    $chatchamp_id = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_chatchamp_id', ''));
    if (!$chatchamp_id) {
        return false;
    }

    try {
        $client = new GuzzleHttp\Client();
        $client->post(CHATCHAMP_CHATCHAMP_API_URL . '/deactivate', [
            'json' => [
                'chatchampId' => $chatchamp_id
            ]
        ]);
    } catch (Exception $e) {
        return false;
    }

    return true;
}
