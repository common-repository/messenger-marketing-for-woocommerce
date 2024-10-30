<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('Chatchamp_WooCommerce_ThankYou')):

    class Chatchamp_WooCommerce_ThankYou
    {

        public function __construct()
        {
            add_action('woocommerce_thankyou', array(&$this, 'call_thankyou'));
        }

        public function call_thankyou()
        {
            ?>
            <script type="text/javascript">
                window.chatchampThankYouPage = true;
            </script>
            <?php
        }
    }

endif;

new Chatchamp_WooCommerce_ThankYou;