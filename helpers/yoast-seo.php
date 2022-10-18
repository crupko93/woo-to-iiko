<?php
/**
 * Пример работы с метаполями
 */



/**
 * Обнолвяем поля товара данными из iiko
 *
 * @param $post_id - id создаваемого товара
 * @param $product - объект товара из iiko
 */
function update_iiko_product_fields( $post_id, $product ){
	if(!empty($product->seoTitle))
		update_post_meta( $post_id, "seo-title", $product->seoTitle );
	if(!empty($product->seoDescription))
		update_post_meta( $post_id, "seo-description", $product->seoDescription );
	if(!empty($product->seoKeywords))
		update_post_meta( $post_id, "seo-keywords", $product->seoKeywords );
}
add_action('iiko_update_product_meta', 'update_iiko_product_fields', 10, 2 );

/** Получаем title */
function get_seo_title( $post_id, $product ) {
	get_post_meta( $post_id, 'seo-title', $product->seoTitle );
}

/** Получаем description */
function get_seo_description( $post_id, $product ) {
	 get_post_meta( $post_id, 'seo-description', $product->seoDescription );
}

/** Получаем keywords */
function get_seo_keywords( $post_id, $product ) {
   get_post_meta( $post_id, 'seo-keywords', $product->seoKeywords );
}

/**
 * Регистрируем произвольные поля для Yoast SEO

function iiko_yoast_variables() {
  wpseo_register_var_replacement( '%%seo-title%%', 'get_seo_title', 'advanced', __('iiko seo title', 'iiko') );
  wpseo_register_var_replacement( '%%seo-desc%%', 'get_seo_description', 'advanced', __('iiko seo description', 'iiko') );
  wpseo_register_var_replacement( '%%seo-keywords%%', 'get_seo_keywords', 'advanced', __('iiko seo description', 'iiko') );
}
add_action('wpseo_register_extra_replacements', 'iiko_yoast_variables');
 *  */

/** Add additional action */
add_action('get_seo_keywords', 'get_seo_keywords');
add_action('get_seo_description', 'get_seo_description');
add_action('get_seo_title', 'get_seo_title');