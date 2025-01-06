<?php

function get_user_data() {
    $user_id = get_current_user_id();
    $user_data = get_userdata($user_id);

    if (empty($user_id) || empty($user_data)) {
        return false;
    }

    return array(
        'user_id'    => strval($user_id),
        'user_email' => $user_data->user_email,
        'username'   => $user_data->user_login,
        'firstname'  => $user_data->first_name,
        'lastname'   => $user_data->last_name
    );
}

function get_server_credentials() {
    
    $nuvei_plugin_settings = get_option('woocommerce_nuvei_settings');

    if (empty($nuvei_plugin_settings) || $nuvei_plugin_settings == '') {
        return false;
    }
    
    if ($nuvei_plugin_settings['testmode'] === 'yes') {

        return array(
            'app_server_code' => $nuvei_plugin_settings['test_app_server_code'],
            'app_server_key'  => $nuvei_plugin_settings['test_app_server_key']
        );

    } else {

        return array(
            'app_server_code' => $nuvei_plugin_settings['app_server_code'],
            'app_server_key'  => $nuvei_plugin_settings['app_server_key']
        );
 
    }
}

function get_app_credentials() {

    $nuvei_plugin_settings = get_option('woocommerce_nuvei_settings');

    if (empty($nuvei_plugin_settings) || $nuvei_plugin_settings == '') {
        return false;
    }
    
    if ($nuvei_plugin_settings['testmode'] === 'yes') {

        return array(
            'mode'     => 'stg',
            'app_code' => $nuvei_plugin_settings['test_app_code'],
            'app_key'  => $nuvei_plugin_settings['test_app_key']
        );

    } else {

        return array(
            'mode'     => 'prod',
            'app_code' => $nuvei_plugin_settings['app_code'],
            'app_key'  => $nuvei_plugin_settings['app_key']
        );

    }
}

function get_nuvei_base_url() {

    $nuvei_plugin_settings = get_option('woocommerce_nuvei_settings');
    
    if (empty($nuvei_plugin_settings) || $nuvei_plugin_settings == '') {
        return false;
    }

    if ($nuvei_plugin_settings['testmode'] === 'yes') {

        return $nuvei_plugin_settings['base_url_stg'];

    } else {

        return $nuvei_plugin_settings['base_url_prod'];
       
    }
}

?>