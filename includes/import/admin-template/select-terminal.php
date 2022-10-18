<?php
/**
 *
 * $terminals;
 * $iiko;
 */

#echo '<pre>'; var_dump($terminals); echo '</pre>';

?>

<form id="terminal" class="uk-form-horizontal">
  <div class="form-group">
    <label class="uk-form-label" for="select_terminal"><?php _e('Select a terminal', 'iiko') ?></label>

      <div class="uk-form-controls">
          <div uk-form-custom="target: > * > span:first-child">
            <select class="form-control" id="select-rest" name="select_terminal">
              <?php
              foreach ($terminals as $terminal) {
                if ( $terminal['deliveryTerminalId'] === get_option('iiko_terminal') ):
                  echo '<option value="'. $terminal['deliveryTerminalId'] .'" selected >'. $terminal['deliveryRestaurantName'] .'</option>';
                else :
                  echo '<option value="'. $terminal['deliveryTerminalId'] .'" >'. $terminal['deliveryRestaurantName'] .'</option>';
                endif;
              }
              ?>
            </select>
            <button class="uk-button uk-button-default" type="button" tabindex="-1">
              <span></span>
              <span uk-icon="icon: chevron-down"></span>
            </button>
          </div>

        <button class="uk-button uk-button-default" type="submit">
          <span class="succes dashicons dashicons-yes" style="display: none; color: dodgerblue; margin-top: 4px;"></span>
          <?php _e('Select terminal', 'iiko'); ?>
        </button>
      </div>

    <div class="iiko_result"></div>
  </div>

</form>