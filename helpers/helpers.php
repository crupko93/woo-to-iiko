<?php
/**
 * Вспомогательные функции
 */

/**
 * Функция вывода ошибок в консоль и лог файл
 *
 * @param string $data
 * @param bool $error - true если вызов в месте ошибки и false если нет.
 *
 * @return string|null - html
 */
/**
 * Функция вывода ошибок в консоль и лог файл
 *
 * @param $data
 * @param bool $echo - выводить сразу ошибку или вернуть результат
 *
 * @return null
 */
function console_log( $data = '', $p1 = '', $p2 = '' ){

    if(is_wp_error($data)){
        $data = 'Wp_error: ' . $data->get_error_code() . ' - '. $data->get_error_message();
    }

    // Если включен режим отладки
    $debug = check_debug();
    if($debug || is_wp_error($data) ) {
        error_log( 'Iiko log: ' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE ), 0 );
    }


    if ( wp_doing_ajax() && class_exists( 'AJAX_Simply_Core') ){
        $html = json_encode( $data, JSON_UNESCAPED_UNICODE );
        $jx = new AJAX_Simply_Core;
        $jx->console($html);
    } else {
        if(!is_admin()) {
             echo '<script> console.log(' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) . ') </script>';
        } else {
            $val = (string)get_option( 'iiko_log', '') . PHP_EOL;
            $val .= (string)wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) . '<br>' . PHP_EOL;
            update_option( 'iiko_log', $val, false);
        }
    }
}


function iiko_notice(){
    $html = get_option( 'iiko_log', '');
    if(!empty( $html)):
        ?>
        <script> console.log(<?=esc_js($html)?>); </script>
        <?php
        delete_option( 'iiko_log');
    endif;
}
add_action( 'admin_notices', 'iiko_notice');

/**
 * Включен ли дебаг режим?
 * @return bool - true, debug mode - enabled, false - disabled
 */
function check_debug() {
    $debug = get_option('iiko_debug');
    return $debug === 'yes' || WP_DEBUG === true;
}

/** @deprecated  */
function is_debug() {
    return check_debug();
}


/**
 * Функция подключает шаблон и передает ему аргументы из параметров.
 *
 * @param string $template
 * @param array $data - ассоциативный массив!
 * @return string|void
 */
function get_iiko_template_part( $template = '', $data = array() ){
    if( empty($template) or !is_array($data) )
        return;
    extract( $data ); // data - ассоциативный массив!
    $template_dir =  IIKO_PLUGIN_DIR . 'includes/import/admin-template/';
    $file = $template_dir . $template . '.php';

    /*  ob_start();*/
    require_once $file;
    /*  $content = ob_get_contents();
      ob_end_clean();*/

    return $template;
}
add_action('get_iiko_template_part', 'get_iiko_template_part', 10 ,2 );


/**
 * Показ IIko ID для категорий товаров
 * define the product_cat_edit_form_fields callback
 */
function action_product_cat_edit_form_fields( $term, $int ) {
    if(!empty(get_term_meta($term->term_id, 'iiko-id', true)))
        echo '<b>' . __('Iiko ID', 'iiko') .':</b> ' . get_term_meta($term->term_id, 'iiko-id', true);
}
add_action( 'product_cat_edit_form_fields', 'action_product_cat_edit_form_fields', 10, 2 );



/**
 * Отправка email уведомления пользователю при смене статуса заказа на "не удался"
 *
 * @param $order_id
 * @param $old_status
 * @param $new_status
 * @param $order
 */
function send_custom_email_notifications( $order_id, $old_status, $new_status, $order ){
    if ( $new_status === 'cancelled' || $new_status === 'failed' ){
        $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
        $customer_email = $order->get_billing_email(); // The customer email
    }

    if ( $new_status === 'cancelled' ) {
        // change the recipient of this instance
        $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Cancelled_Order']->trigger( $order_id );
    }
    elseif ( $new_status === 'failed' ) {
        // change the recipient of this instance
        $wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Failed_Order']->trigger( $order_id );
    }
}
add_action('woocommerce_order_status_changed', 'send_custom_email_notifications', 10, 4 );


/**
 * Получение объекта покупателя
 * по телефону
 *
 * @param string $phone телефон
 *
 * @return WC_Customer|bool
 * @throws Exception
 */
function mihdan_get_customer_by_billing_phone( $phone ) {
    global $wpdb;

    $phone = trim( $phone );

    $customer_id = $wpdb->get_var( $wpdb->prepare( "
    SELECT user_id 
    FROM $wpdb->usermeta 
    WHERE meta_key = 'billing_phone'
    AND meta_value = '%s'
    ", $email ) );

    if ( ! $customer_id ) {
        return false;
    }

    $customer = new WC_Customer( $customer_id );

    if ( ! $customer ) {
        return false;
    }

    return $customer;
}