<?php
/**
 * Функции обработки и загрузки данных в WordPress
 */

/** unused */
function get_woocommerce_categories(){
  $taxonomy     = 'product_cat';
  $orderby      = 'name';
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no
  $title        = '';
  $empty        = 0;

  $args = array(
    'taxonomy'     => $taxonomy,
    'orderby'      => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'hierarchical' => $hierarchical,
    'title_li'     => $title,
    'hide_empty'   => $empty
  );
  $all_categories = get_categories( $args );
  return $all_categories;
}

/**
 * Добавляем категорию товаров в Woocommerce, если категория есть возвращаем массив с id
 *
 * @param $category
 * @param null $jx - ajax
 *
 * @return array|bool|mixed|WP_Error
 */
function add_woo_cats( $category, $jx = null ){
	if(!is_object($category))
		return false;

  $taxonomy = 'product_cat';
  $response = term_exists($category->name, $taxonomy); // null - если нет категории
  //$jx->console('Term_exist? ' . json_encode($response, 1));

  // Есть термин?
  if( is_array($response) and !empty($response) )
    return $response;


  /** 1. Добавляем категорию */
  #!empty($category->parentGroup) ? $parent = $category->parentGroup :  $parent = 0;

  /* Если в iiko стоит значение не включать в меню
  if(false === $category->isIncludedInMenu) {
	  return false;
  } */

  $insert_data = wp_insert_term(
    $category->name, // the term
    $taxonomy, // the taxonomy
    array(
      'description'=> $category->description,
      'parent'      => 0,
    )
  );
	if( is_wp_error($insert_data) ) {
		return false;
	}

	$term_id = $insert_data['term_id'];

	//$jx->console('Category: ' . json_encode($category, 0) );
	//$jx->console('$term_id: ' . $term_id );
  /**
   * Add iiko id cat
   * update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
   */
	update_term_meta($term_id, 'iiko-id', $category->id, true);// is cat
	update_term_meta($term_id, 'code', $category->code, true); // ?
	update_term_meta($term_id, 'order', $category->order, true);// sotr number
	update_term_meta($term_id, 'seo-title', $category->seoTitle, true);// seo
	update_term_meta($term_id, 'seoDescription', $category->seoTitle, true);// seo
	update_term_meta($term_id, 'seo-keywords', $category->seoKeywords, true);// seo
	#update_term_meta($term_id, 'seoText', $category->seoKeywords, true);// not using

	apply_filters('iiko_update_term_meta', $term_id, $category);

  if(!empty($category->images)) {
    $img_tag = media_sideload_image( $category->images[0]["imageUrl"], 0, $category->name, "id" );
    if( is_wp_error($img_tag) ){
		console_log("Не удалось загрузить изображение", true);
		return false;
    } else {   // добавлено
      add_term_meta($term_id, 'thumbnail_id', $img_tag, true);
    }
  }
  return $term_id;
}


/**
 * Добавляет или обновляет информацию о товарах в woocommerce
 *
 * @param $product
 * @param $iiko_product_cats
 * @param $iiko_terminal
 * @param $only_price
 *
 * @param $types_for_download
 *
 * @param $img_upload
 *
 * @return mixed
 */
