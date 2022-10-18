<?php
/**
 * @version
 * @package
 * @author Aleksey Tikhomirov <a.tihomirov@dataduck.com>
 * @copyright 28.06.2019 Dataduck
 */

$cash = IikoExport::get_custom_gateway_title($num = '1');
$card = IikoExport::get_custom_gateway_title($num = '2');

$cash_label = esc_html__('Payment cash name', 'iiko');
$card_label = esc_html__('Payment card name', 'iiko');

$cash_desc = esc_html__('Payment gateway name for cash', 'iiko');
$card_desc = esc_html__('Payment gateway name for card', 'iiko');

if(!class_exists('Alg_WC_Custom_Payment_Gateways')){
  echo __('Для корректной работы необходимо установить и активировать плагин "Custom Payment gateways for WooCommerce"', 'iiko');
  return;
}

if(empty($cash) || empty($card)){
    echo __('Активируйте платежные шлюзы Custom Gateway #1 и Custom Gateway #2. Для дальнейшей настройки.', 'iiko');
    return;
}

?>

<script>
  jQuery(document).ready(function($) {
    $('.form-table').html('<div class="wrap"><label class="pay"><?=$cash_label?></label><div class="pay-input"><input id="cash" type="text" value="<?=$cash['title']?>"></div>' +
    '<span class="description"><?=$cash_desc?></span></div>');
    $('.form-table').append('<div class="wrap"><label class="pay"><?=$card_label?></label><div class="pay-input"><input id="card" type="text" value="<?=$card['title']?>"></div>' +
    '<span class="description"><?=$card_desc?></span></div>');

    $('[name="save"]').on('click', function (event) {
      event.preventDefault(); // останавливаем отправку формы

      let data = {};
      data.cash = $('#cash').val();
      data.card = $('#card').val();
      ajaxs( 'save_payment_setting', data)
        .done(function( response ){
          console.log(this);
          $('[name="save"]').addClass('success').html('<span class="dashicons dashicons-yes"></span> Сохранено!');
      });
    })
  });
</script>
<style>
  .wrap{
    display: flex;
    align-items: center;
    justify-content: left;
  }
  label.pay{
    text-align: left;
    margin: 10px 10px 10px 10px;
    width: 200px;
    line-height: 1.3;
    font-weight: 600;
  }
  .pay-input{
    padding: 10px;
    line-height: 1.3;
  }
  .woocommerce table.form-table .pay-input input{
    width: 250px;
  }
  button.success span.dashicons.dashicons-yes {
    margin-top: 4px;
  }
</style>