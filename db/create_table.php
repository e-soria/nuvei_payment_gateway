<?php

function create_user_cards_table() {

    global $wpdb;

    $table = $wpdb->prefix . 'user_cards';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        card_id INT NOT NULL AUTO_INCREMENT,
        card_token VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        subscription_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (card_id),
        UNIQUE (card_token),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $result = dbDelta($sql);

    if (is_wp_error($result)) {

        error_log('Error al crear la tabla: ' . $result->get_error_message());

    } else {

        add_option('user_cards_table_exists', true);

    }
}


register_activation_hook(__FILE__, 'create_user_cards_table');

if (!get_option('user_cards_table_exists')) {

    create_user_cards_table();

}

?>