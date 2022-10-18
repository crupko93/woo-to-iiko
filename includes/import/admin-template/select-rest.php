<?php
/**
 * Форма выбора ресторана
 * $iiko
 * $organization
 */

#echo '<h1>Файл подключен!</h1>';
#echo '<pre>'; var_dump(get_option('iiko_rest_id')); echo '</pre>';
?>


<form id="rest" class="uk-form-horizontal">
    <fieldset class="uk-fieldset">

        <label class="uk-form-label" for="selectrest"><?php _e('Select a restaurant', 'iiko') ?></label>

        <div class="uk-form-controls">
            <div uk-form-custom="target: > * > span:first-child">
                <select class="uk-select" id="select-rest" name="selectrest">
                    <?php
                    foreach ($organizations as $org) {
                      if ( $org['id'] === get_option('iiko_rest_id') ) {
                        echo '<option value="' . $org['id'] . '" selected >' . $org['name'] . '</option>';
                      }  else {
                        echo '<option value="' . $org['id'] . '">' . $org['name'] . '</option>';
                      }
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
                <?php _e('Select restaurant', 'iiko'); ?>
            </button>

        <div class="iiko_result"></div>
    </fieldset>
</form>

<script>
  jQuery('#rest').submit( function(event){
    event.preventDefault(); // останавливаем отправку формы
    ajaxs( 'set_rest_id', this );
  });
</script>