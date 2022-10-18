<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 25.10.2018
 * Time: 13:07
 */


/**
 * Cохраняем даные о заказе в json для анализа
 * @deprecated
 * @param $data
 * @param string $filename
 */
function logging_ikko( $data, $filename = 'iiko_log' ){
  // Выполняем действие из action
  $result = manage_json_file( $action = 'add', $data, $filename );
  if(function_exists('wc_get_logger'))
    $logger = wc_get_logger();
  if( isset($logger)){
    $logger->debug( $filename . ' '. $result , array( 'source' => 'iiko' ) );
    $logger->debug( $data , array( 'source' => 'iiko' ) );
  }
}


/**
 * Выполняем работу с файлом
 * @param string $action - действие которое надо выполнить
 * @param array $data - данные для записи
 * @param string $filename - string
 * @return bool|string
 */

function manage_json_file ( $action = 'add', $data = array(), $filename = 'iiko_log' ){
  $result = '';
  switch ( $action ){
    case 'add' :
      if ( true === file_json_write( $data, $filename) ){
        $result = 'Файл записан';
      } else {
        //$result = 'Файл существует';
        if ( true === file_json_delete( $filename )){
          file_json_write( $data, $filename);     $result = 'Файл перезаписан';
        }
        else
          $result = 'Невозможно удалить файл';
      }
      return $result;
      break;
    case 'remove' :
      if ( true === file_json_delete( $filename ) )
        file_json_write( $data, $filename);
      else
        return $result = 'Невозможно удалить файл';
      break;
    default :
      if ( true === file_json_exist($filename) )
        $result = true;//Файл есть
      else
        $result = false;//Файла нет
      return $result;
      break;
  }
}

/**
 * Проверяем есть ли файл
 * @param $filename
 * @return bool
 */
function file_json_exist( $filename ){
  $uploads_dir = wp_upload_dir();
  $uploads_dir = $uploads_dir['basedir'];
  $uploads_dir = $uploads_dir . '/' . 'iiko';
  $filename = !empty($filename) ? $filename . '.json' : 'data.json';
  $file = $uploads_dir . '/' . $filename;
  if ( wp_mkdir_p($uploads_dir) && file_exists($file) ) {
    return true;
  }
  return false;
}


/**
 * Удаляем файл из директории
 * @param $filename
 * @return bool
 */
function file_json_delete( $filename ){
  $uploads_dir = wp_upload_dir();
  $uploads_dir = $uploads_dir['basedir'];
  $uploads_dir = $uploads_dir . '/' . 'iiko';
  $filename = !empty($filename) ? $filename . '.json' : 'data.json';
  $file = $uploads_dir . '/' . $filename;
  if ( wp_mkdir_p($uploads_dir) && file_exists($file) ) {
    unlink($file);
    return true;
  }
  return false;
}

/**
 * Запись файла Json в WordPress
 * @param $data
 * @param $filename
 * @return bool
 */
#add_action('file_json_write', 'file_json_write', 10, 2);
function file_json_write($data, $filename ){
  $uploads_dir = wp_upload_dir();
  $uploads_dir = $uploads_dir['basedir'];
  $uploads_dir = $uploads_dir . '/' . 'iiko';
  $filename = !empty($filename) ? $filename . '.json' : 'data.json';
  $file = $uploads_dir . '/' . $filename;
  //var_dump($file);
  if ( wp_mkdir_p($uploads_dir) && !file_exists($file) ) {
    if (!file_exists($uploads_dir . '/index.php')) {
      @file_put_contents($uploads_dir . '/index.php', '<?php' . PHP_EOL . '// silence is golden');
    }
    if ( $file_handle = @fopen($file, 'w') ) {
      fwrite($file_handle, json_encode( $data, JSON_UNESCAPED_UNICODE) );
      fclose($file_handle);
      return true;
    }
  }
  return false;
}

/**
 * Чтение файла Json в WordPress
 * @param array $data
 * @param string $filename
 * @return array|mixed|null|object
 */
#add_filter('file_json_read', 'file_json_read', 10, 2);
function file_json_read( $data = null, $filename = '' ){
  $uploads_dir = wp_upload_dir();
  $uploads_dir = $uploads_dir['basedir'];
  $uploads_dir = $uploads_dir . '/' . 'iiko';
  $filename = !empty($filename) ? $filename . '.json' : 'data.json';
  $file = $uploads_dir . '/' . $filename;
  if( false !== ($data = file_get_contents( $file )) ) {
    $data = json_decode( $data, true );
    return $data;
  }
  else
    return 'Ошибка чтения файла';
}