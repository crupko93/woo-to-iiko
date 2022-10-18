<?php
/**
 * Менеджер лицензий
 *
 * @package woo-to-iiko
 * @author Aleksey Tikhomirov <conta
 */


// This is the secret key for API authentication. You configured it in the settings menu of the license manager plugin.
define('IIKO_SECRET_KEY', '5c44b0dfa40045.77773185'); //Rename this constant name so it is specific to your plugin or theme.

// This is the URL where API query request will be sent to. This should be the URL of the site where you have installed the main license manager plugin. Get this value from the integration help page.
define('LICENSE_SERVER_URL', 'https://woo-to-iiko.rwsite.ru'); //Rename this constant name so it is specific to your plugin or theme.

// This is a value that will be recorded in the license manager data so you can identify licenses for this item/product.
define('IIKO_NAME', 'Woo to Iiko'); //Rename this constant name so it is specific to your plugin or theme.


add_action( 'admin_menu', 'iiko_license_menu' );
function iiko_license_menu() {
  /** add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ); */
  add_options_page( __('Iiko licence manager', 'iiko'), __('Iiko License', 'iiko'), 'manage_options', 'iiko', 'iiko_license_management_page');
}


add_shortcode('license_form', 'iiko_license_management_page');
/**
 *  Форма активации и дективации лицензионного ключа
 */
function iiko_license_management_page() {
	echo '<div class="wrap">';
	echo '<h2>' . __('Iiko License Manager', 'iiko') . '</h2>';
	$result = '';
	if (isset($_REQUEST['deactivate_license'])) {
	  $result = iiko_deactivate_lic( $_REQUEST['iiko_license_key'] );
  } elseif( isset($_REQUEST['activate_license'])) {
	  $result = iiko_activate_lic( $_REQUEST['iiko_license_key']);
  }
	 echo $result;
	?>
	<p><?php
    if(empty(check_license()))
      _e('Please enter the license key for this product to activate it. You were given a license key when you purchased this item.', 'iiko');
    else
	    _e('Thx for your choose!', 'iiko');
      ?>
  </p>

	<form id="activation" action="" method="post">
		<table class="form-table">
			<tr>
				<th style="width:100px;"><label for="iiko_license_key"><?php _e('License Key', 'iiko') ?></label></th>
				<td ><input class="regular-text" type="text" id="iiko_license_key" name="iiko_license_key"  value="<?php echo check_license() ?>" ></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="activate_license" value="<?=__('Activate', 'iiko')?>" class="button-primary" />
			<input type="submit" name="deactivate_license" value="<?=__('Deactivate', 'iiko')?>" class="button" />
		</p>
	</form>

  <script>
   // console.log( 'work it!');
    jQuery('[name=activate_license]').on( function(event){
      //console.log( 'activate', this);
      event.preventDefault(); // останавливаем отправку формы
      ajaxs( 'activate_license', this );
    });
    jQuery('[name=deactivate_license]').on( function(event){
     // console.log( 'deactivate' , this);
      event.preventDefault(); // останавливаем отправку формы
      ajaxs( 'deactivate_license', this );
    } );
  </script>
	<?php
	echo '</div>';
}


/**
 * Активация лицензионного ключа
 *
 * @param string $license_key
 *
 * @return string
 */
function iiko_activate_lic( $license_key = null){

    /*** License activate button was clicked ***/
    if (empty($license_key))
      return null;

    // API query parameters
    $api_params = array(
      'slm_action' => 'slm_activate',
      'secret_key' => IIKO_SECRET_KEY,
      'license_key' => $license_key,
      'registered_domain' => $_SERVER['SERVER_NAME'],
      'item_reference' => urlencode(IIKO_NAME),
    );

    $query = esc_url_raw(add_query_arg($api_params, LICENSE_SERVER_URL));
    $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));
    if (is_wp_error($response)){
      $result = __("Unexpected Error! The query returned with an error.", 'iiko');
    }
    $license_data = json_decode(wp_remote_retrieve_body($response));
    if($license_data->result == 'success'){

      $result = '<br />'. __('The following message was returned from the server: ', 'iiko') .
                '<strong style="color: green; background-color: #c6f7c8; padding: 10px 15px 10px 15px; margin: 50px;">' .
                $license_data->message . '</strong>';

	    update_option( 'iiko_license_key', $license_key );

    } else {
    $result = '<br />' . __('The following message was returned from the server: ', 'iiko') . '<strong style="color: red">' . $license_data->message . '</strong>';
    }
	return $result;
}


/**
 * Деактивация лицензионного ключа
 *
 * @param string $license_key
 *
 * @return mixed
 */
function iiko_deactivate_lic($license_key = null){

    if (empty($license_key)) {
      return null;
    }

	  // API query parameters
		$api_params = array(
			'slm_action' => 'slm_deactivate',
			'secret_key' => IIKO_SECRET_KEY,
			'license_key' => $license_key,
			'registered_domain' => $_SERVER['SERVER_NAME'],
			'item_reference' => urlencode(IIKO_NAME),
		);

		// Send query to the license manager server
		$query = esc_url_raw(add_query_arg($api_params, LICENSE_SERVER_URL));
		$response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

		// Check for error in the response
		if (is_wp_error($response)){
		  return  __('Unexpected Error! The query returned with an error.', 'iiko');
		}

		$license_data = json_decode(wp_remote_retrieve_body($response));
		if($license_data->result == 'success'){
		  $result = '<br />'. __('The following message was returned from the server: ', 'iiko') .
                '<strong style="color: green; background-color: #c6f7c8; padding: 10px 15px 10px 15px; margin: 50px;">' .
                $license_data->message . '</strong>';
	    update_option('iiko_license_key', '');
		} else {
		  $result = '<br />' . __('The following message was returned from the server: ', 'iiko') . $license_data->message;
		}

	return $result;
}


/**
 * Возвращает данные ключа
 *
 * @return string|void false, если нет опции или номер ключа, если он установлен.
 */
function check_license(){
	return get_option('iiko_license_key');
}
/** add_filter( $tag, $function_to_add, $priority, $accepted_args ); */
add_filter('checklicense','check_license', 10, 1);