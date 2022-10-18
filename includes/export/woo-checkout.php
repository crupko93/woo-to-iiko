<?php

//Adding some custom var
global $Lang;

/**
 * WOOCOMMERCE CHECKOUT SETTINGS
 */

add_action('wp_footer', 'iiko_checkout_js');
function iiko_checkout_js(){
  if('yes' === get_option('woo_delivery', 'no') && is_checkout()) {
      require_once IIKO_PLUGIN_DIR . 'includes/export/delivery_method-js.php';
  }
}


add_action( 'wp_enqueue_scripts', 'iiko_front_scripts', 20 );
/**
 * Add js in frontend Checkout page
 */
function iiko_front_scripts() {

  /** JS register */
    wp_register_script( 'jquery.datetimepicker', IIKO_PLUGIN_URL . 'assets/js/jquery.datetimepicker.full.min.js', array(), '1.3.4', true );// inc datetime
    wp_register_script( 'jquery.maskedinput', IIKO_PLUGIN_URL . 'assets/js/jquery.maskedinput.min.js', array(), '1.4.1', true );//phone mask
	  wp_register_script( 'iiko.checkout', IIKO_PLUGIN_URL . 'assets/js/checkout.js', array('jquery'), '1', true );//iiko checkout
    wp_register_script( 'jquery.kladr', IIKO_PLUGIN_URL . 'assets/js/jquery.kladr.min.js', array('jquery'), '1.0.0.', true );// inc klard
	  wp_register_script( 'jquery.validate', IIKO_PLUGIN_URL . 'assets/js/jquery-validate.js', array('jquery'), '1.0.', true );

    if(!is_checkout()) {
        return;
    }

  /** CSS */
  wp_enqueue_style( 'jquery.datetimepicker', IIKO_PLUGIN_URL . 'assets/css/jquery.datetimepicker.css' );// inc css
  wp_enqueue_style( 'kladr', IIKO_PLUGIN_URL . 'assets/css/jquery.kladr.min.css' );// inc css

  /** JS Enqueue */
	wp_enqueue_script('jquery.datetimepicker');
  wp_enqueue_script('iiko.checkout');
	wp_enqueue_script('jquery.validate');

  if( 'yes' === get_option('select_time_inline') or empty(get_option('select_time_inline')) )
    $inline = true;  else    $inline = false;

  if(false !== get_option('rest_time_start'))
//    $start = get_option('rest_time_start');
    $start = '10';
  else $start = '10';

  if(false !== get_option('rest_time_end'))
//    $end = get_option('rest_time_end');
    $end = '22';
  else $end = '23';

  if(false !== get_option('rest_max_day'))
    $rest_max_day = get_option('rest_max_day');
  else $rest_max_day = '7';

  if(false !== get_option('rest_time_int'))
    $rest_time_int = get_option('rest_time_int');
  else $rest_time_int = '0';

  
  /**
   * Date and time Picker
   *
   * jQuery datetimepicker settings Documentation: https://xdsoft.net/jqplugins/datetimepicker/
   */
    
    // Sett location for timepicker
    
    $lg = '';
    if(get_locale() == 'ro_RO'){
        $lg = 'ro';
    }else{
        $lg = 'ru';
    }    
    
  wp_add_inline_script('jquery.datetimepicker', " 
          Date.prototype.addHours= function(h){
              this.setHours(this.getHours()+h);              
              return this;
          }          
          jQuery.datetimepicker.setLocale('".$lg."');          
          jQuery('#datetimepicker').datetimepicker({
           format:'d.m.Y H:i',
           timepicker: true,
           mask: true,                  
           minDate: 0,
           maxDate:'+1970/01/{$rest_max_day}',
           defaultDate: new Date(),
           minTime: new Date(new Date().setHours({$start})),
           maxTime: new Date(new Date().setHours({$end})),
           defaultTime: new Date().addHours({$rest_time_int}),
           inline: '{$inline}'                      
          });
  ");

  /**
   * Phone mask
   */
//  $mask = !empty(get_option('tel_mask')) ? get_option('tel_mask') : '8(999) 999-99-99';
  $mask = '37369423639';
  wp_enqueue_script('jquery.maskedinput');// inc js + inline
  wp_add_inline_script('jquery.maskedinput', "
  jQuery(function(){
    jQuery('#phone').mask('{$mask}');
  });
  ");

	/**
	 * Iiko autocomplete
   * @see  https://github.com/garakh/kladrapi-jsclient
	 */
  $city_id = get_option('iiko_city_id');
  wp_enqueue_style( 'jquery.kladr', IIKO_PLUGIN_URL . 'assets/css/jquery.kladr.min.css' );// inc css
  wp_enqueue_script('jquery.kladr');
  wp_add_inline_script('jquery.kladr', '   
    jQuery(document).ready(function( $ ) {
      
      var $address = $(\'[name="billing_street"]\');  
      // console.log( $(\'#street_iiko_name\').val( obj.name ) );
      
      $address.kladr({
        token: null,
        type: null,
        oneString: true,
        limit: 10,
        parentType: $.kladr.type.city,
        parentId: "'. $city_id .'",
        labelFormat: function (obj, query) { 
          //console.log(obj);
          label = obj.type + \' \' + obj.name;
          return label; 
        },
        change: function (obj) { 
        
          if(obj != null){
            if(obj.type === "Улица"){
              $(\'#street_iiko_name\').val( obj.name );
            } else {
              $(\'#street_iiko_name\').val( obj.name + \' \' + obj.type.toLowerCase());   
            }
            $(\'#street_id\').val( obj.id);
            return $address.val( obj.type + \' \' + obj.name );
          }
            
        }		
      });   
    });
  ');
}


/**
 * Выыод объявления на странцие оформления заказа
 * @param $var
 */
function iiko_before_checkout_form($var){
  $message = esc_attr(get_option('checkout_description'));
  //$message = __("Указывайте время доставки с учетом приготовления пиццы. Время приготовления пиццы ~30 минут.");
  if(!empty($message)) {
      echo "<p class=\"woocommerce-info\">$message</p>";
  }
}
add_action('woocommerce_before_checkout_billing_form', 'iiko_before_checkout_form');


add_action('woocommerce_after_checkout_billing_form', 'iiko_after_checkout_form');
function iiko_after_checkout_form($var){
  ?>
  <style>
    .display-none {
      display: none !important;
    }
    input[type=radio]+label {
      display: inline-block;
      margin: 0 25px 0 5px;
    }
    #ship-to-different-address{
      display: none;
    }
    .optional{
      display: none;
    }
    label.error {
      color: #e2401c;
    }
  </style>
  <?php
}

/**
 * Custom checkout fields
 **/
add_filter( 'woocommerce_checkout_fields', 'iiko_set_default_fields' );
function iiko_set_default_fields( $fields ) {

    $fields['shipping'] = [];

    unset(
        $fields['billing']['billing_postcode'],
        $fields['billing']['billing_country'],
        $fields['billing']['billing_state'],
        $fields['billing']['billing_postcode'],
        $fields['billing']['billing_company'],
        $fields['billing']['billing_city'],
        $fields['billing']['billing_address_2']
    );
    #unset($fields['billing']['billing_address_1']);

    $fields['billing']['billing_address_1']['class'] = array('billing_address_1', 'display-none', );
    $fields['billing']['billing_address_1']['label'] = __('Address', 'iiko');
    $fields['billing']['billing_address_1']['label_class'] = 'screen-reader-text';
    $fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_1']['priority'] = 60;

    $fields['billing']['billing_phone']['type'] = 'text';
    $fields['billing']['billing_phone']['id'] = 'phone';
    $fields['billing']['billing_phone']['disabled'] = 'true';
    $fields['billing']['billing_phone']['class'] = array('phone','form-row', 'form-row-first');
    $fields['billing']['billing_phone']['priority'] = 21;

    $fields['billing']['billing_email']['id'] = 'email';
    $fields['billing']['billing_email']['class'] = array('email', 'form-row', 'form-row-last');
    $fields['billing']['billing_email']['priority'] = 22;

    $fields['billing']['billing_email']['required'] = false;
    $fields['billing']['billing_last_name']['required'] = false;

  return $fields;
}


/**
 * My account fields edit
 *
 * @param $address - fields name
 * @param $load_address - prefix billing or shipping
 *
 * @return mixed
 */
function woocommerce_acc_filter_fields( $address, $load_address ){

    $fields = $address;

    if( isset($fields[$load_address . '_last_name']) ){
        $fields[$load_address . '_last_name']['required'] = false;
    }

    if($load_address === 'billing'){ // not working ?!
        $fields['billing_phone']['priority'] = 21;
        $fields['billing_email']['priority'] = 22;
        $fields['billing_phone']['class'] = array('phone','form-row', 'form-row-first');
        $fields['billing_email']['class'] = array('email', 'form-row', 'form-row-last');
    }

    unset(
        $fields[$load_address . '_postcode'],
        $fields[$load_address . '_state'],
        $fields[$load_address . '_postcode'],
        $fields[$load_address . '_company'],
        $fields[$load_address . '_city'],
        $fields[$load_address . '_address_2'],
        $fields[$load_address . '_address_1']
    );

    return $fields;
}
add_filter( 'woocommerce_address_to_edit', 'woocommerce_acc_filter_fields', 10, 2 );

/**
 * Добавляет поля на страницу оформления заказа
 *
 * @param array $fields
 * @return array
 */
function custom_override_default_locale_fields( $fields )
{

  $ru = [
     'Time of delivery' => 'Время доставки', 
     'As fast as possible' => 'Как можно быстрее', 
     'Select delivery time' => 'Выбрать время доставки', 
     'Choose the date and time of delivery' => 'Выберите дату и время доставки', 
//     'Delivery date and time' => 'Время доставки', 
     'Delivery type' => 'Тип доставки', 
     'Delivery to home' => 'Доставка на дом', 
     "I'll take it myself" => 'Сам заберу', 
     "street" => 'Улица', 
     "street name" => 'Название улицы', 
     "home" => 'Дом', 
     "housing" => 'Корпус', 
     "apartment" => 'Квартира', 
     "entrance" => 'Подъезд', 
     "bancnote" => 'Купюра', 
  ];
    
  $ro = [
     'Time of delivery' => 'Timp de livrare', 
     'As fast as possible' => 'Cât mai repede', 
     'Select delivery time' => 'Selectați termenul de livrare', 
     'Choose the date and time of delivery' => 'Selectați data și ora livrării', 
     'Delivery date and time' => 'Время доставки', 
//     'Delivery date and time' => 'Время доставки', 
     'Delivery type' => 'Tip livrare', 
     'Delivery to home' => 'Livrare la domiciliu', 
     "I'll take it myself" => 'O voi lua eu', 
     "street" => 'Strada', 
     "street name" => 'Numele strazii', 
     "home" => 'Bloc', 
     "housing" => 'Corp', 
     "apartment" => 'Apartament', 
     "entrance" => 'Intrare', 
     "bancnote" => 'Bancnote', 
  ]; 
    
  $lang = '';
    
  if(get_locale() == 'ro_RO')
  {
      $lang = $ro;
  }
  else
  {
      $lang = $ru;
  }
/*    
  $fields['time_choose'] = array(
    'id'             => 'time_choose',
//    'label'          => __('Time of delivery', 'iiko'),
    'label'          => $lang['Time of delivery'],
    'label_class'    =>  array(''),
    'type'           => 'select',//radio
//    'options'        => array( 0 => __("As fast as possible", 'iiko'), 1 => __("Select delivery time", 'iiko')),
    'options'        => array( 0 => $lang['As fast as possible'], 1 => $lang['Select delivery time']),
    'default'        => 0,
    'class'          => array ("time_choose","form-row","form-row-first"), // radio - array ("time_choose","form-row")
    'priority'       => 3,
    'required'       => true
  );
*/    
/*
  $fields['date_time'] = array(
    'id'             => 'datetimepicker',
//    'label'          => __('Choose the date and time of delivery', 'iiko'),
    'label'          => $lang['Choose the date and time of delivery'],
    'label_class'    =>  array(''),
    'placeholder'    => __('Delivery date and time', 'iiko'),
    'class'          => array ('datetime', 'form-row-wide', 'display-none'),
    'autocomplete'   => false,
    'default'        => '',
    'priority'       => 9,
    'required'       => false
  );
*/
  $fields['delivery_type'] = array(
    'id'            => 'delivery_type',
//    'label'          => __('Delivery type', 'iiko'),
    'label'          => $lang['Delivery type'],
    'label_class'    =>  array(''),
    'type'           => 'select',
//    'options'        => array(0 =>  __('Delivery to home', 'iiko'), 1 =>  __( "I'll take it myself", 'iiko' )),
    'options'        => array(0 =>  $lang['Delivery to home'], 1 =>  $lang["I'll take it myself"]),
    'default'        => '0',
    'class'          => array ('delivery_type form-row', 'form-row-last'),
    'priority'       => 4,
    'required'       => false,
  );

	$fields['phone'] = array(
		'type' => 'text',
		'id' => 'phone',
        'label'       => __('Phone number', 'iiko'),
        'label_class' =>  array(''),
		'class' => array('phone','form-row', 'form-row-first'),
		'priority' => 21
	);

	$fields['email'] = array(
		'id' => 'email',
    'label'          => __('Email', 'iiko'),
    'label_class'    =>  array(''),
		'class' => array('email', 'form-row', 'form-row-last'),
		'priority' => 22,
		'required' => false
	);

  $fields['street'] = array(
    "id"            => 'street',
//    "label"         => __("Streetщ", 'iiko'),
    "label"         => $lang['street'],
    "label_class"    =>  array(''),
//    "placeholder"   => __("Street name", 'iiko') . " *",
    "placeholder"   => $lang['street name']. " *",
    "class"         => array ("street","form-row-wide"),
    "autocomplete"  => "",
    "priority"      => 50,
    "required"      => false
  );

  $fields["street_id"] = array(
    "id"             => 'street_id',
    "label_class"    =>  array("screen-reader-text"),
    "placeholder"    => __("Street id in Iiko", 'iiko'),
    "class"          => array ('street_id', 'display-none'),
    "autocomplete"   => '',
    "priority"       => 51,
    "required"       => false
  );

  $fields["street_iiko_name"] = array(
    "id"             => 'street_iiko_name',
    "label"          => __('Street name in Iiko', 'iiko'),
    "label_class"    =>  array("screen-reader-text"),
    "placeholder"    => __("Street name in Iiko", 'iiko'),
    "class"          => array ("street_iiko_name", "display-none",),
    "autocomplete"   => '',
    "priority"       => 55,
    "required"       => false
  );

  $fields["home"] = array(
    "id"             => 'home',
//    "label"          => __("Home", 'iiko'),
    "label"          => $lang['home'],
    "label_class"    =>  array(""),
//    "placeholder"    => __("Home", 'iiko') . ' *',
    "placeholder"    => $lang['home'] . ' *',
    "class"          => array ("home", "form-row-first"),
    "autocomplete"   => "",
    "priority"       => 60,
    "required"       => false
  );

  $fields["housing"] = array(
    "id"             => 'housing',
//    "label"          => __("Housing", 'iiko'),
    "label"          => $lang['housing'],
    "label_class"    =>  array(""),
//    "placeholder"    => __("Housing", 'iiko'),
    "placeholder"    => $lang['housing'],
    "class"          => array ("housing", "form-row-last"),
    "autocomplete"   => "",
    "priority"       => 70,
    "required"       => false
  );

  $fields["apartment"] = array(
    "id"             => 'apartment',
//    "label"          => __("Apartment", 'iiko'),
    "label"          => $lang['apartment'],
    "label_class"    =>  array(""),
//    "placeholder"    => __("Apartment", 'iiko'),
    "placeholder"    => $lang['apartment'],
    "class"          => array ("apartment", "form-row-first"),
    "autocomplete"   => "",
    "priority"       => 80,
    "required"       => false
  );

  $fields["entrance"] = array(
    "id"             => 'entrance',
//    "label"          => __('Entrance', 'iiko'),// Подъезд
    "label"          => $lang['entrance'],// Подъезд
    "label_class"    =>  array(""),// screen-reader-text
//    "placeholder"    => __('Entrance', 'iiko'),
    "placeholder"    => $lang['entrance'],
    "class"          => array ("entrance", "form-row-last"),
    "autocomplete"   => "",
    "priority"       => 90,
    "required"       => false,
  );
    
  $fields["bancnote"] = array(
    "id"             => 'bancnote',
    "label"          => '', // $lang['bancnote']
//    "type"          => 'hidden',  
    "label_class"    =>  array(""),
    "placeholder"    => '', // $lang['bancnote']
    "class"          => array ("bancnote", "form-row-last"),
    "autocomplete"   => "",
    "priority"       => 100,
    "required"       => false,
  );    
    
  $fields["surrender"] = array(
    "id"             => 'surrender',
    "label"          => '',
    "type"          => 'checkbox',
    "label_class"    => array(""),
    "placeholder"    => '',
    "class"          => array ("surrender", "form-row-last"),
    "autocomplete"   => "",
    "priority"       => 110,
    "required"       => false,
  );      

  $fields['address_1'] = array(
	    'class' => array('address_1', 'display-none'),
	    'label' => __('Address', 'iiko'),
	    'label_class' => 'screen-reader-text',
	    'required' =>  false,
	    'priority' => 60
  );

	//echo '<pre>'; var_dump( $fields ); echo '</pre>';
	unset($fields['company']);
	unset($fields['city']);
	unset($fields['state']);
	unset($fields['postcode']);

	// Only on account pages
	if( is_account_page() ) {
	  unset( $fields['street_iiko_name'], $fields['delivery_type'], $fields['date_time'], $fields['time_choose'], $fields['street_id'] );
		return $fields;
	}

  return apply_filters('iiko_checkout_fields', $fields);
}
add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields', 20, 1 );


/**
 * Update the order meta with field value
 **/
add_action( 'woocommerce_checkout_update_order_meta', 'iiko_update_order_meta', 20, 1 );
function iiko_update_order_meta( $order_id ) {
  //check if $_POST has our custom fields

  if ( !empty($_POST['billing_delivery_terminal']) ) {
      update_post_meta($order_id, 'billing_delivery_terminal', esc_attr($_POST['billing_delivery_terminal']));
  }

  if ( !empty($_POST['billing_delivery_type']) ) {
      update_post_meta($order_id, 'billing_delivery_type', $_POST['billing_delivery_type']);
  }

  if ( !empty($_POST['billing_time_choose']) ) {
      update_post_meta($order_id, 'billing_time_choose', esc_attr($_POST['billing_time_choose']));
  }

  if ( !empty($_POST['billing_date_time']) and $_POST['billing_date_time'] !== '__.__.____ __:__' ) {
	  update_post_meta( $order_id, 'billing_date_time', esc_attr( $_POST['billing_date_time'] ) );
  }

  if ( !empty($_POST['billing_street']) ) {
      update_post_meta($order_id, 'billing_street', $address[] = esc_attr($_POST['billing_street']));
  }

  if ( !empty($_POST['billing_street_id']) ) {
      update_post_meta($order_id, 'billing_street_id', esc_attr($_POST['billing_street_id']));
  }

  if ( !empty( $_POST['billing_street_iiko_name']) ) {
      update_post_meta($order_id, 'billing_street_iiko_name', esc_attr($_POST['billing_street_iiko_name']));
  }

  if ( !empty($_POST['billing_home']) ) {
    update_post_meta($order_id, 'billing_home', esc_attr($_POST['billing_home']));
    $address[] = __('Home', 'iiko') . esc_attr($_POST['billing_home']);
  }

  if ( !empty($_POST['billing_housing']) ) {
    update_post_meta($order_id, 'billing_housing', esc_attr($_POST['billing_housing']));
    $address[] = __('Housing', 'iiko') . esc_attr($_POST['billing_housing']);
  }

  if ( !empty($_POST['billing_entrance']) ){
    update_post_meta( $order_id, 'billing_entrance', esc_attr( $_POST['billing_entrance'] ) );
    $address[] = __('Entrance', 'iiko') . esc_attr( $_POST['billing_entrance'] );
  }

  if ( !empty($_POST['billing_apartment']) ) {
    update_post_meta($order_id, 'billing_apartment', esc_attr($_POST['billing_apartment']));
    $address[] = __('Apartment', 'iiko') . esc_attr($_POST['billing_apartment']);
  }

  if ( !empty($address)) {
      update_post_meta($order_id, '_billing_address_1', implode(', ', $address));
  }
    
 /*****************************************************************************************/
 if ( !empty( $_POST['billing_bancnote']) ) {
     update_post_meta($order_id, 'billing_bancnote', esc_attr($_POST['billing_bancnote']));
 }
    
 if ( !empty( $_POST['billing_surrender']) ) {
     update_post_meta($order_id, 'billing_surrender', esc_attr($_POST['billing_surrender']));
 }    

}

// display the extra data in the order admin panel
function kia_display_order_data_in_admin( $order ){  ?>
    <div class="order_data_column" style="width:100%;">
        <?php 
            $surrender = '';
            $delivery_type = '';
            if($order->get_meta( 'billing_surrender' )){ $surrender = 'Да'; }else{ $surrender = 'Нет'; }
            if($order->get_meta( 'billing_delivery_type' )){ $delivery_type = 'Доставка'; }else{ $delivery_type = 'Самовывоз'; }
            
            if(!empty($order->get_meta( 'billing_bancnote' ))){
                echo '<p><strong>Сдача с суммы:</strong> ' . $order->get_meta( 'billing_bancnote' ) . ' </p>';
            }      
                                                   
            //if(empty($order->get_meta( 'billing_surrender' ))){
            //    echo '<p><strong>Без сдачи :</strong> ' . $surrender . ' </p>'; 
            //}
		
			echo '<p><strong>Без сдачи :</strong> ' . $surrender . ' </p>';
                                                   
            if(!empty($order->get_meta( 'billing_delivery_type' ))){
                echo '<p><strong>Тип доставки :</strong> ' . $delivery_type . $order->get_meta( 'billing_delivery_type' ). ' </p>'; 
            }                                           
        ?>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'kia_display_order_data_in_admin' );


/**
 * Add the field to order emails
 *
 * @param array $field
 * @param $sent_to_admin
 * @param $order
 *
 * @return array
 */
function rw_checkout_field_order_meta_keys( $field = array(), $sent_to_admin, $order ) {
  # $keys[__('Restaurant', 'iiko')] = 'billing_delivery_terminal';

    $field[] = array(
            'label' => __('Delivery method', 'iiko'),
            'value' => apply_filters('delivery_name', $order)
    );
    $field[] = array(
        'label' => __('Delivery date & time', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_date_time', true)
    );
    $field[] = array(
        'label' => __('Street', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_street', true)
    );
    $field[] = array(
        'label' => __('Home', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_home', true)
    );
    $field[] = array(
        'label' => __('Housing', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_housing', true)
    );
    $field[] = array(
        'label' => __('Entrance', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_entrance', true)
    );
    $field[] = array(
        'label' => __('Apartment', 'iiko'),
        'value' => get_post_meta( $order->get_id(),'billing_apartment', true)
    );

    //$keys[__('Delivery date & time', 'iiko')] = 'billing_date_time';
    //$keys[__('Street', 'iiko')] = 'billing_street';
    //$keys[__('Home', 'iiko')] = 'billing_home';
    //$keys[__('Housing', 'iiko')] = 'billing_housing';
    //$keys[__('Entrance', 'iiko')] = 'billing_entrance';
    //$keys[__('Apartment', 'iiko')] = 'billing_apartment';

  return $field;
}
add_filter( 'woocommerce_email_order_meta_fields', 'rw_checkout_field_order_meta_keys', 10, 3 );

/**
 * Возвращаем id (GUID) типа доставки ресторана на основе выбора пользователя
 *
 * @param $user_selection - выбор пользователя
 * @return int - id доставки
 */
function change_delivery_type($user_selection){
  console_log($user_selection);
  if($user_selection === '0') // Доставка
  {
      $selection = 'DELIVERY_BY_COURIER';
  }
  elseif($user_selection === '1') // Самовывоз
  {
      $selection = 'DELIVERY_PICKUP';
  }
  else {
      return '';
  }

  $types = IikoApi::getOrderTypes();
  if(is_array($types) and !empty($types)){
    foreach ( $types as $type ){
      if($type->orderServiceType === $selection) {
          return $type->id;
      }
    }
  } else {
      return '';
  }
}
add_filter('change_pay_type', 'change_pay_type');


//TODO: Доделать!!!!
/**
 * Возвращаем код типа оплаты
 *
 * @param string $user_selection - название способа оплаты, которого выбрал пользователь
 *
 * @return string - payment code
 */
function change_pay_type($user_selection)
{
  $payment_one = !empty(get_option('woocommerce_alg_custom_gateway_1_title')) ? get_option('woocommerce_alg_custom_gateway_1_title') : 'Оплата наличными при доставке';
  $payment_two = !empty(get_option('woocommerce_alg_custom_gateway_2_title')) ? get_option('woocommerce_alg_custom_gateway_2_title') : 'Оплата картой при доставке';

  if($user_selection === $payment_one ||
     $user_selection === $payment_two
    ) { // Card
      $selection = 'CASH';
  } else {
    $selection = 'CARD';
  }

  return $selection;

  $types = IikoApi::getPaymentTypes('PaymentTypes');
  if(is_array($types) and !empty($types)){
    foreach ( $types as $type ){
      if($type->code === $selection) {
          return $type->code;
      }
    }
  } else {
      $selection = 'CASH';
  }
  return $selection;
}

