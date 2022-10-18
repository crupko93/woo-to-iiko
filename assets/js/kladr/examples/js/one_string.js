$(function () {
	var $address = $('[name="billing_street"]');

	$address.kladr({
		oneString: true,
		parentType: $.kladr.type.region,
		parentId: '7800000000000',	
		change: function (obj) {
			log(obj);
			console.log('Name:' + obj.name);
			console.log('Type:' + obj.type);
			console.log('id:' + obj.id);
			
			if(obj.type === "Улица")
				$('#street_id').val( obj.name );
		    else
				$('#street_id').val( obj.name + ' ' + obj.type.toLowerCase());
		
			return $address.val( obj.type + ' ' + obj.name );
		}		
	});
 	function log(obj) {
		var $log, i;

		$('.js-log li').hide();

		for (i in obj) {
			$log = $('#' + i);

			if ($log.length) {
				$log.find('.value').text(obj[i]);
				$log.show();
			}
		}
	}  
});
