<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_JavascriptApi
{
    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
        add_action('wp_footer', array(&$this, 'init_javascript_api'));
    }

    public function init_javascript_api()
    {
        ?>
        <script>
            window.chatchamp_sendState = function (facebook_page_id, ip, customer_identifier, event, state) {
                var settings = {
                    url: "<?php echo CHATCHAMP_CHATCHAMP_API_URL ?>/checkbox",
                    type: "POST",
                    contentType: "application/json",
                    dataType: 'json',
                    data: JSON.stringify({
                        "facebookPageIdentifier": facebook_page_id,
                        "ip": ip,
                        "event": event,
                        "state": state,
                        "hostname": window.location.hostname,
                        "customerIdentifier": customer_identifier
                    })
                };

                jQuery.ajax(settings)
                    .done(function (response) {
                    })
                    .fail(function () {
                        console.log('Checkbox api call failed.')
                    });
            };
        </script>
        <?php
    }
}

new Messenger_Marketing_JavascriptApi;