<?php
/**
 * Автообнолвение товаров по wp_cron
 */

if(empty(check_license()) ) {
	return;
}

$hook = 'iiko_cron_auto_update';
$enable_cron = get_option('iiko_cron_auto_update');
if($enable_cron !== 'yes') {
	wp_clear_scheduled_hook($hook);
	return;
}

if ( ! wp_next_scheduled( $hook ) ) { // Проверка существования
	wp_schedule_event( time(), 'hourly', $hook ); // daily, twicedaily, hourly
}



/**
 * PHP Функция обновления товаров
 */
function iiko_cron_auto_update() {

	$products = set_products();

	$iiko_product_cats = get_option ('iiko_product_cats');
	$iiko_terminal = get_option ('iiko_terminal');

    if(!empty(get_option('iiko_types_for_download'))) {
        $types_for_download = get_option('iiko_types_for_download');
    } else {
        $types_for_download = 'all';
    }

    $only_price = get_option('iiko_update_only_price');
	$img_upload = get_option('iiko_no_img_uploads');

	$count = $result = 0;
	foreach ($products as $product){
		$product = json_decode(json_encode($product));
		$result = add_woo_products( $product, $iiko_product_cats, $iiko_terminal, $only_price, $types_for_download, $img_upload );
		if(true === $result){ // Если все ок увеличиваем на 1
			$count++;
		} elseif(false !== $result) { // WP_Error
			console_log( 'При загрузке товара произошла ошибка!' . $result );
		}
	}
	$message = sprintf( __( 'Total uploaded and updated products:<strong>%s</strong>', 'iiko' ), $count );
	console_log($message);
}
add_action( $hook, 'iiko_cron_auto_update' ); // добавляем крон хук