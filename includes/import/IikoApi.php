<?php
/**
 * Функции для работы с API
 */

/**
 * Class IIko Api
 */
class IikoApi
{

  public static $iiko = array();

  /**
   * Получить настройки для соединения и получения данных.
   *
   * @return array
   */
  public static function get_settings()
  {
    !empty(get_option('iiko_serv')) ? static::$iiko['serv'] = get_option('iiko_serv') . 'api/0/' : static::$iiko['serv'] = false;
    !empty(get_option('iiko_login')) ? static::$iiko['login'] = get_option('iiko_login') : static::$iiko['login'] = false;
    !empty(get_option('iiko_password')) ? static::$iiko['pass'] = get_option('iiko_password') : static::$iiko['pass'] = false;
    /** Connection settings */
    !empty(get_option('iiko_rest_id')) ? static::$iiko['iiko_rest_id'] = get_option('iiko_rest_id') : static::$iiko['iiko_rest_id'] = false;

    !empty(get_option('iiko_terminal')) ? static::$iiko['iiko_terminal'] = get_option('iiko_terminal') : static::$iiko['iiko_terminal'] = false;
    /** Cats */
    !empty(get_option('iiko_product_cats')) ? static::$iiko['iiko_product_cats'] = get_option('iiko_product_cats') : static::$iiko['iiko_product_cats'] = false;

    return static::$iiko;
  }


  /**
   * Получаем ключ для использования АПИ
   *
   * https://{server}:{port}/api/0/{operation}?{parametr1}={value1}&{parametrN}={valueN}
   * @param string $transient
   * @param int $depth - задержка в секундах
   * @return string - Маркер доступа(token) апи логина, используемый для авторизации в службах iikoBiz
   */
  public static function get_token( $transient = 'token', $depth = 0 )
  {
    self::get_settings();

    if(!static::$iiko['serv'] or !static::$iiko['login'] or !static::$iiko['pass'])
      return false;

    $link = static::$iiko['serv'] . '/auth/access_token?user_id=' . static::$iiko['login'] . '&user_secret=' . static::$iiko['pass'];
    $token = get_transient($transient);

    if (false === $token) {
      $token = static::get_remote_html(['remote_url' => $link, 'method' => 'get', 'args' => ['timeout' => 60]]);
      if (false !== $token) {
          set_transient($transient, $token, 14 * MINUTE_IN_SECONDS);
          console_log(sprintf(__('Transient %s success set ', 'iiko'), $transient) . $token, false);
      } else {
          console_log( __('Ключ iiko api не был получен, проверьте корректность настроек', 'iiko'), true);
      }
    }

    return $token;
  }


  /**
   * Use API request to list organization
   *
   * @param string $transient
   * @return array|mixed|null|object
   */
  public static function getOrganizationList( $transient = 'OrganizationList' )
  {
    static::$iiko['token'] = static::get_token();

    if(false === static::$iiko['token']){
        return false;
    }

    $organizations = get_transient($transient);
    if (false === $organizations) {

      if(!static::$iiko['serv'] or !static::$iiko['token'])
        return false;

      $link = static::$iiko['serv'] . 'organization/list?access_token=' . static::$iiko['token'];
      $organizations = self::get_remote_html(array('remote_url' => $link, 'method' => 'get'));
      if ( $organizations !== false and !is_wp_error($organizations) ) {
        set_transient($transient, $organizations, 1200 * MINUTE_IN_SECONDS );
        console_log( sprintf(__('Transient %s success set ', 'iiko'), $transient) );
      }
    }
    return $organizations;
  }

  /**
   * Get delivery Terminal
   *
   * $link - /api/0/deliverySettings/getDeliveryTerminals?access_token={accessToken}&organization={organizationId}
   *
   * @param string $transient
   * @return mixed
   */
  public static function getDeliveryTerminal( $transient = 'DeliveryTerminal' )
  {
    $terminals = get_transient($transient);

    if (!empty($terminals) || false !== $terminals) {
      return $terminals;
    }

    static::$iiko['token'] = static::get_token();
    if (!static::$iiko['serv'] or !static::$iiko['token'] or !static::$iiko['iiko_rest_id']) {
        return false;
    }

    console_log('Transient '. $transient .' not set.');

    /** @var  $link - /api/0/deliverySettings/getDeliveryTerminals?access_token={accessToken}&organization={organizationId} */
    $link = static::$iiko['serv'] . 'deliverySettings/getDeliveryTerminals?access_token=' . static::$iiko['token'] .
      '&organization=' . static::$iiko['iiko_rest_id'];

    console_log('Call link: ' . $link , true);

    $terminals = self::get_remote_html(array('remote_url' => $link, 'method' => 'get'));

      if (is_array($terminals)) {
        set_transient($transient = 'DeliveryTerminal', $terminals['deliveryTerminals'], 90 * DAY_IN_SECONDS);
        return $terminals['deliveryTerminals'];
      }

      if ($terminals == '701') { // terminal
          return __('RabbitMq queue is not found. Call <a href="https://iiko.ru/support">iiko support</a> for help', 'iiko');
      }

      return $terminals;
  }

