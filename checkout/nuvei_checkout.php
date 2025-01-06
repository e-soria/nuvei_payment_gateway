<?php

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

function nuvei_init_gateway_class() {
    
    class WC_Nuvei_Gateway extends WC_Payment_Gateway {

        public function __construct() {

            $this->id = 'nuvei'; 
            $this->icon = 'https://staging.hiitclub.online/wp-content/uploads/2024/08/WhatsApp-Image-2024-08-01-at-3.19.36-PM.jpeg'; 
            $this->has_fields = true;
            $this->method_title = 'Nuvei Gateway';
            $this->method_description = 'Use this payment method to make sales and payments through Nuvei';
            
            $this->supports = array( 
                'products', 
                'subscriptions',
                'subscription_cancellation', 
                'subscription_suspension', 
                'subscription_reactivation',
                'subscription_amount_changes',
                'subscription_date_changes',
                'multiple_subscriptions',
            );

            // Method with all the options fields
	        $this->init_form_fields();
            // Load the settings.
            $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->testmode = 'yes' === $this->get_option( 'testmode' ); // true or false
            
            // add new fields to use Nuvei test credentials
            $this->test_app_code = $this->get_option( 'test_app_code' );
            $this->test_app_key = $this->get_option( 'test_app_key' );
            $this->test_app_server_code = $this->get_option( 'test_app_server_code' );
            $this->test_app_server_key = $this->get_option( 'test_app_server_key' );

            // add new fields to use Nuvei production credentials
            $this->app_code = $this->get_option( 'app_code' );
            $this->app_key = $this->get_option( 'app_key' );
            $this->app_server_code = $this->get_option( 'app_server_code' );
            $this->app_server_key = $this->get_option( 'app_server_key' );
            
            $this->base_url_stg = $this->get_option( 'base_url_stg' );
            $this->base_url_prod = $this->get_option( 'base_url_prod' );

            $this->tax_enabled = $this->get_option( 'tax_enabled' );
            $this->tax_percentage = $this->get_option('tax_enabled') === 'no' ? '0' : $this->get_option( 'tax_percentage' );

            // support email
            $this->customer_support_email = $this->get_option('customer_support_email');

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
            
            // This action hook is used for subscriptions renewal system.
            add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'woocommerce_scheduled_subscription_payment'), 10, 2);

            //add_action('woocommerce_scheduled_subscription_payment',  array($this, 'custom_scheduled_subscription_payment'));
            //add_action( 'woocommerce_subscription_status_updated', array( $this, 'woocommerce_subscription_status_updated' ), 10, 3 );
 		}
        
		public function init_form_fields(){

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable Nuvei Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This is the title that users will see at checkout',
                    'default'     => 'Your secure payment through Nuvei.',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Payment method description that the customer will see on your checkout.',
                    'default'     => 'We use all Nuvei security methods.',
                    'desc_tip'    => true,
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable Test Mode',
                    'type'        => 'checkbox',
                    'description' => 'To use "test mode" you must enter your test credentials',
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
                'test_app_code' => array(
                    'title'       => 'Test Application Code',
                    'type'        => 'text',
                    'default'     => ''
                ),
                'test_app_key' => array(
                    'title'       => 'Test Application Key',
                    'type'        => 'password',
                    'default'     => ''
                ),
                'test_app_server_code' => array(
                    'title'       => 'Test App Server Code',
                    'type'        => 'text',
                    'default'     => ''
                ),
                'test_app_server_key'  => array(
                    'title'       => 'Test App Server Key',
                    'type'        => 'password',
                    'default'     => ''
                ),
                'app_code' => array(
                    'title'       => 'Application Code',
                    'type'        => 'text',
                    'default'     => ''
                ),
                'app_key' => array(
                    'title'       => 'Application Key',
                    'type'        => 'password',
                    'default'     => ''
                ),
                'app_server_code' => array(
                    'title'       => 'App Server Code',
                    'type'        => 'text',
                    'default'     => ''
                ),
                'app_server_key'  => array(
                    'title'       => 'App Server Key',
                    'type'        => 'password',
                    'default'     => ''
                ),
                'base_url_stg'  => array(
                    'title'       => 'Base URL stg',
                    'type'        => 'text',
                    'default'     => 'https://ccapi-stg.paymentez.com/v2',
                    'desc_tip'    => true,
                    'description' => 'Base url for endpoints in stg mode. Ex: https://ccapi-stg.paymentez.com/v2',
                ),
                'base_url_prod'  => array(
                    'title'       => 'Base URL prod',
                    'type'        => 'text',
                    'default'     => 'https://ccapi-prod.paymentez.com/v2',
                    'desc_tip'    => true,
                    'description' => 'Base url for endpoints in prod mode. Ex: https://ccapi-prod.paymentez.com/v2',
                ),
                'tax_enabled' => array(
                    'title'       => 'Enable Tax',
                    'label'       => 'Do you pay taxes?',
                    'type'        => 'checkbox',
                    'default'     => 'no',
                    'desc_tip'    => true,
                    'description' => 'If you pay taxes, enable this option.',
                ),
                'tax_percentage' => array(
                    'title'       => 'Tax Percentage',
                    'type'        => 'text',
                    'default'     => '0',
                    'description' => 'Enter the tax percentage do you pay. Do not include the "%" symbol',
                    'desc_tip'    => true,
                    'placeholder' => 'Example: 12'
                ),
                'customer_support_email' => array(
                    'title'       => 'Customer support email',
                    'type'        => 'email',
                    'default'     => '',
                    'description' => 'This is very important. Write the email with which you will support your users in case of a problem during the purchase process.',
                    'desc_tip'    => true,
                    'placeholder' => 'support@support.com'
                ),
            );

	 	}

		public function payment_fields() {

            // let's display some description before the payment form
            if( $this->description ) {
                if( $this->testmode ) {
                    $this->description .= ' <span class="test-mode">TEST MODE ENABLED.</span> In test mode, you can use the card numbers listed in <a href="https://developers.paymentez.com/docs/payments/#javascript">documentation</a>.';
                    $this->description  = trim( $this->description );
                }
            
                echo wpautop( wp_kses_post( $this->description ) );
            }
            
            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

                echo '<div id="user-cards" class="user-cards">';

                    echo '<div class="use-saved-cards-option">';
                        echo '<input type="checkbox" id="use_saved_cards" name="use_saved_cards" />';
                        echo '<label for="use_saved_cards">Deseo pagar con una de mis tarjetas</label>';
                    echo '</div>';
                    
                    do_shortcode('[show_user_cards use_card_button="true" delete_card_button="false"]');

                echo '</div>';
                    
                // Add this action hook if you want your custom payment gateway to support it
                do_action( 'woocommerce_credit_card_form_start', $this->id );
                
                do_shortcode('[tokenize_form is_checkout="true"]');
            
                do_action( 'woocommerce_credit_card_form_end', $this->id );
        
            echo '<div class="clear"></div></fieldset>';

		}

    
		// Function to execute custom PHP and JS Scripts
	 	public function payment_scripts() {

            wc_add_notice( 'POST Debug Info: ' . print_r( $_POST, true ), 'notice' );

            return;

            //wc_add_notice( 'ORDER ID' . print_r($order, true), 'notice');

            //$cart = WC()->cart;
            //$cart_items = $cart->get_cart();

            //wc_add_notice( 'Debug Info: ' . print_r( $cart, true ), 'notice' );
            
            /* $order = wc_get_order( $order_id );
            $order_total = $order->get_total(); // Total de la orden
            $order_subtotal = $order->get_subtotal(); // Subtotal de la orden

            wc_add_notice( 'Debug Info: ' . print_r( $order, true ), 'notice' );
            wc_add_notice( 'Debug Info: ' . print_r( $order_total, true ), 'notice' );
            wc_add_notice( 'Debug Info: ' . print_r( $order_subtotal, true ), 'notice' );
            */

        }

        // Function to validate fields
		public function validate_fields() {
            //wc_add_notice( 'Debug Info: ' . print_r( $_POST, true ), 'notice' );
            
        }

        public function process_payment($order_id) {

            $cart_items = WC()->cart->get_cart();
            $subscription_id = $this->detect_subscription($cart_items);
        
            $user_data = $this->get_user_data();
            $card_token = $_POST['card_token'];
            $order_data = $this->generate_order_data($order_id, $user_data, $cart_items);
        
            // FREE TRIAL CASE
            if ($order_data['order_subtotal'] == 0 && $order_data['order_total'] == 0) {

                if (isset($_POST['use_saved_cards'])) {

                    $update_card = update_card_from_db( $user_data['user_id'], $card_token, $subscription_id);
                    
                } else {
                    
                    $user_card_data = array(
                        "card_token"      => $card_token,
                        'subscription_id' => isset($subscription_id) ? $subscription_id : null,
                    );
                 
                    save_card_into_db($user_data['user_id'], $user_card_data);
                
                }

                return $this->complete_order($order_id);
            } 
            // END FREE TRIAL CASE
        
            $execute_debit = debit_with_token($user_data, $card_token, $order_data);
            wc_add_notice('Execute debit info: ' . print_r($execute_debit, true), 'notice');
        
            if ($execute_debit['status'] === 'success') {

                $this->handle_successful_payment($order_id, $user_data['user_id'], $card_token, $subscription_id, $execute_debit);
                return $this->redirect_to_thank_you_page($order_id);

            } elseif ($execute_debit['status'] === 'pending') {

                return $this->handle_pending_payment($order_id);

            }
        
            return $this->handle_failed_payment($order_id);

        }
        
        private function detect_subscription($cart_items) {

            foreach ($cart_items as $cart_item) {
                $product_id = $cart_item['data']->get_id();
                if (class_exists('WC_Subscriptions_Product') && WC_Subscriptions_Product::is_subscription($product_id)) {
                    return $product_id;
                }
            }

            return null;

        }
        
        private function get_user_data() {

            $data = get_user_data();
            return [
                'user_id'    => $data['user_id'],
                'user_email' => $data['user_email'],
                'first_name' => $data['firstname'],
                'last_name'  => $data['lastname'],
            ];

        }
        
        private function generate_order_data($order_id, $user_data, $cart_items) {

            $product_names = array_map(function($item) {
                return $item['data']->get_name();
            }, $cart_items);
        
            return [
                'order_id'          => $order_id,
                'order_description' => $user_data['first_name'] . ' ' . $user_data['last_name'] . ' has bought: ' . implode(', ', $product_names),
                'order_subtotal'    => (float) WC()->cart->get_subtotal(),
                'order_total'       => (float) WC()->cart->get_total('number'),
                'order_taxes'       => (float) WC()->cart->get_total_tax(),
                'tax_percentage'    => (float) get_option('woocommerce_nuvei_settings')['tax_percentage'],
            ];

        }
        
        private function complete_order($order_id) {

            $order = wc_get_order($order_id);
            $order->payment_complete();
            $order->reduce_order_stock();
            WC()->cart->empty_cart();
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];

        }
        
        private function handle_successful_payment($order_id, $user_id, $card_token, $subscription_id, $execute_debit) {

            if (isset($_POST['use_saved_cards'])) {

                update_card_from_db($user_id, $card_token, $subscription_id);

            } else {

                save_card_into_db($user_id, [
                    'card_token'      => $card_token,
                    'subscription_id' => $subscription_id,
                ]);

            }
        
            $order = wc_get_order($order_id);
            foreach ($execute_debit['transaction_data'] as $key => $value) {
                $order->update_meta_data('_nuvei_debit_' . $key, $value);
            }
        
            update_post_meta($order_id, '_user_card_data', ['subscription_id' => $subscription_id]);
            $order->payment_complete();
            $order->reduce_order_stock();
            WC()->cart->empty_cart();

        }
        
        private function redirect_to_thank_you_page($order_id) {

            $order = wc_get_order($order_id);
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];

        }
        
        private function handle_pending_payment($order_id) {
            // Implement handling of pending payment if required
            return;
        }
        
        private function handle_failed_payment($order_id) {
            wc_add_notice(
                '<p>Error: No es posible realizar la transacción. Por favor prueba con otra tarjeta, comunícate con tu banco o escribe a <a href="mailto:hi@staging.hiitclub.online">hi@staging.hiitclub.online</a> para recibir ayuda</p>',
                'error'
            );
            wp_delete_post($order_id, true);
        }        
        

        // RENOVACIÓN AUTOMÁTICA
        public function woocommerce_scheduled_subscription_payment($amount_total, $renewal_order) {

            // Get user data
            $user_id = $renewal_order->get_customer_id();
            $product_price = null;

            
            // Get product_id ( subscription )
            foreach ( $renewal_order->get_items() as $item_id => $item ) {
                $product = $item->get_product();
                $product_price = $product->get_price();
                $product_id = $product->get_id();

                if (class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product_id ) || $product->is_type('subscription') || $product->is_type( 'variable-subscription') ) {
                    $subscription_id = $product_id;
                }
            }

            // Make a query to get the card associated with the subscription
            global $wpdb;

            $table_name = $wpdb->prefix . 'user_cards';
                   
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND subscription_id = %d",
                $user_id,
                $subscription_id
            );
            
            $user_card = $wpdb->get_row($sql, ARRAY_A);

            if(!$user_card || empty($user_card)) {
                return;
            }

            // Get all info we need to excecute the debit_with_token() API
            // 1) User Data
            $get_user_data = get_userdata($user_id);

            $user_data = [
                'user_id'    => $user_id,
                'user_email' => $get_user_data->user_email,
                'first_name' => $renewal_order->get_billing_first_name(),
                'last_name'  => $renewal_order->get_billing_last_name(),
            ];

            // 2) User Token ID
            $card_token = $user_card['card_token'];

            // 3) Order Data
            $order_id = $renewal_order->get_id();
            
            //$order_description = $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . 'has renewed the' . ' ' . $product_name . ' ' . 'subscription'; 
            $order_description = $user_data['first_name'] . ' ' . $user_data['last_name'] . ' ' . 'has renewed the subscription'; 
            
            $order_data = [
                'order_id'          => $order_id,
                'order_description' => $order_description,
                'order_subtotal'    => (float)$product_price * 1.00,  // without taxes
                'order_total'       => (float)$product_price * 1.00, //with taxes
                'order_taxes'       => (float)$renewal_order->get_total_tax() * 1.00, // only taxes
                'tax_percentage'    => (float)get_option('woocommerce_nuvei_settings')['tax_percentage'] * 1.00, // tax setted from plugin settings
            ];
 
            // EXCECUTE THE DEBIT_WITH_TOKEN() API
            $excecute_debit = debit_with_token($user_data, $card_token, $order_data );

            if ($excecute_debit['status'] === 'success') {

                // add order meta data
                $transaction_data = $excecute_debit['transaction_data'];

                $order = wc_get_order( $order_id );

                foreach ($transaction_data as $key => $value) {
                    $order->update_meta_data( '_nuvei_debit_' . $key, $value );
                }
                
                // TODO: indicar la subscripcion y la orden como activa
                $renewal_order->update_status('completed', 'order_note');

                $order->save();
                
                $primer_mensaje = $order_id;
                
            } else {

                return;

            }
                      
        }
        
        /*
        public function woocommerce_subscription_status_updated( $subscription, $new_status, $old_status ) {
            // Get the subscription object
            $customer = $subscription->get_customer_id();
            $user = get_user_by( 'ID', $customer );

            foreach ($subscription->get_items() as $item_id => $item ) {

                $product = $item->get_product();

                if($product->is_type('subscription')) {
                    $product_name = $product->get_sku();
                    $product_id = $item->get_product_id();
                }
            }
    
        
            // Check if the subscription is active
            if ( $new_status === 'active' ) {
                // Check that it's not a guest customer and the user object is valid
                if ( is_a( $user, 'WP_User' ) && $user->ID > 0 ) {
        
                    // Add the "subscriber-a" role if it's not already set
                    if ( !in_array( $product_name, $user->roles ) ) {
                        $name = strtolower($product_name);
                        $user->add_role($name);
                    } 
        
                }
        
            } else {
                if (in_array($product_name, $user->roles)) {
                    $name = strtolower($product_name);
                    $user->remove_role($name);
                }
            }
        }
        */
     
		public function webhook() {
					
	 	}
 	}
}

add_action( 'plugins_loaded', 'nuvei_init_gateway_class' );


?>