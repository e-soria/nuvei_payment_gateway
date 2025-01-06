<?php

function show_user_cards($atts) {

    //init js files
    $plugin_url = plugin_dir_url(basename(__FILE__)) . 'nuvei-gateway/'; 
    wp_enqueue_script('delete-user-cards', $plugin_url . 'api/delete-user-cards.js', array('jquery'), null, true);

    $atts = shortcode_atts(array(
        'tokenize_form' => 'false',
        'use_card_button' => 'false',
        'delete_card_button' => 'true'
    ), $atts);

    // shortcode atts
    $show_form = strval($atts['tokenize_form']);
    $use_card_button = strval($atts['use_card_button']);
    $delete_card_button = strval($atts['delete_card_button']);

    // get all user cards
    $get_user_cards = list_user_cards();

    // get user data
    $get_user_data = get_user_data();
    $user_id = $get_user_data['user_id'];

    // html output
    $html_output = '';

    if (isset($get_user_cards['result_size']) && $get_user_cards['result_size'] > 0) {
        
        $cards = $get_user_cards['cards'];
        $user_cards = [];

        ?>
        <div id="user-cards-container" class="user-cards-container"> <?php

            foreach ($cards as $card) {

                $card_bin = $card['bin'];
                $card_status = $card['status'];
                $card_token = $card['token'];
                $card_name = $card['holder_name'];
                $card_expiry_year = substr($card['expiry_year'], -2);
                $card_expiry_month = str_pad($card['expiry_month'], 2, "0", STR_PAD_LEFT);
                $card_transaction_reference = $card['transaction_reference'];
                $card_number = $card['number'];
                $card_type = $card['type'];

                $user_cards[] = $card;

                if ($card_type === 'mc') {
                    $card_type_img = '/../wp-content/uploads/2023/10/mastercard_icon.svg';
                } elseif ($card_type === 'vi') {
                    $card_type_img = '/../wp-content/uploads/2023/10/visa_icon.svg';
                }

                ?>

                <div class="user-card" data-ref="<?php echo $card_token; ?>">

                    <div class="user-card-details">
                            <img class="card-type" src="<?php echo $card_type_img ?>" />
                            <p class="card-number">**** **** **** <?php echo esc_html($card_number) ?> </p>
                            <p class="card-name"><?php echo esc_html($card_name); ?></p>
                            <p class="card-expiry"><?php echo esc_html($card_expiry_month . '/' . $card_expiry_year); ?></p>
                    </div>

                    <?php

                    if ($delete_card_button === "true") { ?>
                        
                        <form class="card-form delete-card-form" method="post" >
                            
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="card_token" value="<?php echo $card_token?>">

                            <div class="card-form-ctas">
                                <input type="submit" class="button delete-card-button" value="Eliminar Tarjeta">
                            </div>
                            
                        </form>

                        <?php

                    } else { ?>

                        <div class="card-form-ctas">
                            <a href="#" class="button use-card-button">Usar tarjeta</a>
                        </div>

                        <?php
                    } ?>

                </div>

                <?php
            }
            

            if ($use_card_button === "true") { ?>
            
                <div class="alert info-alert">
                    <p class="info-alert"><i class="icon-info" aria-hidden="true"></i>Para eliminar una de tus tarjetas debes hacerlo desde tu perfil.</p>
                </div>
                
                <?php
            }

            ?>


        </div>

        <?php

        if ($delete_card_button === "true") { ?>
        
            <div class="confirmation-modal" style="display:none;">
                <div class="confirmation-modal-container">
                    <i class="icon-info" aria-hidden="true"></i>
                    <h2>¿Estás seguro de <span>eliminar tu tarjeta</span>?</h2>
                    <div class="modal-ctas">
                        <button class="yes">Si, deseo eliminarla</button>
                        <button class="no">No</button>
                    </div>
                </div>
            </div>

            <?php
        
        } 

    } else {

        $html_output .= '<div class="alert info-alert">';
        $html_output .= '<p><i class="icon-info" aria-hidden="true"></i>No tienes ninguna tarjeta registrada.</p>';
        $html_output .= '</div>';

        echo $html_output;

    } 

    if ($show_form === 'true') {
        echo do_shortcode('[tokenize_form]');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' ) {
        delete_card($user_id, $user_cards);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_card' ) {
        
        $user_card = [
            "card_token" => $_POST['card_token']
        ];     

        save_card_into_db($user_id, $user_card);
    }

    return;
}

add_shortcode('show_user_cards', 'show_user_cards');

?>