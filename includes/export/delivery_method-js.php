<?php
/**
 * @version
 * @package
 * @author Aleksey Tikhomirov <a.tihomirov@dataduck.com>
 * @copyright 28.06.2019 Dataduck
 */
?>


<script>
  jQuery(document).ready(function($) {

      window.eBancnotes = [1,5,10,20,50,100,200,500,1000];
      
      
//    Commented by VCODE Digital Solutions 17.07.19
      
//    $('#delivery_type_field').hide();
//    $('#time_choose_field').removeClass('form-row-first');
      
//      Hide some custom inputs
      $('#bancnote').hide();
      $('#surrender').hide();
    
    <?php
        
      $type = '';
      if($_POST['delivery'] == 'true') $type = '0';
      if($_POST['pickup'] == 'true') $type = '1';
      
    ?>  
      
    $("#delivery_type").val(<?= $type;?>);  
//    console.log(<?= $type;?>);
      
//    $('input[name="billing_phone"]').mask('37369423639');  
    $('input[name="billing_phone"]').attr("disabled", true);
    $('input[name="billing_phone"]').parent().after('<a href="javascript:void(0)" data-toggle="modal" data-target="#myModaLogin" style="display:inline-block;padding-top:0.75em;"><svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="#ff6900" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"/></svg></a>');
      
    setTimeout(function(){
        $('#bancnote_2').on('focus', function(){
            $('#bancnote_2').keyup(function(){
               var value1 = parseInt($('#bancnote_2').val()) || 0;
               $('#bancnote').val(value1);
            });         
        });  
        /***********************************************************/
        /**********************************/
        $('#surrender_2').on('change', function() {
            if($(this).is(":checked")) {
                $('#surrender').attr("checked", true);
            }else{
                $('#surrender').attr("checked", false);
            }
        });
    },5000);
      
      
    let selected = '';
    function out_delivery_type($val){
      if($val === 1){
        return 'Самовывоз';
      }
      return  'Доставка';
    }

    // Изменение
    $(document).on('change', 'input[name*="shipping_method"]', function(e) {
      var regex_delivery = /flat_rate/; // Единая ставка (доставка)
      var res_delivery = regex_delivery.test($(this).val());

      var regex_local = /local_pickup/; // Единая ставка (доставка)
      var res_local = regex_local.test($(this).val());

      if(res_delivery === true){
        selected = $("#delivery_type option[value*='0']").attr("selected", true); // val 1 = Доставка
        console.log( 'Доставка'  );
      } else if(res_local === true) {
        selected = $("#delivery_type option[value*='1']").attr("selected", true); // val 1 = Самовывоз
        console.log( 'Самовывоз' );
      }

    });

    // По-умолчанию

    // Доставка
    let mask_delivery = 'shipping_method_0_flat_rate';
    let result_delivery = $("input[id*=" + mask_delivery + "]");

    if(result_delivery !== undefined){
      result_delivery.each( function( key, value ) {

        function change_delivery() {
          if(value.checked === true){
            selected = $("#delivery_type option[value*='0']").attr("selected", true); // val 1 = Доставка
          }
        }

      });
    }

   // Самовывоз
    let mask = 'shipping_method_0_local_pickup';
    let result = $("input[id*=" + mask + "]");
    if(result !== undefined){
      result.each( function( key, value ) {

        $('body').on('change', value, function() {
          if(value.checked === true){
            selected = $("#delivery_type option[value*='1']").attr("selected", true); // val 1 = Самовывоз
          }
        });

      });
    }


  });
</script>