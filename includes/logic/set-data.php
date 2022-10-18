<?php
/**
 * Функции обработки зароса
 */


/**
 * Получаем подготовленный список организаций
 *
 * @return array | false - список организаций
 */
function set_organization(){
    return IikoApi::getOrganizationList();
}

/**
 * Получаем териминал
 *
 * @param string $option_name
 * @return bool
 */
function set_terminal( $option_name = 'iiko_terminal' ){

  /** Пользователь не выбрал ресторан - прерываем работу по получению терминалов */
  if(false === get_option('iiko_rest_id')) {
    return false;
  }
  $terminals = IikoApi::getDeliveryTerminal();
  return $terminals;
}


/**
 * Получаем список товаров
 *
 * @array $result, false - массив товаров или false при неудаче.
 */
function set_products(){

  if( false === get_option('iiko_rest_id') or false === get_option('iiko_terminal') )
    return false;

   return IikoApi::getNomenclature();
}


/**
 * Получаем категории меню зала ресторана
 * @deprecated
 * @return bool
 */
function set_menu_categories(){
  $products = set_products();
  $rest_menu_cats = $products['productCategories'];

  if( empty($rest_menu_cats) or !is_array($rest_menu_cats[0]) )
    return false;

  $menu_cats = false;
  foreach ($rest_menu_cats as $cats){
    foreach ($cats as $cat){
      $menu_cats[$cat['id']] = $cat['name'];
    }
  }
  return $menu_cats;
}
