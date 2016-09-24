jQuery( function( $ ) {
	
	$( document.body ).on( 'wcpbc_refresh_cart_fragments', function() {
				
		// Ajax action
		$.post( wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wc_price_based_country_refresh_cart' ), function( response ) {
			var fragments = response.fragments;
			var cart_hash = response.cart_hash;
			
			// Replace fragments
			if ( fragments ) {
				$.each( fragments, function( key, value ) {
					$( key ).replaceWith( value );
				});
			}

			// Trigger event so themes can refresh other areas
			$( document.body ).trigger( 'added_to_cart', [ fragments, cart_hash ] );
		});			

		

	});

	// wcpbc_frontend_params is defined we must refresh cart fragments and price filter
	if ( typeof wcpbc_frontend_params !== 'undefined' ) {		
		$( document.body ).trigger( 'wcpbc_refresh_cart_fragments' );				
	}		

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