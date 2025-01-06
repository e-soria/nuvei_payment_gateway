<?php

function tokenize_form($atts) { 
    
  $atts = shortcode_atts(array(
    'is_checkout' => false,
  ), $atts);

  ?>
  
  <div id="tokenize-form">
      
    <div id='tokenize-form-container'>
          
      <div id="response"></div>
          
      <div id='tokenize_example'></div>

      <form id="tokenize_form" method="post" style="display: none;">
          <input type="hidden" name="action" value="save_card">
      </form>

      <input type="button" id='tokenize_btn' class='tok_btn' value="Guardar tarjeta">
      <input type="button" id='retry_btn' class='tok_btn' display='none' value="Guardar nueva tarjeta">
      
    </div>

  </div>

  <?php  


  set_form_settings();

  wp_localize_script('init-tokenization', 'isCheckout', $atts);
  
}
  
add_shortcode('tokenize_form', 'tokenize_form'); 


function set_form_settings() {

  // init js files
  $plugin_url = plugin_dir_url(basename(__FILE__)) . 'nuvei-gateway/'; 

  wp_enqueue_script('nuvei-form', $plugin_url . 'api/nuvei-form.js', array('jquery'), null, true);
  wp_enqueue_script('init-tokenization', $plugin_url . 'api/init-tokenization.js', array('jquery', 'nuvei-form'), null, true);

  // get user data from plugin_hooks.php and send them to nuvei-form.js
  $user_data = get_user_data();
  wp_localize_script('nuvei-form', 'userData', $user_data);

  // get credentials from plugin_hooks.php and send them to nuvei-form.js
  $credentials = get_app_credentials();
  wp_localize_script('nuvei-form', 'credentials', $credentials);

}

