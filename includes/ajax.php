<?php
/**
 * Функции обработчики ajax
 * Для работы необходим плагин Ajax Simple
 *
 */


// функция-обработчик запроса. 'ajaxs_' - обязательный префикс

/**
 *  Выбор ресторана
 *
 * @param $jx
 * @return bool
 */
function ajaxs_set_rest_id( $jx ){
  $jx->console( 'Выбран ресторан с ID: ' . $jx->selectrest );
  // Запишем в опцию. Обновим значение.
  if(empty($jx->selectrest))
    return false;

  update_option( 'iiko_rest_id', $jx->selectrest, false );

  /** удаляем старое значение терминала, если оно есть */
  ajaxs_reset_terminal( $jx );

  $jx->jseval( "jQuery('#rest .succes').show();" );
  $jx->jseval( "jQuery('#rest .iiko_result').html(' " . __('Restaurant selected', 'iiko') . " $jx->selectrest. " .
               __('Please, reload this page to choose terminal.', 'iiko') ."');");
  $jx->console("Ресторан {$jx->selectrest} Сохранен.");

}

/**
 *  Выбор терминала
 *
 * @param $jx
 * @return bool
 */
function ajaxs_set_terminal_id( $jx ){
  $jx->console( 'Выбран ресторан с ID: ' . $jx->select_terminal );
  // Запишем в опцию. Обновим значение.
  if(empty($jx->select_terminal))
    return false;

  /** Записываем ID терминала в опцию */
  update_option( 'iiko_terminal', $jx->select_terminal, false );

  $jx->jseval( "jQuery('#terminal .succes').show();" );
  $jx->jseval("jQuery('#terminal .iiko_result').html('". __('Restaurant ID selected:', 'iiko') . " {$jx->select_terminal}." .
              __('Please, reload this page to choose categories.', 'iiko') ." ');");
  $jx->console("Терминал: $jx->select_terminal Сохранен.");

}

/**
 * Выбор категорий товаров
 *
 * @param $jx
 * @return bool
 */
function ajaxs_set_product_cats( $jx ){
    //$jx->console( 'Выбраны категории: ' . $jx->products );
    if(empty($jx->select_product_cats)) {
        return false;
    }

    // Запишем в опцию. Обновим значение.
    $term_ids = $result = array();

    if ( ! empty($jx->products)) {
        $products = unserialize($jx->products);
    }

	if(!is_array($products["groups"]) or empty($products["groups"])) {
        return false;
    }

    foreach ($products["groups"] as $category) {
	    $category = (object)$category;
        foreach ($jx->select_product_cats as $select_id){
            if($category->id === $select_id){
                $response = add_woo_cats( $category, $jx );
                //$jx->console($response); // false
                if( is_array($response) ) {
	                $term_ids[] = $response['term_id'];//Запишем в выборку пользователя
	                $term = get_term( $response['term_id'], false, 'OBJECT', 'raw' );
	                $jx->jseval( "jQuery('#iiko-result').after('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" .
	                             __('Category', 'iiko') . "<b> $term->name </b>" . __('already exists.', 'iiko') . "</div>');" );
	                console_log(__('Category', 'iiko') . '"' . $term->name . '"' . __('already exists.', 'iiko') );
                }
                elseif( $response !== false ){
                    $term_id = $response;
                    $term_ids[] = $term_id;//Запишем в выборку пользователя
                    $term = get_term( $term_id, false, 'OBJECT', 'raw' );
                    $jx->jseval("jQuery('#iiko-result').after('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" .
                                __('Category', 'iiko') . " <b> $term->name </b>" . __('has been added', 'iiko') ."</div>');");
                    console_log( __('Category', 'iiko') .'"' . $term->name . '"' . __('has been added', 'iiko'));
                } else { // false or string
                    $message = __('An error occurred while adding the category: ', 'iiko') . (string) $response;
                    console_log( $message, true);
                    $jx->error( $message );
                }
            }
        }
    }
    // Запишем выбранные ID
    if( is_array($term_ids) and !empty($term_ids) ) {
      $result = update_option('iiko_product_cats', array_combine($term_ids, $jx->select_product_cats), false);
      if($result == false or is_wp_error($result))
        $jx->error($result);
    } else {
      $jx->error( __('No value!', 'iiko') . json_encode($term_ids) );
    }

    $jx->jseval( "jQuery('#product_cats .succes').show();" );
    $jx->done( __('Everything went well!','iiko') );
}


/**
 * Импорт товаров в Woocommerce
 *
 * @param $jx
 */
function ajaxs_set_products( $jx ){
	//$jx->console( __FUNCTION__ . " start.. ");
	set_time_limit(0);
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 0);
	ini_set('session.gc_maxlifetime', 3600);

    if(!empty(get_option('iiko_types_for_download'))) {
        $types_for_download = get_option('iiko_types_for_download');
    } else {
        $types_for_download = 'all';
    }

    $products = unserialize($jx->products);
    $iiko_terminal = get_option ('iiko_terminal');
	$iiko_product_cats = get_option ('iiko_product_cats');
	$types_for_download = get_option('iiko_types_for_download');
	$only_price = get_option('iiko_update_only_price');
	$img_upload = get_option('iiko_no_img_uploads');

	#$jx->console( $iiko_product_cats . " start.. ");

	$count = $result = 0;
	foreach ($products as $product){
	    //$jx->console("foreach start" . json_encode($product));
	    $product = json_decode(json_encode($product));
		if(empty(check_license()) ) {
			if ( $count >= 2 * 5 ) {
				break;
			}
		}
	    $result = add_woo_products( $product, $iiko_product_cats, $iiko_terminal, $only_price, $types_for_download, $img_upload );
	    //$jx->console("Результат: ". json_encode($result));
	    if(true === $result){ // Если все ок увеличиваем на 1
	      $count++;
	    } elseif(false !== $result) { // WP_Error
		    if ( is_object($jx) ) { $jx->console( "При загрузке товара произошла ошибка!" . $result ); }
	    }
	}

	if ( is_object($jx) ) {
		$message = sprintf( __( "Total uploaded and updated products: <strong>%s</strong>", 'iiko' ), $count );
		$jx->console( "Products loaded." );
		$jx->jseval( "
			jQuery('#products .iiko_result').html('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><p>" . $message . "</p></div>');
		" );
		$jx->jseval( "jQuery('#products .succes').show();" );
		$jx->done( $result );
	}
}


