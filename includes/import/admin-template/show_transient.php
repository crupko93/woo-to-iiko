<?php
/**
 * Вывод транизитов в интерфейс
 */

return false;

function show_transient(){
  $data = IikoApi::getAllTransient();
  console_log( __('Transients', 'iiko') . ': ' . json_encode($data,JSON_UNESCAPED_UNICODE));
  ?>
  <div class="uk-child-width-1-1@m uk-grid-small" uk-grid>
    <div class="uk-card uk-card-default uk-card-body">
      <h4><?php echo __('Transients', 'iiko');?></h4>
      <?php echo '<pre>'; var_dump($data); echo '</pre>'; ?>
    </div>
  </div>
<?php
}