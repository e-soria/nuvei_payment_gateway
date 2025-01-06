<?php

/**
 * Delete a card from the database associated with a specific user.
 *
 * @param string    $card_token  Token of the card to be deleted.
 * @param string    $user_id     User ID associated with the card.
 *
 * @return string|true           Error or null message if the operation was successful.
*/


function delete_card_from_db($card_token, $user_id) {

    if (empty($card_token) || empty($user_id)) {
        return '<p>Error: Token de tarjeta o ID de usuario no válido</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'user_cards';

    $get_db_user_cards = $wpdb->get_results(
        $wpdb->prepare("SELECT card_token FROM $table_name WHERE user_id = %d", $user_id),
        ARRAY_A
    );

    if ( !empty($get_db_user_cards) ) {

        foreach ( $get_db_user_cards as $user_card ) {
    
            if ( $user_card['card_token'] === $card_token ) {
                $wpdb->delete($table_name, array('card_token' => $card_token, 'user_id' => $user_id));
                return true;
            }
        }

    } else {
        return '<p>No existen tarjetas asociadas a tu usuario.</p>';
    }
}



?>