/** ONLY ADMIN PAGES ONLY */
(function($) {

  jQuery('#google_token').closest('td').css( "background-color", "red" );

  console.log();

  $( 'select#address_validate' ).change( function() {
    if ( 'google' === $( this ).val() ) {
      $( this ).closest('tr').next( 'tr' ).hide();
      $( this ).closest('tr').next().next( 'tr' ).show();
    } else if ( 'kladr' === $( this ).val() ) {
      $( this ).closest('tr').next( 'tr' ).show();
      $( this ).closest('tr').next().next( 'tr' ).hide();
    } else {
      $( this ).closest('tr').next( 'tr' ).hide();
      $( this ).closest('tr').next().next( 'tr' ).hide();
    }
  }).change();

  $('[name="iiko_city"]').change( function() {
    $('#iiko_city_id').val( $( this ).attr('data-kladr-id') );
  });

})( jQuery );


jQuery(document).ready(function( $ ) {

  var $address = $('[name="woocommerce_store_city"]');
  var $addressIIKO = $('[name="iiko_city"]');

  $address.kladr({
    type: $.kladr.type.city
  });

  $addressIIKO.kladr({
    type: $.kladr.type.city
  });

});