  /**
   * Получение типов заказа
   *
   * Id - Идентификатор типа заказа
   * Name(String) - Наименование тапа заказа
   * OrderServiceType (String) - Сервисный тип заказа
   *
   * @param string $transient
   * @return string
   */
  public static function getOrderTypes($transient = 'OrderTypes')
  {

    $delivery_types = get_transient($transient);
    if (false !== $delivery_types) {
      return $delivery_types;
    }

    static::$iiko['token'] = static::get_token();
    if( ! self::$iiko['serv'] or ! self::$iiko['token'] or ! self::$iiko['iiko_rest_id'] ) {
      return false;
    }

    $link = self::$iiko['serv'] . 'rmsSettings/getOrderTypes?access_token=' . self::$iiko['token'] .
            '&organization=' . self::$iiko['iiko_rest_id'];

    $response_delivery_types = self::get_remote_html(array('remote_url' => $link, 'method' => 'get'));
    $delivery_types = json_decode(json_encode($response_delivery_types["items"]));

    if (!empty($delivery_types)) {
      console_log( sprintf(__('Transient %s success set ', 'iiko'), json_encode($delivery_types, JSON_UNESCAPED_UNICODE)));
      set_transient($transient, $delivery_types, 3 * MONTH_IN_SECONDS );// temp method
    }

  }

  /**
   * Получить типы оплаты для ресторана
   *
   * @param string $transient
   * @return array|mixed|null|object
   */
  public static function getPaymentTypes( $transient = 'PaymentTypes' )
  {
    $pay_types = get_transient($transient);
    if (false !== $pay_types) {
      return $pay_types;
    }

    static::$iiko['token'] = static::get_token();
    if( ! self::$iiko['serv'] or ! self::$iiko['token'] or ! self::$iiko['iiko_rest_id'] ) {
      return false;
    }

    $link =
        self::$iiko['serv'] . 'rmsSettings/getpaymentTypes?access_token=' .
        self::$iiko['token'] . '&organization=' . self::$iiko['iiko_rest_id'];

      $response_pay_types = self::get_remote_html(array('remote_url' => $link, 'method' => 'get'));
      $pay_types = json_decode(json_encode($response_pay_types["paymentTypes"]));

      if (!empty($pay_types)) {
        console_log( sprintf(__('Transient %s success set ', 'iiko'), json_encode($pay_types)), true);
        set_transient($transient, $pay_types, 3 * MONTH_IN_SECONDS);// temp method
      }
  }


  /**
   * Получить список номенклатуры
   *
   * Ответ
   * groups Group[] Группы
   * products Product[] Продукты
   * revision long Ревизия (одна на все дерево продуктов)
   * productCategories ProductCategory[] Группы продуктов
   * uploadDate string Дата последнего обновления меню в формате "yyyy-MM-dd HH:mm:ss"
   *
   * @param string $transient
   *
   * @return array|bool
   */
  public static function getNomenclature( $transient = 'Nomenclature' )
  {
    $products = get_transient($transient);
    if (false === $products) {
      static::$iiko['token'] = static::get_token();
      if( ! self::$iiko['serv'] or ! self::$iiko['token'] or ! self::$iiko['iiko_rest_id'] ) {
        return false;
      }

      $link = self::$iiko['serv'] . 'nomenclature/' . self::$iiko['iiko_rest_id'] . '?access_token=' . self::$iiko['token'];
      
      $products = self::get_remote_html(array('remote_url' => $link, 'method' => 'get'));
      if(empty($products))
        return false;

      console_log( sprintf(__('Transient %s success set ', 'iiko'), $transient) ); #. json_encode($products));
      set_transient($transient, $products,  HOUR_IN_SECONDS);// temp method

    }

    return $products;
  }


  /**
   * Журнал событий - Получить журнал событий
   *
   * @param $iiko
   * @return array|mixed|null|object - Lf

  public static function getJournal($iiko)
  {
    # /api/0/events/events?access_token={accessToken}&request_timeout={requestTimeout}
    $link = $iiko['serv'] . "events/events?access_token={$iiko['token']}";
    $terminal = IikoApi::get_remote_html(
      array('remote_url' => $link, 'method' => 'post', 'args' => array('body' => array('eventsRequest' => '')))
    );
    return $terminal;
  }
   * */

