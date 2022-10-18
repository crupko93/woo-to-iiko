<?php
/**
 * Plugin Name: Woo to Iiko
 * Version: 0.1.0
 * Plugin URI: http://woo-to-iiko.rwsite.ru/
 * Description: Woocommerce to Iiko Plugin. Import products and Export orders.
 * Author: VCODE Digital Solutions <support@rwsite.ru>
 * Author URI: https://vcode.md/
 *
 * Tags: woocommerce, iiko, api
 * Requires at least: 4.6
 * Tested up to: 5.2.3
 * Requires PHP: 5.6+
 * WC requires at least: 3.2.0
 * WC tested up to: 3.6.5
 *
 * Text Domain: iiko
 * Domain Path: /lang/
 *
 * @package WordPress
 * docs: https://docs.google.com/document/d/1pRQNIn46GH1LVqzBUY5TdIIUuSCOl-A_xeCBbogd2bE/edit#
 *
 * Commercial license: Single license
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
if ( ! defined( 'IIKO_PLUGIN_FILE' ) ) {
    define( 'IIKO_PLUGIN_FILE', __FILE__  );
}
if ( ! defined( 'IIKO_PLUGIN_DIR' ) ) {
    define( 'IIKO_PLUGIN_DIR', plugin_dir_path(__FILE__)  );
}
if ( ! defined( 'IIKO_PLUGIN_URL' ) ) {
    define( 'IIKO_PLUGIN_URL', plugin_dir_url(__FILE__ )  );
}


iiko_loader();
iiko_init();

function iiko_init () {
    //parent class
    $instance = IikoAdmin::instance( __FILE__, '0.1.0', 'iiko_import', 'iiko' );

    new DeliveryTerminals();

    return $instance;
}

/**
 *  LOADER
 */
function iiko_loader(){

    require_once 'components/tgm/require_plugins.php';
    require_once 'components/plugin-updates/plugin-update-checker.php';
    require_once 'components/licence-manager/licence-manager.php';

    /** Подключение сервера обновлений */
    $updater = Puc_v4_Factory::buildUpdateChecker(
        'https://bitbucket.org/bo3gyx/woo-to-iiko/',
        __FILE__,
        'woo-to-iiko'
    );
    $updater->setAuthentication(array(
        'consumer_key' => 'dujPkdwkRYXqFHW3sE',
        'consumer_secret' => '526ma6uxMtpMfWfb5NsVb8pjp6y5YDLy',
    ));
    $updater->setBranch('Single');


    /* Подключение ajax библиотеки */
    require_once 'components/ajax-simply/ajax-simply.php';

    // Вспомогательные фнукции
    require_once 'helpers/helpers.php';
    require_once 'helpers/dashboard-widget.php';
    require_once 'helpers/yoast-seo.php';
    require_once 'helpers/DeliveryTerminals.php';

    // Load plugin libraries
    require_once 'includes/lib/IIKO_Admin_Api.php';// базовый класс настроек плагина
    require_once 'includes/lib/IikoCustomer.php';// unused

    // ajax обработчики
    require_once 'includes/ajax.php';

    // логика
    require_once 'includes/logic/set-data.php';
    require_once 'includes/logic/add-data.php';
    require_once 'includes/logic/remove-data.php';

    // Страница настроек импорта
    require_once 'includes/import/IikoApi.php'; // функции работы с Api
    require_once 'includes/import/IikoAdmin.php';// Админка импорта
    require_once 'includes/import/form-import.php'; // HTML формы импорта
    require_once 'includes/import/cron_auto_update.php';

    // события для экспорта заказа в iiko
    require_once 'includes/export/create-order.php';
    require_once 'includes/export/woo-checkout.php';
    require_once 'includes/export/filters.php';
    require_once 'includes/export/IikoExport.php';

    add_filter( 'woocommerce_get_settings_pages', 'iiko_add_settings_page' );
    function iiko_add_settings_page( $settings ) {
        $settings[] = require 'includes/WC_Settings_IIKO.php';
        return $settings;
    }
}
