<?php

/**
 * Store card information in the database.
 * 
 * @param string $user_id       User ID associated with the card.
 * @param array  $user_card     Card information to store.
 *
 * @return bool                 True if the operation was successful, false otherwise.
 * 
*/

function save_card_into_db($user_id, $user_card) {

    $html_output = '';

    if (empty($user_id) || empty($user_card)) {

        $html_output .= '<div class="alert error-alert">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>Token de tarjeta o ID de usuario no válido</p>';
        $html_output .= '</div>';

        echo $html_output;

        return false;

    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_cards';

    $data_to_insert = array(
        'card_token'      => $user_card['card_token'],
        'user_id'         => $user_id,
        'subscription_id' => isset($user_card['subscription_id']) ? $user_card['subscription_id'] : null,
    );

    $wpdb->insert($table_name, $data_to_insert);

    if ($wpdb->last_error) {

        $html_output .= '<div class="alert error-alert">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>Error al guardar los datos en la db: ' . $wpdb->last_error . '</p>';
        $html_output .= '</div>';

        echo $html_output;

        return false;

    }

    return true;
}

?>