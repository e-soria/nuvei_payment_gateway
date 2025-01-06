<?php

/**
 * Retrieves card information from the database based on the user ID and a given reference.
 *
 * @param string $user_id     User ID associated with the card.
 * @param string $reference   Reference of the card to be recovered.
 *
 * @return array|notice_error    An associative array with the card information or message error if not found.
*/


function get_card_from_db( $user_id, $reference ) {

    global $wpdb;
                
    $table_name = $wpdb->prefix . 'user_cards';
    
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND card_ref = %d",
        $user_id,
        $reference
    );

    $user_card = $wpdb->get_row($sql, ARRAY_A);

    if (empty($user_card)) {
        return wc_add_notice(
            '<p>
                No es posible realizar la transacción. 
                Por favor prueba con otra tarjeta, comunícate con nosotros para recibir ayuda.
            </p>', 
            'error' 
        );
    }
    
    return $user_card;


}


?>