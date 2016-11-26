<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce 2Checkout Gateway by Krokedil Integration 
 * 
 *
 * @class    WCPBC_Gateway_Twocheckout
 * @version  1.6.3
 * @author   oscargare
 */
class WCPBC_Gateway_Twocheckout {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){				
		add_action( 'woocommerce_receipt_twocheckout', array( __CLASS__, 'receipt_page') );
	}
	
	/**
	 * Add 2Checkout integration code to receipt page
	 */
	public static function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );				
		
		if ( $order->get_order_currency() <> wcpbc_get_base_currency() ) {			
			
			$_input_currency_code = "<input type='hidden' name='currency_code' value='" . $order->get_order_currency() . "' />";

			wc_enqueue_js('				
				$("#twocheckout_payment_form").submit(function(event){				
					if ( $("#twocheckout_payment_form input[name=\'currency_code\']").size()>0 ) {
						$("#twocheckout_payment_form input[name=\'currency_code\']").val("' . $order->get_order_currency() . '");
					} else {						
						$("#twocheckout_payment_form").append("'. $_input_currency_code . '");					
					}				
				});
			');
		}		
	}
	
}

WCPBC_Gateway_Twocheckout::init();