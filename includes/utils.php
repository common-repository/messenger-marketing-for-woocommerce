<?php
if (!defined('ABSPATH')) {
    exit();
}

add_filter('clean_url', 'chatchamp_add_async_forscript', 11, 1);
function chatchamp_add_async_forscript($url)
{
    if (strpos($url, '#asyncload') === false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url) . "' async='async";
}