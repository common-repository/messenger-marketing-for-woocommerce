<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Chatchamp_Facebook')):

    class Chatchamp_Facebook
    {
        public function __construct()
        {
            add_shortcode('chatchamp_growth_tool', array('Chatchamp_Facebook', 'growth_tool'));

            // deprecated
            add_shortcode('chatchamp_checkbox', array('Chatchamp_Facebook', 'messenger_checkbox_shortcode_plugin'));
            add_shortcode('chatchamp', array('Chatchamp_Facebook', 'messenger_checkbox_shortcode_plugin'));
        }

        static public function messenger_checkbox_shortcode_plugin()
        {
            return static::messenger_checkbox_plugin('addToCart');
        }

        static public function messenger_checkbox_plugin($event)
        {
            ob_start();
            require __DIR__ . '/../../public/partials/checkbox-plugin.php';
            $contents = ob_get_contents();
            ob_get_clean();
            return $contents;
        }

        static public function growth_tool($atts)
        {
            $a = shortcode_atts(array(
                'id' => 'none'
            ), $atts);
            ob_start();
            $id = $a['id'];
            require __DIR__ . '/../../public/partials/chatchamp-growth-tool.php';
            $contents = ob_get_contents();
            ob_get_clean();
            return $contents;
        }

    }

endif;

new Chatchamp_Facebook;