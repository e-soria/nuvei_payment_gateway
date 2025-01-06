<?php

/**
* Delete a tokenized card from the user through the Nuvei API*
* @param array   $user_cards     All cards associated with the user
* @param string  $user_id        User ID
*
* @return redirect|message       If card was deleted returns redirect to same page or returns a error message.
*/

function delete_card($user_id, $user_cards) {

    //print_r($_POST);

    $html_output = '';
    
    $auth_token = create_auth_token();

    $card_token = null;
    $card_ref = $_POST['card_token'];
    
    foreach ($user_cards as $card) {

        //print_r($card);

        if($card_ref == $card['token']) {

            $card_token = $card['token'];

            $card_data = [
                "card" => [
                    "token" => $card_token
                ],
        
                "user" => [
                    "id" => $user_id
                ]
            ];

            break;
        }

    }
    
    $dataJSON = json_encode($card_data);
  
    $base_url = get_nuvei_base_url();
    $url = $base_url . '/card/delete/';

    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($request, CURLOPT_POSTFIELDS, $dataJSON);
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Auth-Token: ' . $auth_token));
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true); //consultar esto

    $response = curl_exec($request);

    if (curl_errno($request)) {

        $html_output .= '<p>Error en la solicitud: ' . curl_error($request) . '</p>';

        curl_close($request);

        return $html_output;
        
    }

    curl_close($request);

    $result = json_decode($response, true);
  
 
    if (isset($result['message']) && $result['message'] === 'card deleted') {

        delete_card_from_db($card_token, $user_id);
        
        $current_url = home_url($_SERVER['REQUEST_URI']);

        return wp_redirect($current_url);

    } else {

        $html_output .= '<p>An error occurred while deleting your card. The card may not have been deleted.</p>';

        return $html_output;
        
    }

}

?>