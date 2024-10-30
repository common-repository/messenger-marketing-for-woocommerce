<?php
if (!defined('ABSPATH')) {
    exit();
}

class Messenger_Marketing_FacebookSDK
{
    public function __construct()
    {
        add_action('plugins_loaded', array(&$this, 'init'));
    }

    public function init()
    {
        add_action('wp_head', array(&$this, 'load_facebook_sdk'));
    }

    public function load_facebook_sdk()
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
                    js.src = "//connect.facebook.net/en_US/all.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk')
            );
        </script>
        <?php
    }

}

new Messenger_Marketing_FacebookSDK;