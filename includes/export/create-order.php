<?php
/**
 * Экспорт заказов в iiko
 */


/*add_action( 'woocommerce_view_order', 'woo_order_details_table', 11 );
function woo_order_details_table($order_id){
    if ( ! $order_id ) { return; }

    wc_get_template(
        'order/order-details.php',
        array(
            'order_id' => $order_id,
        )
    );
}*/


add_action('woocommerce_thankyou', 'iiko_order_action', 10, 1);
//add_action('woocommerce_resume_order', 'iiko_order_action', 10, 1);
function iiko_order_action($order_id)
{
    if (!$order_id){
        return;
    }

    // Getting an instance of the order object
    $order = wc_get_order($order_id);
    $order_items = array();

    if ($order->is_paid()) {
        $paid = 'yes';
    } else {
        $paid = 'no';
    }



    foreach ($order->get_items() as $item_id => $item) {
        if ($item['variation_id'] > 0) {
          $product_id = $item['variation_id']; // variable product
        } else {
          $product_id = $item['product_id']; // simple product
          # $product = $item->get_product();
        }
        // Get the product object
        $product = wc_get_product($product_id);

        $order_items[] = array(
          'id' => get_post_meta($product_id, 'iiko-id', true), // Идентификатор продукта
          'code' => $product->get_sku(), // Артикул товара
          'name' => $product->get_name(), /** Название продукта */
          'amount' => $item->get_quantity(), /** Количество */
          'sum' => $product->get_price(), // Стоимость
          # 'modifiers' => array("OrderItemModifier" => array('')),
          'comment' => '', // Комментарий 225
          # 'guestId' => '', // Идентификатор гостя
          #'comboInformation' => array("ComboItemInformation");
        );
    }

    //echo '<h4>' . esc_html__('Delivery method', 'iiko') .': '. get_delivery_name($order) . '</h4><br>';

   /*
    $checkout = WC()->checkout;
    $checkout->set_value('delivery_method');
    echo '<pre>';
    var_dump($response =get_option('iiko_response'));
    echo '</pre>';
   */

  do_action('create_iiko_order', $order, $order_items);
}



add_action('create_iiko_order', 'create_order', 10, 2);
/**
 * Создание заказа
 *
 * @param $order
 * @param $order_items
 */
function create_order($order, $order_items)
{
    /* Отправка данных в iiko */
   $response = IikoApi::sendOrder($order, $order_items);

	if (isset($response['status']) && $response['status'] === 'Новая') {
        $order->update_status('completed', __('Order transferred to the delivery service. Expect courier. ', 'iiko') . $response['deliveryDate'], false);
        update_post_meta($order->get_id(), '_billing_date_time', $response['deliveryDate']); // обновим время доставки заказа
	} elseif (isset($response['status']) && $response['status'] === 'Не подтверждена'){
        /** Обновим статус */
        $order->update_status('processing', __('Order transferred to manager. Expect call.', 'iiko') . $response['deliveryDate'], false);
        /** обновим время доставки заказа */
        update_post_meta($order->get_id(), '_billing_date_time', $response['deliveryDate']);
    } else {
        $message_text =  sprintf( __( 'The order %s was not passed to the delivery service. Please contact our manager!', 'iiko' ), $order->get_order_number() );
        $order->update_status('failed',$message_text, false);
	}
    console_log($response);
}

/**
 * Подготовим заказ в формат iiko
 *
 * @param WC_Order $order
 * @param $order_items
 * @param array $iiko
 *
 * @return string - json encoded respond
 */
