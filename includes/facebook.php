<?php
if (!defined('ABSPATH')) {
    exit();
}

add_action('wp_head', 'chatchamp_load_facebook_sdk');
function chatchamp_load_facebook_sdk()
{
    ?>
    <script>
        (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk')
        );
    </script>
    <?php
}

add_action('wp_footer', 'chatchamp_init_facebook_sdk');
function chatchamp_init_facebook_sdk()
{
    $ip = chatchamp_get_the_user_ip();

    ?>
    <script>
        window.chatchamp_sendState = function (ip, event, state) {
            var settings = {
                crossDomain: true,
                url: "https://www.chatchamp.io/log",
                type: "POST",
                contentType: "application/json",
                dataType: 'jsonp',
                data: {
                    "ip": ip,
                    "event": event,
                    "state": state,
                    "hostname": window.location.hostname
                }
            };

            jQuery.ajax(settings).done(function (response) {
                console.log(response);
            });
        };

        window.fbAsyncInit = function () {
            FB.init({
                appId: '<?php echo CHATCHAMP_FACEBOOK_APP_ID ?>',
                xfbml: true,
                version: 'v2.6'
            });

            <?php do_action('chatchamp_after_fb_init'); ?>

            FB.Event.subscribe('messenger_checkbox', function (e) {
                window.chatchamp_sendState("<?php echo $ip; ?>", e.event, e.state);
                if (e.event === 'rendered') {
                    console.log("Plugin was rendered");
                } else if (e.event === 'checkbox') {
                    var checkboxState = e.state;
                    console.log("Checkbox state: " + checkboxState);
                    jQuery('#messenger_checkbox_user_checked').val(checkboxState);
                } else if (e.event === 'not_you') {
                    console.log("User clicked 'not you'");
                } else if (e.event === 'hidden') {
                    jQuery('#messenger-updates').hide();
                    console.log("Plugin was hidden");
                }
            });
        };
    </script>
    <?php
}

function chatchamp_messenger_checkbox_plugin($header_element, $button_size)
{
    $header_element = sanitize_text_field($header_element);
    $fb_page_id = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_facebook_page_id', ''));
    if (!$fb_page_id || !is_numeric($fb_page_id)) {
        return;
    }

    $origin = get_home_url();
    $user_ref = chatchamp_get_user_ref();
    $customer_identifier = chatchamp_get_customer_identifier();
    $facebook_app_id = CHATCHAMP_FACEBOOK_APP_ID;
    $header_title = sanitize_text_field(WC_Admin_Settings::get_option('chatchamp_settings_header_title', 'Receive order updates via messenger'));
    $header = '<' . $header_element . ' id="messenger-updates-header" style="margin-bottom: 0;">' . $header_title . '</' . $header_element . '>';

    $checkbox_plugin_code = <<<HTML
			<div class="fb-messenger-checkbox"  
			  origin={$origin}
			  page_id={$fb_page_id}
			  messenger_app_id={$facebook_app_id}
			  user_ref={$user_ref}
			  allow_login="true" 
			  prechecked="true"
			  size={$button_size}></div>
HTML;

    if (!chatchamp_subscribed()) {
        echo '<div id="messenger-updates">'
            . $header
            . $checkbox_plugin_code
            . woocommerce_form_field('messenger_checkbox_user_checked', array(
                'type' => 'text',
                'class' => array('chatchamp-woocommerce-hiddenfield form-row-wide'),
            ), '');

        echo '</div>';
    }

    echo <<<HTML
    <script>
        window.chatchamp_subscribeToFacebook = function(reference) {
            FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
                    'app_id':'{$facebook_app_id}',
                    'page_id':'{$fb_page_id}',
                    'ref':reference,
                    'user_ref':'{$user_ref}'
                  });    
        };
    
        jQuery('form.cart').submit(function() {
            window.chatchamp_subscribeToFacebook('{\"customerIdentifier\":\"{$customer_identifier}\", \"event\":\"addToCart\", \"shoppingSystem\":\"wooCommerce\"}');
            return true;
        });
        
        jQuery('.add_to_cart_button').on("click", function(){
            window.chatchamp_subscribeToFacebook('{\"customerIdentifier\":\"{$customer_identifier}\", \"event\":\"addToCart\", \"shoppingSystem\":\"wooCommerce\"}');
            return true;
        });
        
        jQuery('form[name="checkout"]').submit(function() {
            window.chatchamp_subscribeToFacebook('{\"customerIdentifier\":\"{$customer_identifier}\", \"event\":\"precheckout\", \"shoppingSystem\":\"wooCommerce\"}');
            return true;
        });
    </script>
HTML;
}