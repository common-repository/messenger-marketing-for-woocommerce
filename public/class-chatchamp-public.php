<?php

class Chatchamp_Public
{
    private $plugin_name;
    private $plugin_version;

    public function __construct($plugin_name)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_version = chatchamp_get_plugin_version();

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles'));
        add_action('admin_notices', array($this, 'display_admin_notice'));
    }

    // only possible after posts_selection, wp_enqueue_scripts is called after posts_selection
    public function page()
    {
        if (is_shop() || is_home() || is_front_page()) {
            return 'home';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
                return 'thank_you';
            } else {
                return 'checkout';
            }
        } elseif (is_product()) {
            return 'product';
        } elseif (is_product_category()) {
            return 'category';
//        } elseif (is_product_tag()) {
//            return 'product_tag';
//        } elseif (is_account_page()) {
//            return 'account';
        } else {
            return 'other';
        }
    }

    public function enqueue_scripts()
    {
        $chatchamp_id = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_chatchamp_id', ''));
        $page = $this->page();
        wp_enqueue_script('chatchamp', CHATCHAMP_CHATCHAMP_API_URL . '/js/chatchamp-loader.js?id=' . $chatchamp_id . '&page=' . $page . '#asyncload', array('jquery'), null, true);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('chatchamp.css', plugin_dir_url(__FILE__) . 'css/chatchamp.css', array(), $this->plugin_version, 'all');
    }

    public function display_admin_notice()
    {
        $setup_succesful = WC_Admin_Settings::get_option('chatchamp_successful', false);

        if (!$setup_succesful && false === strpos($_SERVER['REQUEST_URI'], 'admin.php?page=wc-settings&tab=settings_tab_chatchamp')) :?>
            <div class="update-nag" style="background-color: #ffba00;">
                <?php echo 'Welcome to chatchamp! Please make sure that your installation is completed in <a href="' . admin_url('admin.php?page=wc-settings&tab=settings_tab_chatchamp') . '">Settings</a>.'; ?>
            </div>
        <?php endif;
    }
}