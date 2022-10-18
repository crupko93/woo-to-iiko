<?php


## Произвольный виджет в консоли в админ-панели
add_action('wp_dashboard_setup', 'iiko_dashboard_widgets');
function iiko_dashboard_widgets() {
    wp_add_dashboard_widget('iiko_widget', __('Iiko info', 'iiko'), 'dashboard_widget_content');
}

function dashboard_widget_content() {
  #  echo do_shortcode('');

	$products = set_products();

	if(empty($products['groups'])){
	  echo __('Product information has not been received', 'iiko');
	  return;
  }

  $result = array();
  foreach ($products['groups'] as $category) {
    $result[] = add_woo_cats( $category );
  };


	$only_price = get_option('iiko_update_only_price');
	$enable_cron = get_option('iiko_cron_auto_update');
	/** apply_filters( $tag, $value, $var... ); */

  $lic = check_license();
  if (empty($lic)){
    $lic = '<span style="color: red">' . __('Trial version', 'iiko') . '</span>';
  }

  ?>
  <div class="inside" style="background-color: #d8ecd8; margin-top: 0; padding-top: 20px;">
    <h4><?php _e('Last uploaded data:', 'iiko'); ?></h4>
    <div><?php echo __('<b>Revision</b> №', 'iiko' ) . $products['revision'] ?></div>
    <div><?php echo __('<b>Upload date: </b> ', 'iiko' ) . $products['uploadDate'] ?></div>
    <div><?php echo __('<b>Total items: </b>', 'iiko' ) . count($products['products']) ?></div>
  </div>

  <div class="inside">
    <div><?=__('When re-import update prices only?', 'iiko') . ' <b>'.  $only_price . '</b>'?></div>
    <div><?= __('Scheduled upload included?', 'iiko') . ' <b>'.  $enable_cron . '</b>'?></div>
    <div><?=__('License:', 'iiko') . ' <b>'. $lic . '</b><br>'?></div>
  </div>
	<?php
}
