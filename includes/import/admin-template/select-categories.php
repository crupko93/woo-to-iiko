<?php
/**
 * Template of form select product categories
 */

if (empty($products['groups'])){
  echo '<div class="uk-alert-warning uk-animation-fade" uk-alert><a class="uk-alert-close" uk-close></a>'  . __('Product groups is empty! Please create groups in iiko.') . '</div>';
  return;
}

$products_obj = json_decode(json_encode($products['groups']));


?>

<form id="product_cats" class="uk-form-horizontal">
    <fieldset class="uk-fieldset">

        <label class="uk-form-label" for="select_product_cats"><?php _e('Select categories to import', 'iiko') ?></label>

        <div class="uk-form-controls">
            <select multiple="multiple" name="select_product_cats" id="select_product_cats" class="uk-select uk-form-width-medium">
              <?php
              foreach ($products_obj as $product) {
                if ( !empty($iiko['iiko_product_cats']) and in_array($product->id, $iiko['iiko_product_cats'], true) ):
                  echo '<option class="" value="' . $product->id . '" selected="selected">' . $product->name . '</option>';
                else :
                  echo '<option class="" value="' . $product->id . '">' . $product->name . '</option>';
                endif;
              } ?>
            </select>

            <input type="hidden" name="products" value="<?= $encoded ?>">

            <div class="uk-margin">
                <button class="uk-button uk-button-default" type="submit">
                  <span class="succes dashicons dashicons-yes" style="display: none; color: dodgerblue; margin-top: 4px;"></span>
                  <?php _e('Select categories', 'iiko'); ?>
                </button>
            </div>
        </div>

        <div class="messages"></div>
        <div class="iiko_result"></div>
    </fieldset>
</form>