<?php

if (!defined('ABSPATH')) {
    exit();
}

add_action('wp_head', 'chatchamp_inline_css');
function chatchamp_inline_css()
{
    ?>
    <style>
        .chatchamp_plugin {
            clear: both;
        }

        .chatchamp_plugin-header {
            margin-top: 10px;
        }

        .chatchamp-woocommerce-hiddenfield {
            display: none;
        }
    </style>
    <?php
}