<?php
/**
 * Форма импорта для админки
 */

add_action('add_form_import', 'add_form_import', 10, 2);
/**
 * @param array $iiko
 * @param bool $message
 */
function add_form_import( $iiko = array(), $message = false ){
  /**
   * Загружаем настройки, получаем токен
   * https://iiko.biz:9900/api/0/auth/access_token?user_id=OttoPizzaWine&user_secret=6hGf9h9W
   */
    /** 1 - Получаем список организаций */
    $organizations = set_organization();
    if(false === $organizations) {
        _e('Настройки соединения заданы не правильно.', 'iiko');
        return;
    }
?>

<!-- UIkit CSS -->
<link rel="stylesheet" href="<?php echo IIKO_PLUGIN_URL . 'assets/css/uikit.css'; ?>" />

<div class="uk-section uk-section-muted">
  <div class="uk-container">

    <div class="uk-card uk-card-default">
      <div class="uk-card-header">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
          <div class="uk-width-auto">
            <img src="<?=IIKO_PLUGIN_URL . 'assets/img/store.svg'?>" class="uk-display-inline-block" width="80" height="80">
          </div>
          <div class="uk-width-expand">
            <h3 class="uk-card-title uk-margin-remove-bottom"><?=__('Welcome to iiko importer', 'iiko')?></h3>
            <p class="uk-text-meta uk-margin-remove-top">
              <time datetime="2016-04-01T19:00">
                    <?php echo date( 'd.m.Y H:i', current_time( 'timestamp', 0 ));?>
              </time></p>
          </div>
        </div>

      <div class="uk-text-meta uk-margin uk-flex uk-flex-middle">
        <span><a class="uk-button uk-button-default uk-button-small" href="<?php echo '/wp-admin/admin.php?page=wc-settings&tab=iiko';?>"><?=__('General Settings', 'iiko')?></a></span>
      </div>

      <div id="message"><?php if($message) : ?>
          <div class="uk-alert-warning" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?=esc_attr($message)?></p>
          </div>
        <?php endif; ?>
      </div>

      </div>
    </div>
    <div>
        <div class="uk-card uk-card-default uk-card-body uk-card-hover">

<!--          <ul uk-tab>
            <li class="uk-active"><a href="#">Left</a></li>
            <li><a href="#">Item</a></li>
            <li><a href="#">Item</a></li>
            <li class="uk-disabled"><a>Disabled</a></li>
          </ul>-->

          <h3 class="uk-card-title"><?php _e('Import','iiko');?></h3>
          <ul>
            <li>1 <?php _e('Choose an organization and reload the page.', 'iiko')?></li>
            <li>2 <?php _e('Choose a terminal. Reload the page.', 'iiko')?></li>
            <li>3 <?php _e('Choose product categories to import.', 'iiko')?></li>
            <li>4 <?php _e('Start importing categories and products.', 'iiko')?></li>
          </ul>
        <?php
          /**
           * get form template
           */
          do_action('get_iiko_template_part', 'select-rest',$data = array( 'iiko' => $iiko, 'organizations' => $organizations));

          /** 3 - Получаем список терминалов, если выбрана организация */
          $terminals = set_terminal();
          if( !empty($terminals) and !is_string($terminals) ) {
            do_action('get_iiko_template_part', 'select-terminal', array(
                'iiko'          => $iiko,
                'organizations' => $organizations,
                'terminals'     => $terminals
            ));
          } elseif (is_array($terminals)){
            echo '<div class="uk-alert-warning" uk-alert>'. __('Terminal list is empty!', 'iiko') .'</div>';
          } elseif (is_string($terminals)){
	          echo '<div class="uk-alert-warning" uk-alert>'. $terminals .'</div>';
          }

          /** 3 - Получаем список товаров */
          $products = set_products();
          if( false !== $products){
            $data = serialize($products); $encoded = htmlentities($data);
            do_action('get_iiko_template_part', 'select-categories',
              array('iiko' => $iiko, 'products' => $products, 'encoded' => $encoded));

            do_action('get_iiko_template_part', 'run',
              array('iiko' => $iiko, 'products' => $products, 'encoded' => $encoded));
          }
        ?>
        </div>
      <?php
      /** Показ опций */
      do_action('get_iiko_template_part', 'show_options',$data = array( 'iiko' => $iiko, 'organizations' => $organizations));
      ?>
    </div>

    <div>
        <div class="uk-card uk-card-secondary uk-card-body">
            <h4><?php _e('Additional settings', 'iiko')?></h4>
            <form id="reset">
                <div class="form-group">
                    <p>
                      <?php _e('In case of error by the API, you can reset the transits and send the request again. 
                    <br> Attention list of terminals can be obtained no more than once a day.!', 'iiko') ?></p>
                    <button id="reset-org-btn" class="button-primary" type="submit" value="<?=wp_create_nonce('iiko')?>">
                      <?php _e('Reset restaurant list', 'iiko'); ?></button>
                    <button id="terminal-btn" class="button-primary" type="submit" value="<?=wp_create_nonce('iiko')?>">
                      <?php _e('Reset terminal list','iiko');?></button>
                    <button id="product-btn" class="button-primary" type="submit" value="<?=wp_create_nonce('iiko')?>">
                      <?php _e('Reset products and categories list','iiko'); ?></button>
                </div>
                <div class="form-group">
                  <p><h5><?php _e('In case of errors, you can remove all the products derived from iiko with images.', 'iiko') ?></h5></p>
                  <button id="delete-all" class="button-danger" type="submit"><?php _e('Remove items','iiko'); ?></button>
                </div>
              <div class="result uk-margin-top"></div>
            </form>
        </div>
    </div>

    <?php
    /**
     * Показ всех транзитов при дебаге
     */
     do_action('get_iiko_template_part', 'show_transient');
    ?>
</div>
</div>

<script>
jQuery(document).ready(function($){

  jQuery('#terminal').submit( function(event){
      event.preventDefault(); // останавливаем отправку формы
      ajaxs( 'set_terminal_id', this );
  } );
  jQuery('#product_cats').submit( function(event){
    jQuery('#products .iiko_result').html(
      '<div class="uk-alert-warning uk-animation-fade" uk-alert><a class="uk-alert-close" uk-close></a><div uk-spinner></div><div class="uk-display-inline-block" style="margin-left:20px;"><?php
          _e('Category loading ... This may take a long time. Do not refresh the page.','iiko');
          ?></div></div>');
      event.preventDefault(); // останавливаем отправку формы
      ajaxs( 'set_product_cats', this,  function( result ){
        console.log ( 'Result: ' + JSON.stringify(result));
        jQuery('#products .iiko_result').hide();
      });
  });
  jQuery('#products').submit( function(event){
      $('.uk-alert').hide();
      jQuery('#products .iiko_result').html(
      '<div class="uk-alert-warning uk-animation-fade" uk-alert><a class="uk-alert-close" uk-close></a><div uk-spinner></div><div class="uk-display-inline-block" style="margin-left:20px;"><?php
      _e('Products loading ... This may take a long time. Do not refresh the page.','iiko');
      ?></div></div>').show();
      event.preventDefault(); // останавливаем отправку формы
      ajaxs( 'set_products', this,
        // success function
        function( result ){},
        // always function
        null,
        // error function
        function( xhr, status, error ){
          console.log( 'fail:' + JSON.stringify(xhr) + " :: " + status + " :: " + error);
          jQuery('#products .iiko_result').html(
            '<div class="uk-alert-danger uk-animation-fade" uk-alert><a class="uk-alert-close" uk-close></a><div class="uk-display-inline-block" style="margin-left:20px;"><?php
              _e('An error occurred during the download! See the details in the JS console.','iiko');
              ?></div></div>');
        } );
  } );

  /* reset */
  jQuery('#reset-org-btn').click( function(event){
      event.preventDefault();
      ajaxs( 'reset_rest', this );
  } );
  jQuery('#terminal-btn').click( function(event){
      event.preventDefault();
      ajaxs( 'reset_terminal', this );
  } );
  jQuery('#product-btn').click( function(event){
      event.preventDefault();
      ajaxs( 'reset_products', this );
  } );

  jQuery('#delete-all').click( function(event){
    event.preventDefault();
    jQuery('.result').html(
      '<div class="uk-alert-warning uk-animation-fade" uk-alert><a class="uk-alert-close" uk-close></a><div uk-spinner></div><div class="uk-display-inline-block" style="margin-left:20px;"><?php
        _e('Products are deleted ... Do not refresh the page.','iiko');
        ?></div></div>');
    ajaxs( 'delete_products', this );
  } );

});
</script>

<style>
  .wp-admin select[multiple] {
      height: 300px;
  }
  pre {
    font: .875rem/1.5 Consolas,monaco,monospace;
    color: #666 !important;
    background: #fff;
  }
  .uk-section {
    padding-top: 20px !important;
  }
  .uk-container {
    max-width: 1400px;
  }
  h1.screen-reader-text {
    height: auto !important;
    display: inline-block !important;
    position: inherit !important;
    width: initial;
    line-height: normal;
    z-index: 999;
    margin: 0 auto;
    overflow: auto;
    clip-path: initial;
  }
  /*.iiko-wrap {
    background-color: #00968829;
    padding: 15px;
  }*/
  .iiko_result{
    margin-top: 15px;
    color: #00c57b;
  }
  .button-danger {
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 26px;
    height: 28px;
    margin: 0;
    padding: 0 10px 1px;
    cursor: pointer;
    -webkit-appearance: none;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
    background: #F44336;
    border: 1px solid #E91E63;
    border-top-color: #F44336;
    box-shadow: 0 1px 0 #E91E63;
    color: #fff;
    text-shadow: 0 -1px 1px #F44336, 1px 0 1px #F44336, 0 1px 1px #F44336, -1px 0 1px #F44336;
  }
  .uk-section-muted {
    background: #f8f8f8 url('<?php echo IIKO_PLUGIN_URL . 'assets/img/background-min.jpg	'?>')
  }
  .uk-card-secondary {
    background: #3e4a53;
  }
  #wpcontent {
    height: 100%;
    padding-left: 0px;
  }
  .wp-person a:focus .gravatar, a:focus, a:focus .media-icon img {
    box-shadow: none;
  }
  .update-nag {
    display: none !important;
  }
  .wp-core-ui .button-secondary:focus, .wp-core-ui .button.focus, .wp-core-ui .button:focus {
      border-color: transparent !important;
      box-shadow: none !important;
  }
  ::selection {
      background: rgba(255, 255, 255, 0.5);
      color: #fff;
      text-shadow: none;
  }
</style>


<!-- UIkit JS -->
<script src="<?php echo IIKO_PLUGIN_URL . 'assets/js/uikit.js'?>" ></script>
<script type="application/javascript" src="<?php echo IIKO_PLUGIN_URL . 'assets/js/uikit-icons.min.js'?>" ></script>

<?php
}



