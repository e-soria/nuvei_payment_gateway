<?php

function refund_form() {  
    
    $customer_support_email = get_option('woocommerce_nuvei_settings')['customer_support_email'];

    // Iniciar el buffer de salida
    ob_start(); ?>

    <div class="refound-section">
        <div class="refound-section-container">

            <?php
            
            if ( !empty($_POST) ) {

                if ( !empty($_POST['transaction_id']) ) {

                    $transaction_id = $_POST['transaction_id'];
                    $refound = refund($transaction_id);

                    if ( $refound['status'] === 'success' ) { ?>

                        <div class='alert sucess-alert'>
                            <p><i class="icon-info" aria-hidden="true"></i>La solicitud se ha procesado correctamente. El reembolso debería efectuarse en las proximas horas.</p>
                        </div>

                        <?php

                    } elseif ( $refound['status'] === 'error' ) { ?>

                        <div class='alert error-alert'>
                            <p><i class="icon-info" aria-hidden="true"></i>No es posible realizar la solicitud de reembolso. 
                            Revisa que el ID de la transacción sea el correcto o contáctate con <a href="mailto:<?php echo $customer_support_email?>"><?php echo $customer_support_email?></a> para recibir ayuda.</p>
                        </div>

                        <?php
                        
                    } else { ?>

                        <div class='alert error-alert'>
                            <p><i class="icon-info" aria-hidden="true"></i>No es posible realizar la solicitud de reembolso. 
                            Es posible que ya esté reembolsada o que el banco no pueda hacerlo de manera automática. 
                            Contáctate con <a href="mailto:<?php echo $customer_support_email?>"><?php echo $customer_support_email?></a> para recibir ayuda.</p>
                        </div>

                        <?php

                    }

                } else {  ?>

                    <div class='alert error-alert'>
                        <p><i class="icon-info" aria-hidden="true"></i>Debes ingresar tu ID de transacción para comenzar el proceso de reembolso.</p>
                    </div>

                    <?php
                }

                
            } else { ?>

                <div class='alert info-alert'>
                    <p>
                        <i class="icon-info" aria-hidden="true"></i>
                        Para solicitar el reembolso necesitamos el ID de la transacción. Puedes encontrarlo en el correo que te enviamos cuando realizaste la compra.
                    </p>
                </div>

                <?php
            }
    
            ?>
            
            <form class="refound-form" method="post">
                <input type="text" name="transaction_id" id="transaction_id" placeholder="Coloca tu ID de la transacción" />
                <input type="submit" value="Solicitar reembolso" />
            </form>
        
        </div>
    </div>

    <?php

    return ob_get_clean();
} 

add_shortcode('refund_form', 'refund_form'); 