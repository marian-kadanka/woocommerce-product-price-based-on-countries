jQuery(document).ready(function($){

	var inpt_name = '';	
	var inpt_names = ['_regular_price', '_sale_price'];
	
	for(var i = 0; i<wcpbc_regions_keys.length; i++ ) {

		for (var j = 0; j<inpt_names.length; j++) {		

			inpt_name = '_' + wcpbc_regions_keys[i] + inpt_names[j];

			$('input[name="'+inpt_name+'"]').attr('readonly','readonly');
        	$('input[name="'+inpt_name+'"]').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());

        	inpt_name = '_' + wcpbc_regions_keys[i] + '_variable' + inpt_names[j];

        	$('input[name^="'+inpt_name+'"]').attr('readonly','readonly');
        	$('input[name^="'+inpt_name+'"]').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());        	
		}

		$('input[name="_' + wcpbc_regions_keys[i] + '_price_method"').attr('disabled','disabled');
    	$('input[name="_' + wcpbc_regions_keys[i] + '_price_method"').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());

		$('input[name^="_' + wcpbc_regions_keys[i] + '_variable_price_method"').attr('disabled','disabled');
    	$('input[name^="_' + wcpbc_regions_keys[i] + '_variable_price_method"').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
	}	
			
});