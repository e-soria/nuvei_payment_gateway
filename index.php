<?php

/**
    * Plugin Name: Nuvei Payment Gateway
    * Description: Permite a los usuarios realizar pagos con tarjeta de crédito y débito a través de la pasarela de pagos de Nuvei.
    * Author: Enzo Soria
    * Author URI: https://enzosoria.com
    * License: GPL v3
    * License URI: https://www.gnu.org/licenses/gpl-3.0.html 
**/

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
