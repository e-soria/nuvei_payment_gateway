<?php

function refund($transaction_id) {

    // output
    $html_output = '';

    // auth token
    $auth_token = create_auth_token();

    if( !$auth_token ) {

        $html_output .= '<div class="alert error-alert" style="margin-bottom: 24px;">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No auth token available.</p>';
        $html_output .= '</div>';

        return $html_output;

    }

    $refund_data = [
        "transaction" => [
            "id" => $transaction_id
        ]
    ];

    //sending data to endpoint to excecute debit token
    $dataJSON = json_encode($refund_data);

    $request = curl_init();
    
    $url = 'https://ccapi-stg.paymentez.com/v2/transaction/refund/';
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($request, CURLOPT_POSTFIELDS, $dataJSON);
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Auth-Token: ' . $auth_token));
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true); //consultar esto

    // excecute the request
    $response = curl_exec($request);

    // if error
    if (curl_errno($request)) {
        $html_output .= '<p>Error en la solicitud: ' . curl_error($request) . '</p>';
        curl_close($request);
        return $html_output;
    }

    // close request
    curl_close($request);

    // decode json response
    $request_result = json_decode($response, true);

    if(isset($request_result['error'])) {
        
        return array(
            "status" => "error",
            "detail" => $request_result['error']
        );

    }

    $request_status = $request_result['status'];
    $request_deail = $request_result['detail'];

    return array(
        "status" => $request_status,
        "detail" => $request_deail
    );

}

?>
