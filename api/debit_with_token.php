<?php

/**
 * Performs a debit transaction using a card token through the Nuvei API.
 *
 * @param array  $user_data     User data, including ID and email.
 * @param string $card_token    Token of the card associated with the transaction.
 * @param array  $order_data    Order data, including subtotal, total, taxes, etc.
 *
 * @return array                An array containing transaction data and its status.
*/

function debit_with_token( $user_data, $card_token, $order_data ) {

    //wc_add_notice( 'Debug Infooooo: ' . print_r($user_data, true), 'notice' );
    //wc_add_notice( 'Card token: ' . print_r($card_token, true), 'notice' );
    //wc_add_notice( 'Debug Infooooo: ' . print_r($order_data, true), 'notice' );

    $file_path = ABSPATH . 'subscription_log.txt';
    
    file_put_contents($file_path, "Renovación completada para el pedido ID:" . print_r($user_data, true) , FILE_APPEND);

    // 1. Create an authentication token
    $auth_token = create_auth_token();

    // 2. Check for the existence of the authentication token
    $html_output = '';

    if( !$auth_token ) {

        $html_output .= '<div class="alert error-alert" style="margin-bottom: 24px;">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No auth token available.</p>';
        $html_output .= '</div>';

        return $html_output;

    }

    // 3. Check for the existence of user data
    if( empty($user_data) || !$user_data ) {

        $html_output .= '<div class="alert error-alert" style="margin-bottom: 24px;">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No user data available.</p>';
        $html_output .= '</div>';


        return $html_output;
    }

    // 4. Get relevant order data
    $cart_subtotal  = $order_data['order_subtotal'];    // without taxes
    $cart_total     = $order_data['order_total'];       // with taxes
    $cart_taxes     = $order_data['order_taxes'];       // only taxes 
    $tax_percentage = $order_data['tax_percentage'];    // tax percentage

    // 5. Create data for the debit transaction
    if( $cart_taxes > 0 ) {

        $debit_data = [
            "user" => [
                "id"    => strval($user_data['user_id']),
                "email" => $user_data['user_email']
            ],
            
            "order" => [
                "amount"         => $cart_total,
                "vat"            => ($cart_subtotal * $tax_percentage) / 100,
                "taxable_amount" => $cart_subtotal,
                "tax_percentage" => $tax_percentage,
                "description"    => $order_data['order_description'],
                "dev_reference"  => 'Order number:' . ' ' . '#' . strval($order_data['order_id']),
            ],

            "card" => [
                "token" => $card_token
            ]
        ];

    } else {

        $debit_data = [
            "user" => [
                "id"    => strval($user_data['user_id']),
                "email" => $user_data['user_email']
            ],
            
            "order" => [
                "amount"         => $cart_total,
                "vat"            => 0.00,
                "tax_percentage" => 0.00,
                "description"    => $order_data['order_description'],
                "dev_reference"  => 'Order number:' . ' ' . '#' . strval($order_data['order_id']),
            ],

            "card" => [
                "token" => $card_token
            ]
        ];

    }

    //wc_add_notice( 'Debug Infooooo: ' . print_r($debit_data, true), 'notice' );

    // 6. Send data to Nuvei API endpoint to execute debit transaction
    $dataJSON = json_encode($debit_data);

    $base_url = get_nuvei_base_url();
    $url = $base_url . '/transaction/debit/';

    //wc_add_notice( 'Debug Infooooo: ' . print_r($base_url, true), 'notice' );
    //wc_add_notice( 'Debug Infooooo: ' . print_r($url, true), 'notice' );

    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($request, CURLOPT_POSTFIELDS, $dataJSON);
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Auth-Token: ' . $auth_token));
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    // 7. Execute the request
    $response = curl_exec($request);

    // 8. Handle errors in the request
    if ( curl_errno($request) ) {

        $html_output .= '<p>Error in the request: ' . curl_error($request) . '</p>';
       
        curl_close($request);
        
        return $html_output;

    }

    // 9. Close the request
    curl_close($request);

    // 10. Decode the JSON response
    $request_result = json_decode($response, true);

    //wc_add_notice( 'REQUEST_RESULT INFO: ' . print_r($request_result, true), 'notice' );

    // 11. Get transaction data and status
    $transaction_data = $request_result['transaction'];
    $status = $request_result['transaction']['status'];

    //wc_add_notice( 'Debug Infooooo: ' . print_r($transaction_data, true), 'notice' );
    //wc_add_notice( 'Debug Infooooo: ' . print_r($status, true), 'notice' );

    file_put_contents($file_path, "Renovación completada para el pedido ID:" . print_r($request_result, true) , FILE_APPEND);

    // 12. Return the results
    return array(
        'transaction_data' => $transaction_data,
        'status'           => $status
    );
}
