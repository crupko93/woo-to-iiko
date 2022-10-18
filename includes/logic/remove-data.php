<?php
/**
 *
 */

## Удаляет все вложения записи (прикрепленные медиафайлы) записи вместе с записью (постом)
add_action( 'iiko_delete_img', 'iiko_delete_img' );
function iiko_delete_img( $postid ){
  $img = get_post_meta($postid,'_thumbnail_id', true);
  $gallery = get_post_meta( $postid, '_product_image_gallery', true);
  if( !empty($img) ){
    if( false === wp_delete_attachment( $img, true ) ){
      console_log("Не удалось удалить медиа файл $img", true);
    } else {
      console_log("Медиа файл удален");
    }
  }
  if(!empty($gallery)) {
    $ga = explode(",", $gallery);
    //console_log(json_encode($gallery), true);
    foreach ($ga as $id){
      if( false === wp_delete_attachment( $id, true ) ){
        console_log("Не удалось удалить медиа файл $id", true);
      } else {
        console_log("Медиа файл удален");
      }
    }
  }
}