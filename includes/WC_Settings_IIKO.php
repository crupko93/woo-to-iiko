<?php
/**
 * WooCommerce IIKO Settings
 * Основные настройки плагина
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}


if ( class_exists( 'WC_Settings_IIKO', false ) ) {
  return new WC_Settings_IIKO();
}

/**
 * WC_Settings_IIKO.
 */
class WC_Settings_IIKO extends WC_Settings_Page {


    public function __construct() {
        $this->id    = 'iiko';
        $this->label = 'Iiko';
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
        add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts') );

        parent::__construct();
  }

  /**
   * Add plugin options tab
   *
   * @return array
   */
  public function add_settings_tab( $settings_tabs ) {
    $settings_tabs[$this->id] = __('Iiko settings', 'iiko' );
    return $settings_tabs;
  }

  /**
   * Get sections
   *
   * @return array
   */
  public function get_sections() {
    $sections = array(
      ''               => __( 'General Settings', 'iiko' ),
      'export'         => __( 'Export Settings', 'iiko' ),
      'checkout_page'  => __( 'Checkout Page Settings', 'iiko' ),
    );
    return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
  }


  /**
   * Get settings
   *
   * @return array
   */
  public function get_settings( $section = null ) {

    switch( $section ){
        case '' :
        $settings = array(
          array(
            'name' => __('Iiko connection settings', 'iiko'),
            'type' => 'title',
            'desc' => __('Setup iiko connection settings for import and export', 'iiko'),
            'id' => 'wc_settings_tab_demo_section_title'
          ),
          array(
            'name' => __('Server iiko', 'iiko'),
            'type' => 'text',
            'default' => 'https://iiko.biz:9900/',
            'desc' => __('Example: https://iiko.biz:9900/', 'iiko'),
            'id' => 'iiko_serv'
          ),
          array(
            'name' => __('Login', 'iiko'),
            'type' => 'text',
            'default' => '',
            'desc' => __('Client login.', 'iiko'),
            'id' => 'iiko_login'
          ),
          array(
            'name' => __('Password', 'iiko'),
            'type' => 'password',
            'default' => '',
            'desc' => __('Client secret', 'iiko'),
            'id' => 'iiko_password'
          ),
          array(
            'name' => __('City', 'iiko'),
            'type' => 'text',
            'default' => __('Санкт-Петербург','iiko'),
            'desc' => __('Default city. Need to use KLADR ', 'iiko'),
            'id' => 'iiko_city'
          ),
          array(
            'name' => __('City ID', 'iiko'),
            'type' => 'text',
            'default' => '7800000000000',
            'desc' => __('City ID in KLADR. Filled automatically after selecting a city.', 'iiko'),
            'id' => 'iiko_city_id'
          ),
          array(
            'title'           => __( 'Enable debug mode', 'woocommerce' ),
            'desc'            => __( 'Enable debug mode for import and export. Or enable all wordpress Debugging, WP_DEBUG = true', 'iiko' ),
            'id'              => 'iiko_debug',
            'default'         => 'no',
            'type'            => 'checkbox',
            'checkboxgroup'   => 'start',
            'show_if_checked' => 'option',
            'desc_tip'        => __( 'See console and php error log.', 'iiko' ),
          ),
          'section_end' => array(
            'type' => 'sectionend',
            'id' => 'wc_settings_tab_demo_section_end'
          ),
          /** TITLE  */
          array(
              'name' => __('Import Settings', 'iiko'),
              'type' => 'title',
              'desc' => __('Additional settings', 'iiko'),
              'id' => 'wc_settings_tab_demo_section_title'
          ),
    /*	      array(
            'title'    => __( 'Placeholder product url', 'iiko' ),
            'desc'     => __( 'Image url for product with not set thumbnail', 'iiko' ),
            'id'       => 'product_placeholder',
            'type'     => 'text',
            'default'  => '',
            'css'      => 'width: 250px;',
            'desc_tip' => true,
          ),*/
          array(
                'title'    => __( 'Update only product price for re-import', 'iiko' ),
                'desc'     => __( 'Update only product price for re-import', 'iiko' ),
                'id'       => 'iiko_update_only_price',
                'type'     => 'checkbox',
                'default'  => 'no',
                'css'      => 'width: 250px;',
                'desc_tip' => true,
          ),
          array(
            'title'    => __( 'Update products automatically', 'iiko' ),
            'desc'     => __( 'Update products automatically every day', 'iiko' ),
            'id'       => 'iiko_cron_auto_update',
            'type'     => 'checkbox',
            'default'  => 'no',
            'css'      => 'width: 250px;',
            'desc_tip' => true,
          ),
          array(
              'title'    => __( 'Do not upload or update images when importing', 'iiko' ),
              'desc'     => __( 'Do not upload or update images when importing', 'iiko' ),
                'id'       => 'iiko_no_img_uploads',
                'type'     => 'checkbox',
                'default'  => 'no',
                'css'      => 'width: 250px;',
                'desc_tip' => true,
          ),
          array(
                'title'    => __( 'Type items for loading', 'iiko' ),
                'desc'     => __( 'Select type items for loading', 'iiko'),
                'desc_tip' => false,
                'id'       => 'iiko_types_for_download',
                'default'  => 'all',
                'type'     => 'select',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width: 350px;',
                'options'  => array(
                    'all'            => __( 'All', 'iiko' ),
                    'dish'           => __( 'Dish', 'iiko' ),
                    'good'           => __( 'Product (Good)', 'iiko' ),
                    'modifier'       => __( 'Modifier', 'iiko' ),
                    'dish_and_good'  => __( 'Product and Dish', 'iiko' )
                ),
          ),
          'section_add_end' => array(
            'type' => 'sectionend',
            'id' => 'wc_settings_add_demo_section_end'
          ),
        );
      break;

    case 'export':
        $settings = array(
            'delivery_title' => array(
                'name'     => __( 'Export Settings', 'iiko' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_delivery_section_title'
            ),
            'delivery_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_delivery_section_end'
            ),
        );
        break;

        case 'checkout_page':
            $settings = array(
                array(
                    'name'     => __( 'Checkout page', 'iiko' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'       => 'checkout_title'
                ),
                'checkout_description' => array(
                    'name'     => __( 'Any text. Displayed on the payment page.', 'iiko' ),
                    'type'     => 'textarea',
                    'desc'     => __( 'Enter any text here to show it on the payment page.', 'iiko' ),
                    'id'       => 'checkout_description',
                    'desc_tip' => true,
                ),
                array(
                    'title'    => __( 'Show the choice of delivery terminal?', 'iiko' ),
                    'desc'     => __( 'Check this if your have several Delivery terminals. And the user must choose one of them. ', 'iiko' ),
                    'id'       => 'show_delivery_terminal',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                ),
                array(
                    'title'    => __( 'Show Name or Address of delivery terminal?', 'iiko' ),
                    'desc'     => __( 'Works only if the option marked above.', 'iiko'),
                    'id'       => 'show_delivery_terminal_address',
                    'default'  => 'all',
                    'type'     => 'select',
                    'class'    => 'wc-enhanced-select',
                    'css'      => 'min-width: 350px;',
                    'desc_tip' => __( 'Select display option. ', 'iiko' ),
                    'options'  => array(
                        'name'                   => __( 'Name', 'iiko' ),
                        'deliveryRestaurantName' => __( 'Restaurant Name', 'iiko' ),
                        'address'                => __( 'Address', 'iiko' ),
                        'name_and_address'       => __( 'Name + Address', 'iiko' )
                    ),
                ),
                array(
                    'title'    => __( 'Specify the mask to enter the phone number', 'iiko' ),
                    'desc'     => __( 'Specify the mask to enter the phone number', 'iiko' ),
                    'id'       => 'tel_mask',
                    'type'     => 'text',
                    'default'  => '8(999) 999-99-99',
                    'css'      => 'width: 250px;',
                    'desc_tip' => false,
                ),
                array(
                    'title'    => __( 'Show calendar expanded', 'iiko' ),
                    'desc'     => __( 'Check if you want to show the calendar immediately in expanded form, and not as a string.', 'iiko' ),
                    'id'       => 'select_time_inline',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                ),
                array(
                    'title'    => __( 'Specify the start time of the restaurant', 'iiko' ),
                    'desc'     => __( 'Specify the start time of the restaurant. For example: 11', 'iiko' ),
                    'id'       => 'rest_time_start',
                    'css'               => 'width:50px;',
                    'default'           => '11',
                    'desc_tip'          => true,
                    'type'              => 'number',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1,
                    ),
                ),
                array(
                    'title'    => __( 'Specify the closing time of the restaurant', 'iiko' ),
                    'desc'     => __( 'Specify the time of the restaurant shutdown. For example: 23', 'iiko' ),
                    'id'       => 'rest_time_end',
                    'css'               => 'width:50px;',
                    'default'           => '23',
                    'desc_tip'          => true,
                    'type'              => 'number',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1,
                    ),
                ),
                array(
                    'title'    => __( 'Number of days to pre-order', 'iiko' ),
                    'desc'     => __( 'The number of days to pre-order. For example: 7', 'iiko' ),
                    'id'       => 'rest_max_day',
                    'css'               => 'width:50px;',
                    'default'           => '7',
                    'desc_tip'          => true,
                    'type'              => 'number',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1,
                    ),
                ),
                array(
                    'title'    => __( 'The number of hours for the preparation and delivery of the order', 'iiko' ),
                    'desc'     =>  __( 'Recommended value: 1 or 0', 'iiko' ),
                    'id'       => 'rest_time_int',
                    'css'               => 'width:50px;',
                    'default'           => '1',
                    'type'              => 'number',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1,
                    ),
                    'desc_tip' => __( 'The number of hours required for the preparation and delivery of the order received in advance,
             since the opening of the restaurant. For example, if the restaurant opens at 11,
             stand at 1, then the delivery will be available for 12 hours.', 'iiko'),
                ),
                array(
                    'title'    => __( 'Использовать способы доставки Woocommerce', 'iiko' ),
                    'desc'     => __( 'Если вы используете встроенный типы доставки Woocommerce, вы можете включить эту опцию. 
                      Пример: <a href="https://i.imgur.com/NdhXNxn.jpg" target="_blank">https://i.imgur.com/NdhXNxn.jpg</a>', 'iiko' ),
                    'desc_tip' => __( 'Эта опция подключит js скрипт, который будет скрывать поле "Тип доставки" и изменять его значение,
                     в зависимости от выборанного значения а поле "Доставка" woocommerce. Вы можете использовать 2 типа "Единая ставка" и "Самовывоз".
                      При этом единая ставка может быть с разной ценой. В iiko будет передана итоговая стоимость заказа, включающая стоимость доставки.', 'iiko'),
                    'id'       => 'woo_delivery',
                    'class'     => 'new',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                ),
                'section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wc_settings_tab_demo_section_end'
                ),
            );

            break;

    }

