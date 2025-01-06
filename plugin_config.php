<?php

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

//adding Nuvei gateway to woocommerce -> settings -> payments
function add_nuvei_gateway( $gateways ) {
    $gateways[] = 'WC_Nuvei_Gateway';
	return $gateways;
}

add_filter( 'woocommerce_payment_gateways', 'add_nuvei_gateway' );

function nuvei_plugin_styles() {

    $plugin_url = plugins_url('/', __FILE__);
    wp_enqueue_style('refund-form-styles', $plugin_url . '/css/refund-form.css');
    wp_enqueue_style('show-user-cards-styles', $plugin_url . '/css/show-user-cards.css');
    wp_enqueue_style('tokenize-form-styles', $plugin_url . '/css/tokenize-form.css');
}

add_action( 'wp_enqueue_scripts', 'nuvei_plugin_styles', 20 );

function order_table_styles() {

    $plugin_url = plugins_url('/', __FILE__);
    wp_enqueue_style('order-table-styles', $plugin_url . '/admin/styles.css');
    
}

add_action('admin_enqueue_scripts', 'order_table_styles');



?>