function order_prepare(WC_Order $order, $order_items, $iiko )
{

  /** @var  $Customer - Информация о заказчике */
  $Customer = array(
    'id' => getGUID(), // Guid Идентификатор
    'name' => $order->get_billing_first_name(), /** string   Имя   */
    //  "middleName" => '', // Отчество
    'surName' => $order->get_billing_last_name(), //Фамилия
    'phone' => $order->get_billing_phone(), /** Телефонный номер.   Регулярное выражение, которому должен соответствовать телефон.   ^(8|\+?\d{1,3})?[ -]?\(?(\d{3})\)?[ -]?(\d{3})[ -]?(\d{2})[ -]?(\d{2})$  */
    'email' => $order->get_billing_email(), // email
    # "nick" => !empty($current_user) ? $current_user->user_login : '' , // Никнэйм
    #  "shouldReceivePromoActionsInfo" => '',
    # "sex" => '', // Пол:  NotSpecified = 0,  Male = 1,  Female = 2. Для входящих запросов передавать 0,1 или 2.
    # "imageId" => '', // Идентификатор изображения пользователя
    #  "customProperties" => '',
    #  "publicCustomProperties" => '',
    #  "balance" => '',
    #  "isBlocked" => false,
    #  "additionalPhones" => array("CustomerPhone" => array('')),
    #  "addresses" => '',
    #  "cards" => array("GuestCardInfo" => array())
  );

  /** @var  $Address - Адрес доставки заказа */
  $Address = array(
    'city' => get_option('iiko_city'),
    'street' => get_post_meta($order->get_id(), '_billing_street_iiko_name', true), #apply_filters("filter_address", get_post_meta($order->get_id(),"_billing_street", true)),
    'streetClassifierId' => get_post_meta($order->get_id(), '_billing_street_id', true),
    'home' => get_post_meta($order->get_id(), '_billing_home', true),
    'housing' => get_post_meta($order->get_id(), '_billing_housing', true),
    'apartment' => get_post_meta($order->get_id(), '_billing_apartment', true), // Квартира - 10
    'entrance' => get_post_meta($order->get_id(), '_billing_entrance', true), // Подъезд
    /* "floor"     => '', // Этаж - 10
     'doorphone' => '', // Домофон - 10
    */
    'comment' => get_post_meta($order->get_id(), '_billing_address_1', true), // Дополнительная информация - 500
  );

  $PaymentItem = array(
    'sum' => $order->get_total(), //decimal  Сумма к оплате
    'paymenttype' => change_pay_type(get_post_meta($order->get_id(), '_billing_delivery_type', true)),//  PaymentType  Тип оплаты (одно из полей: id, code является обязательным)
    'isProcessedExternally' => false, // bool - Является ли позиция оплаты проведенной
    'isPreliminary' => false, // bool - Является ли позиция оплаты предварительной
    'isExternal' => false, //  bool  Принята ли позиция оплаты извне
    'additionalData' => change_delivery_type( get_post_meta( $order->get_id(), '_billing_delivery_type', true)), // string - Дополнительная информация
  );

  /** @var  $OrderItem - информация о составе заказа */
  $OrderItem = $order_items;
  /**  Дата и время.   Строка в формате “YYYY-MM-DD hh:mm:ss”   */
  $DateTime = $order->get_date_created()->date_i18n('Y-m-d H:i:s'); // Дата и время создания заказа. Текщее время

  if ('1' === get_post_meta($order->get_id(), '_billing_time_choose', true)) {
    $date = strtotime(get_post_meta($order->get_id(), '_billing_date_time', true) . ':00'); // // Дата и время доставки. переводит из строки в дату
    $date = date('Y-m-d H:i:s', $date); // переводит в iiko формат
  } else {
      $date = null; // Ближайшее возможное
  }

  if ( '0' !== get_post_meta($order->get_id(), '_billing_delivery_type', true)) {
      $isSelfService = true;
  } else {
      $isSelfService = false;
  }

  /** @var  $Order - Информация о заказе */
  $Order = array(
    'id' => getGUID(), //   Guid идентификатор заказа
    'externalId' => $order->get_id(), // string Идентификатор заказа – должен быть уникальным в рамках данной организации
    'date' => $date, /**  date('Y.m.d H:i', strtotime($date) ) Дата выполнения заказа, если задан null, то система подставит время как текущее + продолжительность доставки из “График работы и картография”     */
    'items' => $OrderItem, /** */
    'payments' => $PaymentItem,
    'phone' => $order->get_billing_phone(), /**  $order->get_billing_phone() Контактный телефон. Регулярное выражение, которому должен соответствовать телефон: ^(8|\+?\d{1,3})?[ -]?\(?(\d{3})\)?[ -]?(\d{3})[ -]?(\d{2})[ -]?(\d{2})$ */
    'isSelfService' => $isSelfService, // bool - Признак доставки самовывозом
    'orderTypeId' => change_delivery_type(get_post_meta($order->get_id(), '_billing_delivery_type', true)), //  Guid - Идентификатор типа заказа. Получается методом  Получение списка допустимых типов заказов https://docs.google.com/document/d/1kuhs94UV_0oUkI2CI3uOsNo_dydmh9Q0MFoDWmhzwxc/edit#bookmark=id.otkoysintiqs
    'address' => $Address,
    'comment' => !empty($order->get_customer_note()) ? $order->get_customer_note() : __('Заказ с сайта ', 'iiko') . get_site_url(), // Комментарий к заказу - 500
    /* 'conception' => '', // Концепция
     'personsCount' => '', // Количество персон */
    'fullSum' => $order->get_total(), /**   Decimal    Сумма заказа     */
    # 'marketingSource' => '', // string Маркетинговый источник (реклама). Можно указывать не более одного источника. Пример: deliveryMarket.ru
    # 'marketingSourceId' => '', // Guid Идентификатор маркетингового источника
    # 'discountCardTypeId'=> '', // Guid Идентификатор скидки для заказа. Получается методом Получить список скидок, доступных для применения в доставке для заданного ресторана
    # 'discountCardSlip'  => '', // string Трек скидочной карты, которую надо применить к заказу. Если указан одновременно с discountCardTypeId, то будет применятся скидка по discountCardTypeId.
    # 'discountOrIncreaseSum' => '', // decimal Сумма скидки. Необходима только для скидок со свободной суммой.
    # 'orderCombos' => array("DeliveryOrderCombo" => array('')), // Массив комбо-блюд, включенных в заказ.
  );

  /** @var  $OrderRequest - Тело основного запроса на доставку */
  $OrderRequest = array(
    'organization' => $iiko['iiko_rest_id'], // Идентификатор ресторана, список доступных ресторанов можно получить при помощи функции Получение списка организаций
    'deliveryTerminalId' => select_delivery_terminal($order, $iiko), // Идентификатор доставочного термина, на который нужно отправить заказ. Используется ТОЛЬКО в том случае когда не активирована функция автораспределния заказов и когда нет (физически) операторов коллцентра, которые могут обработать заказ
    'customer' => $Customer, // Заказчик
    'order' => $Order, // Заказ
    /* "coupon" => '', // Номер купона, который применяется к заказу.
       "availablePaymentMarketingCampaignIds" => array("Guid" => array('')), // Массив идентификаторов применяемых акций, содержащих Действия оплаты. Если действия оплаты не используются, то массив должен быть пустым.
       "applicableManualConditions" => array("Guid" => array('')), // Массив идентификаторов ручных условий, которые применяются к заказу.
       "customData" => '', // Служебная информация. Только хранится, доступна через API, на UI не выводится
     */
  );

  //console_log('Request to iiko: ');
  //console_log($OrderRequest);

  return json_encode($OrderRequest);
}


