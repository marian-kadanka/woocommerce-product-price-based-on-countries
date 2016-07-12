jQuery( function( $ ) {
	
	$( document.body ).on( 'wcpbc_refresh_cart_fragments', function() {
				
		/* Storage Handling */
		var $supports_html5_storage;
		try {
			$supports_html5_storage = ( 'sessionStorage' in window && window.sessionStorage !== null );

			window.sessionStorage.setItem( 'wc', 'test' );
			window.sessionStorage.removeItem( 'wc' );
		} catch( err ) {
			$supports_html5_storage = false;
		}
		
		var $fragment_refresh = {
			url: wc_cart_fragments_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'get_refreshed_fragments' ),
			type: 'POST',
			success: function( data ) {
				if ( data && data.fragments ) {

					$.each( data.fragments, function( key, value ) {
						$( key ).replaceWith( value );
					});

					if ( $supports_html5_storage ) {
						sessionStorage.setItem( wc_cart_fragments_params.fragment_name, JSON.stringify( data.fragments ) );
						sessionStorage.setItem( 'wc_cart_hash', data.cart_hash );
					}

					$( document.body ).trigger( 'wc_fragments_refreshed' );
				}
			}
		};

		$.ajax( $fragment_refresh );	
		
	} );
	

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