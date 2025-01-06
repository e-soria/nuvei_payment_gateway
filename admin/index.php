<?php

function nuvei_orders_menu_page() {
    add_submenu_page(
        'woocommerce',                  // Slug del menú principal de WooCommerce
        'Nuvei Orders',                 // Título de la página en el menú
        'Nuvei Orders',                 // Título en la barra de navegación
        'manage_options',               // Capacidad requerida para acceder a la página (en este caso, administradores)
        'nuvei_orders_page',            // Identificador único de la página
        'nuvei_orders_page_content'     // Función que muestra el contenido de la página
    );
}
// Llama a esta función en 'admin_menu'
add_action('admin_menu', 'nuvei_orders_menu_page');

function get_order_data() {

    $orders = wc_get_orders('');
    
    $order_data = array(); // Array para almacenar información de las órdenes y suscripciones

    foreach ($orders as $order) {
    
        $order_id = $order->get_id();
        
        $customer = array(
            'first_name' => $order->get_billing_first_name(),
            'last_name'  => $order->get_billing_last_name(),
        );

        $product_names = array();
        $subscription_order = '';

        foreach ($order->get_items() as $item_id => $item) {

            // Obtener el producto asociado al item
            $product = $item->get_product();
            $product_id = $item->get_id();
            $product_name = $item->get_name();

            // Almacenar nombre de producto en el array
            $product_names[] = $product_name;

            if ( wcs_order_contains_subscription($order)) {

                $subscriptions = wcs_get_subscriptions_for_order($order, array('order_type' => 'parent'));

                foreach($subscriptions as $subscription) {

                    $subscription_id = $subscription->get_id();
                }

            }

        }

        // NUVEI API DATA
        // Filtrar metadatos que comienzan con "_nuvei_debit"

        $order_meta_data = $order->get_meta_data();

        $nuvei_debit_data = array_filter($order_meta_data, function ($meta_item) {
            return strpos($meta_item->key, '_nuvei_debit') === 0;
        });

        // Convertir los objetos de metadatos a un array de datos y extraer las claves reales
        $nuvei_debit_data = array_reduce($nuvei_debit_data, function ($result, $meta_item) {
            $key = str_replace('_nuvei_debit_', '', $meta_item->key);
            $result[$key] = $meta_item->value;
            return $result;
        }, []);

        $nuvei_debit_data['amount'] = (float)($nuvei_debit_data['amount'] * 1.00);

        // Almacenar información de la orden y suscripción en el array
        $order_data[] = array(
            'order_id'               => $order_id,
            'subscription_parent_id' => $subscription_id,
            'customer'               => $customer,
            'product_names'          => $product_names,
            'date'                   => $order->get_date_created()->date_i18n(),
            'amount'                 => $order->get_total(),
            'nuvei_debit_data'       => $nuvei_debit_data
        );
    }

    // Al final de la función, devolver el array con la información
    return $order_data;
}


