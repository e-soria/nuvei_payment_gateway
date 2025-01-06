<?php

function list_user_cards() {

    $auth_token = create_auth_token();

    $html_output = '';

    if( !$auth_token ) {
        
        $html_output .= '<div class="alert error-alert" style="margin-bottom: 24px;">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No auth token available.</p>';
        $html_output .= '</div>';
    
        return $html_output;

    }

    $user_data = get_user_data();

    if( !$user_data ) {

        $html_output .= '<div class="alert error-alert" style="margin-bottom: 24px;">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No user data available.</p>';
        $html_output .= '</div>';

        return $html_output;
    
    } else {

        $user_id = $user_data['user_id'];

    }

    $base_url = get_nuvei_base_url();
    $request_url = $base_url . '/card/list?uid=' . $user_id;

    $request_headers = array(
        'headers' => array(
            'Auth-Token' => $auth_token
        )
    );
    
    $response = wp_remote_get($request_url, $request_headers);
    $request_status = $response['response']['code'];

    if ( is_array($response) && $request_status === 200 ) {
        
      $data = json_decode(wp_remote_retrieve_body($response), true);

      return $data;

    } else {

        $html_output .= '<div class="alert error-alert">';
        $html_output .= '<i class="icon-info" aria-hidden="true"></i><p>There was an error obtaining the card information.</p>';
        $html_output .= '</div>';

        return $html_output;
        
    }

}

?>
