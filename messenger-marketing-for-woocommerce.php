<?php
/**
 * Plugin Name: Messenger Marketing for WooCommerce
 * Plugin URI: https://www.chatchamp.com/
 * Description: Recover abandoned carts with messenger marketing | Remind customers of unpaid payments | Retarget customers on Facebook Messenger
 * Author: chatchamp UG (haftungsbeschränkt)
 * Author URI: https://www.chatchamp.com/
 * Version: 1.8.15
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: © 2019 chatchamp UG (haftungsbeschränkt).
 */

defined('ABSPATH') or die();

// consts
define('CHATCHAMP_CHATCHAMP_API_URL', 'https://api.chatchamp.io');
//define('CHATCHAMP_CHATCHAMP_API_URL', 'https://5f867ec0.ngrok.io');
//define('CHATCHAMP_CHATCHAMP_API_URL', 'https://api.staging.chatchamp.io');

define('CHATCHAMP_PLUGIN_DIR', plugin_dir_path(__FILE__));

// composer autoload
require_once CHATCHAMP_PLUGIN_DIR . '/vendor/autoload.php';

include_once 'includes/debug.php';
include_once 'includes/class-chatchamp-api.php';
include_once 'includes/webhook.php';

class Messenger_Marketing_For_Woocommerce
{
    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
    }

    public function activate_plugin()
    {
        chatchamp_debug_to_console('huhu');
        (new Chatchamp_API())->save();
        chatchamp_init_webhook();
    }
}

new Messenger_Marketing_For_Woocommerce();

add_action('wp_loaded', 'chatchamp_bootstrap');

function chatchamp_get_plugin_version()
{
    if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . "/wp-admin/includes/plugin.php";
    }
    $plugin_data = get_plugin_data(__FILE__);
    return $plugin_data['Version'];
}

function chatchamp_bootstrap()
{
    if (!class_exists('WooCommerce')) {
        require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        add_action('admin_notices', 'chatchamp_woocommerce_missing_notice');
    } else {
        register_deactivation_hook(__FILE__, 'chatchamp_pause_webhooks');
        register_deactivation_hook(__FILE__, 'chatchamp_deactivation_webhook');

        define('CHATCHAMP_PLUGIN_NAME', plugin_basename(__FILE__));

        include_once 'includes/views/checkout.php';
        include_once 'includes/views/product.php';
        include_once 'includes/views/thank_you.php';
        include_once 'includes/facebook/send_to_messenger.php';
        include_once 'includes/class-chatchamp-localization.php';
        include_once 'includes/class-chatchamp-setup.php';
        include_once 'includes/utils.php';

        include_once 'public/class-chatchamp-public.php';

        new Chatchamp_Public('messenger-marketing-for-woocommerce');

        add_filter('woocommerce_get_settings_pages', 'chatchamp_settings_page');
        add_filter("plugin_action_links_" . CHATCHAMP_PLUGIN_NAME, 'chatchamp_add_settings_link');
    }
}

function chatchamp_settings_page($settings)
{
    $settings[] = include('includes/class-chatchamp-settings.php');
    return $settings;
}

function chatchamp_add_settings_link($links)
{

    $settings_link = '<a href="admin.php?page=wc-settings&tab=chatchamp">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

function chatchamp_woocommerce_missing_notice()
{
    $message = 'Chatchamp Messenger Marketing plugin requires WooCommerce to be activated.';
    ?>
    <div class="error fade">
        <p>
            <strong><?php echo esc_html($message); ?></strong>
        </p>
    </div>
    <?php
}