function nuvei_orders_page_content() {

    echo '<section class="wrap nuvei-orders-section">';
    echo '<h1>Nuvei Orders</h1>';

    // Obtener las órdenes de Nuvei
    $order_data = get_order_data();
    
    $order_customers = [];

    foreach($order_data as $order) {
        $order_customers[] = $order['customer']['first_name'] . ' ' . $order['customer']['last_name'];
    }

    $filter_status = isset($_POST['filter_status']) ? sanitize_text_field($_POST['filter_status']) : '';
    $filter_user = isset($_POST['filter_user']) ? sanitize_text_field($_POST['filter_user']) : '';
    $filter_date_from = isset($_POST['filter_date_from']) ? sanitize_text_field($_POST['filter_date_from']) : '';
    $filter_date_to = isset($_POST['filter_date_to']) ? sanitize_text_field($_POST['filter_date_to']) : '';


    echo '<form method="post" class="nuvei-order-filters">';

        echo '<div class="filter-user">';
            echo '<label for="filter_user">Filter by User:</label>';
            echo '<select id="filter_user" name="filter_user">';
                echo '<option value="" ' . selected('', $filter_user, false) . '>All</option>';

                foreach($order_customers as $order_customer) {
                    echo '<option value="' . $order_customer .  '"' . selected($order_customer, $filter_user, false) . '>' . $order_customer . '</option>';
                }

            echo '</select>';
        echo '</div>';

        echo '<div class="filter-status">';
            echo '<label for="filter_status">Filter by Status:</label>';
            echo '<select id="filter_status" name="filter_status">';
                echo '<option value="" ' . selected('', $filter_status, false) . '>All</option>';
                echo '<option value="success" ' . selected('success', $filter_status, false) . '>Success</option>';
                echo '<option value="pending" ' . selected('pending', $filter_status, false) . '>Pending</option>';
                echo '<option value="failure" ' . selected('failure', $filter_status, false) . '>Failure</option>';
            echo '</select>';
        echo '</div>';

        echo '<div class="filter-date">';
            
            echo '<div>';
                echo '<label for="filter_date_from">Filter by Date (From):</label>';
                echo '<input type="date" id="filter_date_from" name="filter_date_from" value="' . esc_attr($filter_date_from) . '">';
            echo '</div>';

            echo '<div>';
                echo '<label for="filter_date_to">Filter by Date (To):</label>';
                echo '<input type="date" id="filter_date_to" name="filter_date_to" value="' . esc_attr($filter_date_to) . '">';
            echo '</div>';

        echo '</div>';
        
        echo '<div class="filter-ctas">';
            echo '<input type="submit" value="Apply Filters">';
            echo '<input class="reset-filters" type="button" value="Reset filters" onClick="window.location.reload();">';
        echo '</div>';

    echo '</form>';
    
    // Mostrar las órdenes en una tabla
    // <th class="order-amount">Amount</th>
    echo '<table class="nuvei-orders-table widefat">';
        echo 
        '<thead>
            <tr>
                <th class="order-id">Nº Orden</th>
                <th class="order-sub-id">Nº Subs</th>
                <th class="transaction-id">ID</th>
                <th class="transaction-auth">Auth code</th>
                <th class="order-sub-id">User</th>
                <th class="order-products">Products</th>
                <th class="transaction-amount">Amount</th>
                <th class="order-date">Date</th>
                <th class="transaction-ref">Reference</th>
                <th class="transaction-desc">Description</th>
                <th class="transaction-status">Status</th>
            </tr>
        </thead>';

    echo '<tbody>';

    foreach ($order_data as $order) {

        $order_status = $order['nuvei_debit_data']['status'];
        $customer = $order['customer']['first_name'] . ' ' . $order['customer']['last_name'];
        $order_created_date = $order['date'];
        $order_results = 0;

        $nuvei_amount = (float)$order['nuvei_debit_data']['amount'];
        $formatted_amount = number_format((float)$nuvei_amount, 2, '.', '');

        switch ($order['nuvei_debit_data']['status']) {

            case 'success':
                $order_status = 'success';
            break;

            case 'pending':
                $order_status = 'pending';
            break;

            case 'failure':
                $order_status = 'failure';
            break;

            default:
                $order_status = '';
            break;
           
        }

        if ( 
            ($filter_status && $order_status != $filter_status) 
        ||  ($filter_user && $customer != $filter_user)
        ||  ($filter_date_from && strtotime($order_created_date) < strtotime($filter_date_from))  
        ||  ($filter_date_to && strtotime($order_created_date) > strtotime($filter_date_to)) ) {
            continue; 
        }

        $order_results++;

        echo '<tr>';

            echo '<td class="order-id">';
            $order_edit_url = admin_url('post.php?post=' . $order['order_id'] . '&action=edit');
            echo '<a href="' . esc_url($order_edit_url) . '">' . esc_html($order['order_id']) . '</a>';
            echo '</td>';
            
            if (isset($order['subscription_parent_id'])) {
                
                $subscription_edit_url = admin_url('post.php?post=' . $order['subscription_parent_id'] . '&action=edit');
                echo '<td class="order-subs-id">';
                echo '<a href="' . esc_url($subscription_edit_url) . '">' . esc_html($order['subscription_parent_id']) . '</a>';
                echo '</td>';
                
            } else {
                echo '<td class="order-subs-id">' . (isset($order['subscription_parent_id']) ? esc_html($order['subscription_parent_id']) : '') . '</td>';
            }
            
            echo '<td class="transaction-id">' . esc_html($order['nuvei_debit_data']['id']) . '</td>';
            echo '<td class="transaction-auth">' . esc_html($order['nuvei_debit_data']['authorization_code']) . '</td>';

            echo '<td class="customer"><span>' . $customer . '</span></td>';

            echo '<td class="order-products">';
                foreach ($order['product_names'] as $product_name) {
                    echo esc_html($product_name) . ', ';
                }
            echo '</td>';
            
            echo '<td class="transaction-amount">' . '$' . esc_html($formatted_amount) . '</td>';

            echo '<td class="order-date">' . esc_html(date('d/m/Y', strtotime($order['date']))) . '</td>';
            // echo '<td class="order-amount">' . '$' . esc_html($order['amount']) . '</td>'; // Espacio para el Amount
            echo '<td class="transaction-ref">' . esc_html($order['nuvei_debit_data']['dev_reference']) . '</td>';
            echo '<td class="transaction-desc">' . esc_html($order['nuvei_debit_data']['product_description']) . '</td>';

            echo '<td class="transaction-status ' .  $order_status . '">' . '<span>' . esc_html($order['nuvei_debit_data']['status']) . '</span></td>';

        echo '</tr>';
    }

    echo '</tbody></table>';

    if($order_results === 0) {

        echo '<div class="alert info-alert">';
            echo '<p>No se ha encontrado ninguna orden. Prueba con otra búsqueda.</p>';
        echo '</div>';

    }

    echo '</section>';

    return;
   
}

?>