<?php
if (!defined('ABSPATH')) {
    exit();
}

add_action('wp_head', 'chatchamp_jquery');
function chatchamp_jquery()
{
    wp_enqueue_script('jquery');
}