<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Chatchamp_WooCommerce_Setup')):

    class Chatchamp_WooCommerce_Setup
    {

        public function __construct()
        {
            add_action('woocommerce_settings_saved', array(&$this, 'setup_changed'));
            add_action('wp_ajax_chatchamp_set_setup_successful', array(&$this, 'set_setup_successful'));
            if (is_admin()) {
                $this->needs_migration();
            }
        }

        function set_setup_successful()
        {
            update_option('chatchamp_successful', true, true);
            wp_die();
        }

        function setup_changed()
        {
            update_option('chatchamp_successful', false, true);
        }

        function needs_migration()
        {
            $chatchamp_id = sanitize_text_field(get_option('chatchamp_settings_chatchamp_id', ''));
            $page_id = sanitize_text_field(get_option('chatchamp_settings_facebook_page_id', ''));
            if (!$chatchamp_id && $page_id) {
                try {
                    $client = new GuzzleHttp\Client();
                    $response = $client->get(CHATCHAMP_CHATCHAMP_API_URL . '/woocommerce/migrate', [
                        'json' => [
                            'facebookPageId' => $page_id
                        ]
                    ]);
                    $data = json_decode($response->getBody());
                    if ($data->success) {
                        update_option('chatchamp_settings_chatchamp_id', $data->secureId);
                    }
                } catch (Exception $e) {
                }
            }
        }
    }

endif;

new Chatchamp_WooCommerce_Setup;

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
}