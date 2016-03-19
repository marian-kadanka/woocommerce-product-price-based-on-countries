jQuery( function( $ ) {
	
	var orderReviewHtlm = $('#order_review').html();

	$( document ).ajaxComplete(function( event, xhr, settings ) {	

		if ( settings.url.indexOf('update_order_review') > 1 && orderReviewHtlm !== $('#order_review').html() ){

			$( document.body ).trigger( 'wcpbc_refresh_cart_fragments' );

			orderReviewHtlm = $('#order_review').html();
		}		
	});
	
});