    /**
     * Получаем ответ по API
     *
     * @param null $atts
     *
     * @return array|mixed|null|object
     */
  public static function get_remote_html($atts)
  {
    $remote_url = $args = $transient_name = $method = $args = $response = $body = null;
    extract(shortcode_atts(array(
      'remote_url' => '',
      'transient_name' => '',
      'method' => 'get',
      'args' => array(
        /* 'timeout'     => 60,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array( 'Content-Type: application/json; charset=utf-8'),
        'body'        => null, // параметры запроса в массиве
        'cookies'     => array()
        */
      ),
    ), $atts));

    if ($atts['method'] === 'get')
      $response = wp_remote_get($remote_url, $args); // Получаем HTML
    elseif ($atts['method'] === 'post')
      $response = wp_remote_post($remote_url, $args); // Получаем HTML
    else
      wp_die(__('error in method', 'iiko'));

    if ( is_wp_error($response) or $remote_url == '' ) { // Если ответа сервера нет:

      console_log(__('Response error: ', 'iiko') . $response->get_error_message(), true);


    } elseif ( wp_remote_retrieve_response_code($response) === 200 ) { // OK

      console_log('Response: ' . json_encode($response, JSON_UNESCAPED_UNICODE), true);

      $html = wp_remote_retrieve_body($response); // Получим тело
      $body = json_decode($html, true); // Преобразуем в объект

    } else { // BAD
      console_log(__('Response bad', 'iiko') .': '. json_encode($response, JSON_UNESCAPED_UNICODE), true);
	    $body = json_decode(wp_remote_retrieve_body($response), true);
	    if($body['code'] == '701'){
		    return $body['code'];
	    }
      $body = false;
    }
    return $body;
  }

  /**
   * Отображение всех транзитов, используется для отладки.
   *
   * @return mixed
   */
  public static function getAllTransient(){

    $data['token'] = get_transient('token');
    $data['OrganizationList'] = get_transient('OrganizationList');
    $data['DeliveryTerminal'] = get_transient('DeliveryTerminal');
    $data['OrderTypes'] = get_transient('OrderTypes');
    $data['PaymentTypes'] = get_transient('PaymentTypes');
    $data['Nomenclature'] = get_transient('Nomenclature');

    return $data;
  }


  /**
   * Отправка информации о заказе в Iiko
   *
   * @param $order
   * @param $order_items
   * @return array|mixed|null|object
   */
    public static function sendOrder($order, $order_items){

        self::$iiko['token']           = self::get_token();
        self::$iiko['request_timeout'] = '00:01:00';

        /** @var  $link - /api/0/orders/add?access_token={accessToken}&request_timeout={requestTimeout} */
        $link =
            self::$iiko['serv'] . 'orders/add?access_token=' .
            self::$iiko['token'] . '&request_timeout=' .
            self::$iiko['request_timeout'];

        # console_log("Order link: $link");
        $order_encoded = order_prepare($order, $order_items, self::$iiko );
        # $order_encoded2 =  '{"order":{"address":{"home":"","apartment":"606","street":"Проспект Героев 27\/1, под 7","city":"Санкт-Петербург"},"items":[{"amount":"1","name":"Пицца 4 сыра","id":"823863e5-96e4-4ade-ac4f-fd31da753ba5"},{"amount":"1","name":"Пицца пепперони","id":"48aa167e-e505-4b0f-94a2-006856df9d3f"}],"externalId":"8485","comment":null,"phone":"89811798900","id":"04edc087-4127-f731-b29b-e5d1c62cd505","fullSum":1200,"date":"2018-11-04 23:08:18"},"customer":{"name":"Кристина ","phone":"89811798900","id":"ff57e3f8-afb5-c0cd-66ed-700f641e18a6"},"organization":"18b5089b-01bf-11e6-80c8-d8d385655247","restaurantId":"18b5089b-01bf-11e6-80c8-d8d385655247","deliveryTerminalId":"2330baf9-e0b0-bd49-0151-491c39c00145","isSelfService":false}';

        $request = array(
            'remote_url' => $link,
            'method' => 'post',
            'args' => array(
                'timeout' => 60,
                'httpversion' => '1.0',
                'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
                'body' => $order_encoded,
                'method' => 'POST',
                'data_format' => 'body',
                #'stream' => true,
                #'filename' => __DIR__ .'/iiko.log',
            )
        );

        $request = self::send_order($request);

        return $request ;
    }

    private static function send_order($array){
        return self::get_remote_html($array);
    }


	/**
	 * Отправляет информацию о клиенте, если клиента нет создает запись о нем
	 * @link /api/0/customers/create_or_update?access_token={accessToken}&organization={organizationId}
	 * @param $CustomerForImport
	 *
	 * @return array|mixed|null|object
	 */
	public static function CreateGuest( IikoCustomer $CustomerForImport ){
		self::$iiko['token'] = self::get_token();
		$request_url         = self::$iiko['serv'] .
                               'customers/create_or_update?access_token=' . self::$iiko['token'] .
                               '&organization=' . self::$iiko['iiko_rest_id'];

		$response = self::get_remote_html(
			array(
				'remote_url' => $request_url,
				'method' => 'post',
				'args' => array(
					'timeout' => 60,
					'httpversion' => '1.0',
					'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
					'body' => $CustomerForImport,
					'method' => 'POST',
					'data_format' => 'body'
				)
			)
		);

		return $response; // Guid
	}


	/**
	 * @unused
	 *
	 */
	public function get_deliveryDiscounts() {
		$request_url = self::$iiko['serv'] . 'deliverySettings/deliveryDiscounts?access_token=' .
                       self::$iiko['token'] . '&organization=' . self::$iiko['iiko_rest_id'];

		$response = self::get_remote_html(
			array(
				'remote_url' => $request_url,
				'method' => 'get',
			)
		);

	}

}