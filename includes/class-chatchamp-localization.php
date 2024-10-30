<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Chatchamp_Localization')) :

    class Chatchamp_Localization
    {
        public function __construct()
        {
            add_action('wp_footer', array(&$this, 'inline_script'));
        }

        public function inline_script()
        {
            $my_current_lang = '';
            if (has_filter('wpml_current_language')) {
                $my_current_lang = apply_filters('wpml_current_language', NULL);
            }
            ?>
            <script type="application/javascript">
                window.chatchamp_locale = '<?php echo $my_current_lang; ?>';
            </script>
            <?php
        }
    }

endif;

return new Chatchamp_Localization();