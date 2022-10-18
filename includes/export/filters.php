<?php
/**
 * Functions Additional filters
 */


add_filter('iiko_checkout_fields', 'iiko_checkout_fields', 10 , 1); // null
/**
 * Добавляем поле выбора терминала доставки на странциу оформления заказа
 *
 * @param $fields - массив полей
 * @return array - массив полей с полем выбора терминала доставки
 */
function iiko_checkout_fields($fields){

	if( false === get_option('show_delivery_terminal') or 'no' === get_option('show_delivery_terminal')) {
		return $fields;
	}

	$settings_select = get_option('show_delivery_terminal_address');
	$terminals = get_transient( 'DeliveryTerminal' );

	#	echo '<pre>'; var_dump($terminals); echo '</pre>';
	if ( $terminals !== false and ! empty( $terminals[1] ) ) {
		$selects = array();
		foreach ( $terminals as $terminal ) {
			$terminal                                 = (object) $terminal;

			switch ($settings_select) {
				case 'name':
					$selects[ $terminal->deliveryTerminalId ] = $terminal->name;
					break;
				case 'deliveryRestaurantName':
					$selects[ $terminal->deliveryTerminalId ] = $terminal->deliveryRestaurantName;
					break;
				case 'address':
					$selects[ $terminal->deliveryTerminalId ] = $terminal->address;
					break;
				case 'name_and_address':
					$selects[ $terminal->deliveryTerminalId ] = $terminal->name . ' - ' . $terminal->address;
					break;
			}
		}

		$fields['delivery_terminal'] = array(
			'id'         => 'delivery_terminal',
			'label'       => __( 'Delivery terminal', 'iiko' ),
			'label_class' => array( '' ),
			'type'        => 'select',
			'options'     => $selects,
			'default'     => '0',
			'class'       => array( 'delivery_terminal', 'form-row-wide' ),
			'priority'    => 8,
			'required'    => false,
		);
	}
	return $fields;
}


/**
 * Возвращаем Название типа доставки по коду из селекта
 *
 * @param WC_Order $order
 *
 * @return string - Название способа доставки
 */
function get_delivery_name( WC_Order $order ){

    $name = get_post_meta( $order->get_id(),'_billing_delivery_type', true);

    if ($name === '1') {
        return __('Сам заберу', 'iiko');
    }

    if($name === '0') {
        return __('Доставка курьером', 'iiko');
    }
}
add_filter('delivery_name', 'get_delivery_name', 10, 1);

