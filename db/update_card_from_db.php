<?php

/**
 * Actualiza el ID de suscripción asociado a una tarjeta en la base de datos.
 *
 * @param string  $user_id          ID del usuario asociado a la tarjeta.
 * @param string  $card_token       Token de la tarjeta a actualizar.
 * @param string  $subscription_id  Nuevo ID de suscripción asociado a la tarjeta.
 */

function update_card_from_db( $user_id, $card_token, $subscription_id ) {
    
    global $wpdb;
  
    $table_name = $wpdb->prefix . 'user_cards';
  
    $sql = $wpdb->prepare(
        "UPDATE $table_name SET subscription_id = %d WHERE user_id = %d AND card_token = %s",
        $subscription_id,
        $user_id,
        $card_token
    );
   
    $result = $wpdb->query($sql);

    if ( $result === false ) {
        wc_add_notice(
            '<p>
                No es posible realizar la transacción. 
                Por favor, prueba con otra tarjeta, comunícate con tu banco o contáctate con nosotros para recibir ayuda. 
            </p>', 
            'error' 
        );
    }
}

?>