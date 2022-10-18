/**
 * Checkout page scripts
 */

jQuery(document).ready(function($) {

  /**
   * Form fileds
   */
  let $s_time = $( 'select#time_choose' ); // выбрать время или нет?
  let $s_dtype = $('select#delivery_type'); //Самовывоз или доставка

  if($s_time.val() === '1')
    $('#datetimepicker_field').removeClass('display-none');

  $s_time.change( function() {
    // console.log('it`s magic!');
    if ( '1' === $( this ).val() ) {
      $( '#datetimepicker_field' ).removeClass('display-none');
    } else {
      $( '#datetimepicker_field' ).addClass('display-none').val('');
    }
  });


  if($s_dtype.val() === '1'){
    $('.street, .home, .housing, .apartment, .entrance').addClass('display-none');
  }
  $s_dtype.change( function() {
    if ('1' === $(this).val()) {
      $('.street, .home, .housing, .apartment, .entrance').addClass('display-none');
    } else {
      $('.street, .home, .housing, .apartment, .entrance').removeClass('display-none');
    }
  });


  /**
   * Form fields validate
   */

  let checkout_form = $( 'form.checkout' );
  checkout_form.on( 'checkout_place_order', function() {

    // условие валидации в зависимости от типа доставки
    let $bdt = function (){
        let bd = $('select#delivery_type').val();

        if( bd === '0') {
          return true; //required
        } else {
          $('input[name="billing_street"]').val('');//очищаем поле
          $('input[name="billing_home"]').val('');
          $('input[name="billing_housing"]').val('');
          $('input[name="billing_apartment"]').val('');
          $('input[name="billing_entrance"]').val('');
          $('input[name="billing_street_id"]').val('');
          $('input[name="billing_street_iiko_name"]').val('');
          return false;
        }
    };

    // условие валидации в зависимости от времени доставки
    let $timechose = function(){

      let time_choose = $('select#time_choose').val();

      if( time_choose === '1') {
        $('input#datetimepicker').val();
        return true; //required
      } else {
        return false;
      }
    };

    // задаем настройки валидатора
    jQuery.validator.setDefaults({
      debug: true,
      success: "valid"
    });

    // проверяем поля
     checkout_form.validate({
        rules: {
          billing_first_name: {
            required: true,
          },
          billing_phone: {
            required: true,
          },
          billing_street: {
            required: $bdt(),
          },
          billing_home: {
            required: $bdt(),
          },
          billing_date_time: {
            required: $timechose(),
          }
        },
        messages: {
          billing_first_name: "Заполните поле",
          billing_phone: "Заполните поле",
          billing_street: "Заполните поле",
          billing_home: "Заполните поле",
          billing_date_time: "Укажите время",
        }
    });

    //Если форма валидна вернет true
    // return true to continue the submission or false to prevent it return true;
    return checkout_form.valid();
  });


});