/**
 * Generates GUID
 *
 * @return string
 */
function getGUID()
{
  if (function_exists('com_create_guid')) {
    return com_create_guid();
  }

    mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
    $charId = md5(uniqid(rand(), true));
    $hyphen = chr(45);// "-"
    $uuid   = substr($charId, 0, 8) . $hyphen
              . substr($charId, 8, 4) . $hyphen
              . substr($charId, 12, 4) . $hyphen
              . substr($charId, 16, 4) . $hyphen
              . substr($charId, 20, 12);

    return $uuid;
}

function select_delivery_terminal( $order, $iiko ){
	$end_terminal = $iiko['iiko_terminal'];

	if( false === get_option('show_delivery_terminal') or 'no' === get_option('show_delivery_terminal')) {
		return $end_terminal;
	}

	$end_terminal = get_post_meta($order->get_id(), '_billing_delivery_terminal', true);

	return $end_terminal;
}


/**
 * Handle a custom 'customvar' query var to get orders with the 'customvar' meta.
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Order_Query.
 * @return array modified $query

function handle_custom_query_var( $query, $query_vars ) {
    if ( ! empty( $query_vars['billing_delivery_type'] ) ) {
        $query['billing_delivery_type'][] = array(
            'key' => 'billing_delivery_type',
            'value' => esc_attr( $query_vars['billing_delivery_type'] ),
        );
    }

    return $query;
}
add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'handle_custom_query_var', 10, 2 );
 *  */