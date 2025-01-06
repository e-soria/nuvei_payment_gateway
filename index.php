<?php

/**
 * Plugin Name: Nuvei Payment Gateway
 * Author Name: Enzo Soria
 * Description: This plugin allows you to integrate Nuvei as a payment gateway.
 * Version: 0.1.0
*/ 

include(dirname(__FILE__) . '/plugin_config.php');

include(dirname(__FILE__) . '/checkout/nuvei_checkout.php');

include(dirname(__FILE__) . '/api/shortcodes/nuvei_form.php');
include(dirname(__FILE__) . '/api/shortcodes/show_user_cards.php');
include(dirname(__FILE__) . '/api/shortcodes/refund_form.php');

include(dirname(__FILE__) . '/api/debit_with_token.php');
include(dirname(__FILE__) . '/api/delete_card.php');
include(dirname(__FILE__) . '/api/list_user_cards.php');

include(dirname(__FILE__) . '/api/refund.php');

include(dirname(__FILE__) . '/db/create_table.php');
include(dirname(__FILE__) . '/db/save_card_into_db.php');
include(dirname(__FILE__) . '/db/delete_card_from_db.php');
include(dirname(__FILE__) . '/db/update_card_from_db.php');
include(dirname(__FILE__) . '/db/get_card_from_db.php');

include(dirname(__FILE__) . '/auth/create_auth_token.php');
include(dirname(__FILE__) . '/plugin_hooks.php');
include(dirname(__FILE__) . '/admin/index.php');

//adding settings link in plugins page
$plugin_basename = plugin_basename(__FILE__);

function add_plugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout">Ajustes</a>';
    array_push($links, $settings_link);
    return $links;
}

add_filter("plugin_action_links_$plugin_basename", 'add_plugin_settings_link');


?>
