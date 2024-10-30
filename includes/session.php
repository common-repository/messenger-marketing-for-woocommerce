<?php
if (!defined('ABSPATH')) {
    exit();
}

function chatchamp_get_customer_identifier()
{
    $customer_id = WC()->session->get_customer_id();
    return $customer_id;
}