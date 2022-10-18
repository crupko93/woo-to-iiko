<?php
/**
 * @version
 * @package
 * @author Aleksey Tikhomirov <a.tihomirov@dataduck.com>
 * @copyright 28.06.2019 Dataduck
 */

defined( 'ABSPATH' ) || wp_die('ABSPATH is not defined');

class IikoExport
{


    public function __construct()
    {
        add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts') );
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
    }

    /**
     * @param string $num
     * @return array
     */
    public static function get_custom_gateway_title($num = '1'){
        return maybe_unserialize( get_option("woocommerce_alg_custom_gateway_{$num}_settings", '') );
    }

    /**
     * @param $option
     * @param string $num
     *
     * @return bool
     */
    public static function set_custom_gateway_title($option, $num = '1'){
        return update_option("woocommerce_alg_custom_gateway_{$num}_settings", $option );
    }

    public static function export_js(){
        require_once IIKO_PLUGIN_DIR . 'includes/export/export-settings-js.php';
    }
}

new IikoExport();