jQuery( function( $ ) {		

	//shipping_calculator_submit
	if ( $('#calc_shipping_country').length > 0 ) {

		$( document ).ajaxSuccess(function( event, request, settings, data ) {			
			if ( typeof settings.data !== 'undefined' && settings.data.indexOf('&calc_shipping=') > -1 ) {
				var $html = $.parseHTML( data );
				
				var new_country_sel = $( '.wcpbc-widget-country-selecting', $html ).html();
				$( '.wcpbc-widget-country-selecting' ).html( new_country_sel );

				//only premium
				var new_currency_sel = $( 'wcpbc-widget-currency-switcher', $html ).html();
				$( '.wcpbc-widget-currency-switcher' ).html( new_currency_sel );				
			}
			
		});
	}	
	
});