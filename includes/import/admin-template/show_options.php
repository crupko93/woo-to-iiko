<?php
/**
 * Показ всех опций при дебаг режиме
 */
//if(!is_debug()) {
//    return false;
//}

$iiko = IikoApi::get_settings();
?>

<div class="uk-card uk-card-secondary uk-padding-small">
  <h3 class="uk-card-title">Data</h3>
  <h5><?php _e('Settings', 'iiko') ?>:</h5>
  <span><?php _e('Please disable debug mode after work is ended', 'iiko')?></span>
  <?php
  foreach ($iiko as $name => $setting) {
    echo '<div><b>' . $name . '</b>: ';
    if(is_array($setting)) {
      echo '<br>';
      foreach ($setting as $value) {
          echo '-' . print_r($value) . '<br>';
      }
    }
    else {
        echo $setting;
    }
    echo '<br></div>';
  }
  ?>
</div>
