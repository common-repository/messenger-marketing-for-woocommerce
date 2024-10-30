<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Chatchamp_Settings')) :

    class Chatchamp_Settings extends WC_Settings_Page
    {

        private $chatchamp_api;

        public function __construct()
        {
            $this->id = 'chatchamp';
            $this->label = 'chatchamp';
            parent::__construct();
            $this->chatchamp_api = new Chatchamp_API();
        }

        function get_settings($current_section = '')
        {
            $setup_succesful = WC_Admin_Settings::get_option('chatchamp_successful', false);
            $desc = null;
            if ($setup_succesful) {
                $desc = 'Further settings can be found under <a href="https://www.chatchamp.io/settings?utm_source=WooCommerce_Store&utm_medium=Plugin&utm_campaign=WooCommerce_Plugin_Store" target="_blank">https://www.chatchamp.io/settings</a>.';
            } else {
                $desc = '<div class="update-nag" style="background-color:#ffba00;margin-bottom:20px;">Please make sure to sign up for free under <a href="https://www.chatchamp.io/?utm_source=WooCommerce_Store&utm_medium=Plugin&utm_campaign=WooCommerce_Plugin_Store" target="_blank">https://www.chatchamp.io/</a> before activating.</div>';
            }
            $settings = array(
                'general_options' => array(
                    'title' => __('General options', 'messenger-marketing-woocommerce'),
                    'type' => 'title',
                    'desc' => $desc,
                    'id' => 'chatchamp_settings_tab_section_start'
                ),
                'chatchamp_id' => array(
                    'name' => __('Chatchamp ID (REQUIRED)', 'messenger-marketing-woocommerce'),
                    'type' => 'text',
                    'desc' => __('The ID of your chatchamp account. You have to sign up at http://www.chatchamp.io to get your personal id.', 'messenger-marketing-woocommerce'),
                    'id' => 'chatchamp_settings_chatchamp_id',
                    'css' => 'min-width:350px;',
                    'desc_tip' => true
                ),
                'section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'chatchamp_settings_section_end'
                )
            );

            return apply_filters('wc_settings_tab_chatchamp_settings', $settings);
        }

        public function save()
        {

            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::save_fields($settings);

            $this->chatchamp_api->save();
        }

        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }

    }

endif;

return new Chatchamp_Settings();