    return apply_filters( 'iiko_fields', $settings, $section );
  }

    /**
    * Output the settings
    */
    public function output() {
    global $current_section;
    $settings = $this->get_settings( $current_section );
    WC_Admin_Settings::output_fields( $settings );
    }


    /**
    * Save settings
    */
    public function save() {
    global $current_section;
    $settings = $this->get_settings( $current_section );
    WC_Admin_Settings::save_fields( $settings );
    }


    /**
    * Add js to admin. KLADR and custom script + css
    *
    * @param $hook_suffix
    */
    public function add_scripts( $hook_suffix ){
    // инициализация только на странцие настроек woocommerce
        if ( 'woocommerce_page_wc-settings' !== $hook_suffix ) {
          return;
        }

        if(isset($_GET['section'], $_GET['tab']) && $_GET['section'] === 'export' && $_GET['tab'] === 'iiko') {
            add_action('admin_footer', [__CLASS__, 'export_js']);
        }

        wp_enqueue_style( 'jquery.kladr', IIKO_PLUGIN_URL . 'assets/css/jquery.kladr.min.css' );// inc css
        wp_register_script( 'jquery.kladr', IIKO_PLUGIN_URL . 'assets/js/jquery.kladr.min.js', array('jquery'), '1.0.0.', false );// inc klard

        wp_enqueue_script('jquery.kladr');
        wp_enqueue_script( 'iiko_admin_settings', IIKO_PLUGIN_URL . 'assets/js/admin/settings.js', array('jquery-migrate'), '1.0.0.', true);
    }

    public static function get_custom_gateway_title($num = '1'){
        return maybe_unserialize( get_option("woocommerce_alg_custom_gateway_{$num}_settings") );
    }

    public static function set_custom_gateway_title($option, $num = '1'){
        return update_option("woocommerce_alg_custom_gateway_{$num}_settings", serialize($option) );
    }

    public static function export_js(){
        require_once IIKO_PLUGIN_DIR . 'includes/export/export-settings-js.php';
    }

}

return new WC_Settings_IIKO();