function add_woo_products($product, $iiko_product_cats, $iiko_terminal, $only_price, $types_for_download, $img_upload){

    // Если текущий терминал входит в список, где товар запрещен к продаже.
	if(!empty($product->prohibitedToSaleOn) and is_array($product->prohibitedToSaleOn)){
		foreach ($product->prohibitedToSaleOn as $terminals){
			foreach ($terminals as $terminal){
				//console_log('Исключить? ' . json_encode($term, true ), true);
				if($terminal === $iiko_terminal){
					return false;
				}
			}
		}
	}

    //  Если товар не относится к категории для импорта, пропускаем
	if((isset($product->parentGroup) and $product->parentGroup !== false) && ! in_array($product->parentGroup,
            $iiko_product_cats, true)) {
                return false;
            }

	// Если тип не задан.
	$resul_validate = allow_type_validate($product, $types_for_download);
	//console_log($product->id . ' '. $product->name . ' ' . $product->type . ' + ' . $types_for_download . ' = ' . (int) $resul_validate, true);
	if(false == $resul_validate) {
		return false;
	}


	// Если установлена установлена определенная цена для определенного терминала.
	if( !empty($product->differentPricesOn) and is_array($product->differentPricesOn) ){
		foreach ($product->differentPricesOn as $dif) {
			//jx()->console("dif->price: " . json_encode($dif->price));
			if ( get_option( 'iiko_terminal' ) === $dif->terminalId ) {
				$product->price = $dif->price;
			}
		}
	}

    //  Товар с таким именем есть?
	$post_id = wc_get_product_id_by_sku( $sku = $product->code );// Проверка по артикулу.
	if(0 !== $post_id){ // Товар есть, обновляем цену.

		if($product->isDeleted === true){ // Если товар помечен как удаленный. Меняем статус
			// $status -  The post status publish|pending|draft|private|static|object|attachment|inherit|future|trash.
			$post = array( 'ID' => $post_id, 'post_status' => 'draft' );
			wp_update_post($post);
		}

		iiko_update_product_meta($post_id, $product, $only_price);

		if($img_upload === 'no') {
			$attach_id = get_post_meta( $post_id, '_thumbnail_id', true );
			wp_delete_attachment( $attach_id, $force_delete = false );
			iiko_add_product_image( $product, $post_id ); // Обновляем картинку
		}

		console_log( "Товар $product->name ($post_id) есть, обновили цену.. " );
		return true;

	}
	else { //  Товара нет. Добавляем товар
		if(!empty($product->additionalInfo)){
			$post_excerpt = $product->additionalInfo;
		} else {
			$post_excerpt = '';
		}

		$post = array(
			#'ID'          => ,
			'post_author'  => $user_id = get_current_user_id(),
			'post_content' => $product->description,
			'post_excerpt' => $post_excerpt,
			'post_status'  => "publish",
			'post_title'   => $product->name,
			'post_parent'  => 0,
			'post_type'    => "product",
			# 'meta_input'     => array( 'meta_key'=>'meta_value' ),
		);

		$post_id = wp_insert_post( $post, $wp_error = true );
		console_log( "Товара нет. Добавляем товар id: $post_id " );
		if ( empty($post_id) ) {// Ошибка при создании поста
			console_log( "Ошибка при создании поста $post_id", true );
			return $post_id->get_error_message();
		}

		// Устанавливаем категорию товара (необходимо для успешного создания)
		foreach ( $iiko_product_cats as $id_woo => $id_iiko ) {
			if ( $id_iiko === $product->parentGroup ) {
				$response = wp_set_object_terms( $post_id, (int) $id_woo, 'product_cat' );
				/*if ( is_wp_error($response) ) {
				   console_log( "Ошибка: " . $response->get_error_message() );
				   return $response->get_error_message();
				} else {
					console_log( "Установлена категория товара: " . json_encode( $response, JSON_UNESCAPED_UNICODE ) );
				}*/
			}
		}

		iiko_update_product_meta( $post_id, $product, $only_price );

		update_post_meta( $post_id, "iiko-id", $product->id );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_product_attributes', array() );

		if($img_upload === 'no') { // Добавляем изображения
			iiko_add_product_image( $product, $post_id );
		}

		/** Add old WC version support */
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version = '3.4', "<=" ) ) {
			update_post_meta( $post_id, 'total_sales', '0' );
		}

		/**
		 * Поддержка обновления данных метаполей товара. Предназначено для использования в темах.
		 *
		 * $post_id - id создаваемого товара
		 * $product - данные о товаре из iiko
		 */
		do_action( 'iiko_update_product_meta', $post_id, $product );

		console_log( "Все поля обновлены" );
		return true;
	}
}



/**
 * Устанавливаем миниатюру товара и дополнительные изображения
 *
 * @param $product - товар из iiko
 * @param $post_id - id товара, который был создан
 * @param array $gallery - массив для записи доп. изображений товара
 * @return mixed - ничего не возвращает. В случае ошибки поля не будут обновлены
 */
