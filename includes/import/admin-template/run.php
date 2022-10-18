<?php
/**
 * Select products and run import products
 */

if (empty($products['groups'])){
  return false;
}

$data = serialize($products);

/**
 * test_long_script($data);
 * @deprecated - не используется
 * @param string $data

function test_long_script( $data = '' )
{
	$iiko_product_cats = get_option ('iiko_product_cats');
  $products = unserialize($data);
  $count = 0;
  foreach ($products['products'] as $product) {
    # $jx->console("foreach start" . json_encode($product));
    $product = json_decode(json_encode($product));
    #console_log($product, true);
    if ($count >= 10) {
      break;
    }
    $result = add_woo_products($product, $iiko_product_cats);
    if ($result == true) {
      $count++;
    }
  }
  $message = sprintf(__("Всего загружено и обновлено товаров: <strong>%s</strong>", 'iiko'), $count);
  echo $message;
}
 */
?>

<div><?php echo __('<b>Revision</b> №', 'iiko' ) . $products['revision'] ?></div>
<div><?php echo __('<b>Upload date: </b> ', 'iiko' ) . $products['uploadDate'] ?></div>
<div><?php echo __('<b>Total items: </b>', 'iiko' ) . count($products['products']) ?></div>

<form id="products" class="products uk-margin-top">
  <div class="form-group">
    <?php $data = serialize($products['products']);  $encoded = htmlentities($data);
    echo '<input type="hidden" name="products" value="'. $encoded .'">';
    ?>
    <input type="hidden" name="iiko_nonce" value="<?= wp_create_nonce('iiko') ?>">

    <button class="uk-button uk-button-primary" type="submit">
      <span class="succes dashicons dashicons-yes" style="display: none; color: dodgerblue; margin-top: 4px;"></span>
      <?php _e('Start import products', 'iiko'); ?>
    </button>

    <div id="iiko-message" class="messages"></div>
    <div id="iiko-result" class="iiko_result"></div>
  </div>
</form>
