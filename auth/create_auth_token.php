<?php

function create_auth_token() {

    //get credentials from plugin_hooks
    $credentials = get_server_credentials();

    if(!$credentials) {
        return;
    }

    $server_app_code = $credentials['app_server_code'];
    $server_app_key = $credentials['app_server_key'];

    $date = new DateTime();
    $unix_timestamp = $date->getTimestamp();

    $uniq_token_string = $server_app_key . $unix_timestamp;
    $uniq_token_hash = hash('sha256', $uniq_token_string);

    $auth_token = base64_encode($server_app_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

    return $auth_token;

}

?>