function iiko_add_product_image($product, $post_id, $gallery = array() ){
  if( empty($product->images) or !is_array($product->images) )
    return false;

  #console_log( "Устанавливаем миниатюру товара" );
  $count = $result = 0;
  krsort ( $product->images);
  foreach ($product->images as $image){
    $img = media_sideload_image( $image->imageUrl, $post_id, $product->name, "id"); // return id or wp_error
    if( is_wp_error($img) ) {
      console_log("Не удалось загрузить изображение");
      return null;
    }

    if($count > 0)
      $gallery[] = $img;
    else
      $result = add_post_meta($post_id, '_thumbnail_id', $img);

    if($result == true ){

      /** Add support theme header settings */
      $theme = wp_get_theme(get_template());
      if($theme->get( 'Name' ) === "resca" ){
        update_post_meta( $post_id, 'thim_mtb_top_image', $img );
       # update_post_meta( $post_id, 'thim_mtb_bg_color', '#210d0c' );
       # update_post_meta( $post_id, 'thim_mtb_text_color', '#fff' );
        update_post_meta( $post_id, 'thim_mtb_using_custom_heading', '1' );
      }

    }
      #console_log( "Для товара $product->name Установлена миниатюра. Изображение id: $img " );
    $count++;
  }

  if( !empty($gallery) ) {
    #error_log("Gallery add_product_image: " . $gallery);
    console_log( "Для товара $product->name Установлены дополнительные изображения, их id: " . json_encode($gallery) );
    if(is_array($gallery))
      $result = update_post_meta($post_id, '_product_image_gallery', implode(",", $gallery));
    else
      $result = update_post_meta($post_id, '_product_image_gallery', $gallery);
  }

}

/**
 * Обновляем мета поля товара
 *
 * @param $post_id
 *
 * @param $product
 * @param $only_price
 *
 * @return bool
 */
function iiko_update_product_meta($post_id, $product, $only_price){

	update_post_meta( $post_id, '_regular_price', $product->price );
	update_post_meta( $post_id, '_price', $product->price );

	if( $only_price === 'yes' ) {
		return true;
	}

	// видимоть товара в меню
	if($product->isIncludedInMenu === false){
		wp_update_post(array( 'ID' => $post_id, 'post_status' => 'draft' ));
	}

	// Теги
	if(!empty($product->tags) and is_array($product->tags)) {
		wp_set_object_terms( $post_id, $product->tags, 'product_tag' );
	}

	if(!empty($product->weight)) update_post_meta( $post_id, '_weight', $product->weight );
	if(!empty($product->code)) update_post_meta($post_id, '_sku', $product->code);

	update_post_meta( $post_id, 'post_content', $product->description );

	if(!empty($product->carbohydrateAmount))
		update_post_meta( $post_id, 'carbohydrateAmount', $product->carbohydrateAmount );

	if(!empty($product->carbohydrateAmount))
		update_post_meta( $post_id, 'energyAmount', $product->energyAmount );

	if(!empty($product->fatAmount))
		update_post_meta( $post_id, 'fatAmount', $product->energyAmount );

	if(!empty($product->fiberAmount))
		update_post_meta( $post_id, 'fatAmount', $product->fiberAmount );

	if(!empty($product->carbohydrateFullAmount))
		update_post_meta( $post_id, 'fatAmount', $product->carbohydrateFullAmount );

	if(!empty($product->energyFullAmount))
		update_post_meta( $post_id, 'energyFullAmount', $product->energyFullAmount );

	if(!empty($product->fatFullAmount))
		update_post_meta( $post_id, 'fatFullAmount', $product->fatFullAmount );

	if(!empty($product->fiberFullAmount))
		update_post_meta( $post_id, 'fiberFullAmount', $product->fiberFullAmount );

}


/**
 * Проверка типа товара на соответствие настройкам
 *
 * @param $product
 * @param $types_for_download - dish, good, modifier
 *
 * @return bool
 */
function allow_type_validate($product, $types_for_download){
	// Относится ли товар к разрешенному типу
	if($types_for_download === 'all')
			return true;
	elseif ($types_for_download === 'dish' and $product->type === 'dish')
		return true;
	elseif ($types_for_download === 'good' and $product->type === 'good')
		return true;
	elseif ($types_for_download === 'modifier' and $product->type === 'modifier')
		return true;
	elseif ($types_for_download === 'dish_and_good' and
	        ($product->type === 'good' or $product->type === 'dish'))
		return true;
	else
		return false;
}