/**
 *  Сброс списка ресторанов
 *
 * @param $jx
 */
function ajaxs_reset_rest( $jx ){
    #$jx->console( __FUNCTION__ . " start.. ");

    /** Удаляю транзиты организаций */
	delete_transient('token');
    delete_transient('OrganizationList');
    delete_option("iiko_orgs"); // Список всех организаций
    delete_option("iiko_rest_id"); // Выбранная организация

    /** Удаляю транзиты терминалов */
     ajaxs_reset_terminal( $jx );

    $message = __("Transient and options organization has be removed. Please reload this page.",'iiko');
    $jx->console( $message );
    $jx->jseval("jQuery('#reset .result').after('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" .
      $message . "</div>');");
}

/**
 * Сброс списка терминалов
 *
 * @param $jx
 */
function ajaxs_reset_terminal( $jx ){
  #$jx->console( __FUNCTION__ . " start.. ");

    delete_transient('DeliveryTerminal'); // getDeliveryTerminal
    delete_option("iiko_terminal");

    /** Удаляем товары */
    ajaxs_reset_products ( $jx );

    $message = __("Transient and options terminals has be removed. Please reload this page.", 'iiko');

    $jx->console($message);
    $jx->jseval("jQuery('#reset .result').after(
      '<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" . $message . "</div>');");
}


/**
 * Сброс списка категорий + товаров
 *
 * @param $jx
 */
function ajaxs_reset_products ( $jx ){
    #$jx->console( __FUNCTION__ . " start.. ");

    delete_transient('Nomenclature');
    delete_option("iiko_product_cats");

    $iiko = IikoApi::get_settings();
    $jx->console("Get iiko settings: " . $iiko["serv"]);

    $message = __("Transient and options products and categories has be removed. Please reload this page.", 'iiko');

    $jx->console( $message );
    $jx->jseval("jQuery('#reset .result').after('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" .
      $message . "</div>');");
}


/**
 * Удаление товаров и изображений товаров в Woocommerce
 *
 * @param $jx
 */
function ajaxs_delete_products ( $jx ){
  $jx->console( __FUNCTION__ . ' start.. ');

  $maximum_time   = ini_get('max_execution_time');
  $timestamp      = time();
  $timeout_passed = false;
  $removed        = 0;

  $args = array(
    'post_type'   => array( 'product', 'product_variation' ),
    'post_status' => get_post_stati(),
    'numberposts' => 150,
  );
  $products = get_posts( $args );


  $msg = sprintf(__( 'Trying to remove %s products.', 'iiko'), sizeof( $products ) );
  $jx->jseval("jQuery('#reset .result').html('<div class=\"uk-alert uk-animation-fade\" uk-alert>" . $msg ."</div>');");

  foreach( $products as $product ) {
    $_product = wc_get_product( $product->ID );
    if ( $_product ) {
      $iiko_id = get_post_meta( $product->ID, "iiko-id", true );
      if(empty($iiko_id)) {
	     continue; // Пропустить
      }
      iiko_delete_img($product->ID);
      //printf( '<li>%s</li>', $_product->get_formatted_name() );
      wp_delete_post( $product->ID, $force_delete = true );
      $removed++;
    }
  }
  $msg =  sprintf(__( 'Removed %s products.', 'iiko'), $removed ) ;
  $jx->jseval("jQuery('#reset .result').html('<div class=\"uk-alert-success uk-animation-fade\" uk-alert><a class=\"uk-alert-close\" uk-close></a>" .
    $msg . "</div>');");
  $jx->success( $msg );
}



/**
 * Активация лицензионного ключа
 *
 * @param $jx
 * @return string
 */
function ajaxs_activate_license ( $jx ){
    //$jx->console( __FUNCTION__ . " start.. ");
    return iiko_activate_lic($jx->iiko_license_key);
}

/**
 * Деактивация лицензионного ключа
 *
 * @param $jx
 * @return mixed
 */
function ajaxs_deactivate_license ( $jx ){
    //$jx->console( __FUNCTION__ . " start.. ");
    return iiko_deactivate_lic($jx->iiko_license_key);
}


/**
 * Save export payment title
 *
 * @param $jx
 */
function ajaxs_save_payment_setting($jx){

    $cash = IikoExport::get_custom_gateway_title($num = '1');
    $card = IikoExport::get_custom_gateway_title($num = '2');

    if( !isset($jx->cash, $jx->card) ) {
        return;
    }

    $res = true;
    if($jx->cash !== $cash['title']){
        $cash['title'] = $jx->cash;
        $res = IikoExport::set_custom_gateway_title( $cash ,$num = '1');
    }

    if($jx->card !== $card['title']){
        $card['title'] = $jx->card;
        $res = IikoExport::set_custom_gateway_title( $card ,$num = '2');
    }

    $jx->done($res);
}