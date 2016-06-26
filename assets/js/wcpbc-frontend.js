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
		
});