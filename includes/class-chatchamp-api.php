<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Chatchamp_API')):

    class Chatchamp_API
    {
        const API_DESCRIPTION = 'chatchamp api user';

        private $plugin_version;

        public function __construct()
        {
            $this->plugin_version = chatchamp_get_plugin_version();
        }

        private function get_api_key()
        {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare("SELECT key_id, user_id, description, permissions, consumer_key, consumer_secret, last_access FROM {$wpdb->prefix}woocommerce_api_keys WHERE user_id = %d AND description = %s", get_current_user_id(), self::API_DESCRIPTION), ARRAY_A);
        }

        private function generate_consumer_key()
        {
            $consumer_key = 'ck_' . wc_rand_hash();
            $consumer_key_hash = wc_api_hash($consumer_key);
            return array($consumer_key, $consumer_key_hash);
        }

        private function create_api_key()
        {
            global $wpdb;

            list($consumer_key, $consumer_key_hash) = $this->generate_consumer_key();
            $consumer_secret = 'cs_' . wc_rand_hash();

            $data = array(
                'user_id' => get_current_user_id(),
                'description' => self::API_DESCRIPTION,
                'permissions' => 'read_write',
                'consumer_key' => $consumer_key_hash,
                'consumer_secret' => $consumer_secret,
                'truncated_key' => substr($consumer_key, -7)
            );

            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_api_keys',
                $data,
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );

            return array($consumer_key, $consumer_secret);
        }

        private function update_api_key($api_key)
        {
            global $wpdb;

            list($consumer_key, $consumer_key_hash) = $this->generate_consumer_key();

            $wpdb->update(
                $wpdb->prefix . 'woocommerce_api_keys',
                array('consumer_key' => $consumer_key_hash),
                array('key_id' => $api_key['key_id']),
                array('%s'),
                array('%d')
            );

            return array($consumer_key, $api_key['consumer_secret']);
        }

        public function save()
        {
            $api_enabled = get_option('woocommerce_api_enabled');
            if ($api_enabled == 'no') {
                update_option('woocommerce_api_enabled', 'yes');
            }

            $chatchamp_key = $this->get_api_key();

            if (is_null($chatchamp_key)) {
                list($consumer_key, $consumer_secret) = $this->create_api_key();
            } else {
                list($consumer_key, $consumer_secret) = $this->update_api_key($chatchamp_key);
            }

            $this->update_server($consumer_key, $consumer_secret);
        }

        public function update_server($consumer_key, $consumer_secret)
        {
            $chatchamp_id = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_chatchamp_id', ''));

            if ($chatchamp_id) {
                try {
                    $client = new GuzzleHttp\Client();
                    $response = $client->post(CHATCHAMP_CHATCHAMP_API_URL . '/woocommerce/setup', [
                        'json' => [
                            'shopUrl' => get_home_url(),
                            'cartUrl' => wc_get_cart_url(),
                            'chatchampId' => $chatchamp_id,
                            'pluginVersion' => $this->plugin_version,
                            'apiKey' => $consumer_key,
                            'apiSecret' => $consumer_secret
                        ]
                    ]);
                    $data = json_decode($response->getBody());
                    if ($data->success) {
                        update_option('chatchamp_successful', true, true);
                    }
                } catch (Exception $e) {
                }
            }
        }
    }
endif;