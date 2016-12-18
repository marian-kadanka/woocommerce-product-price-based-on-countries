<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PayPal Express by AngellEYE Integration 
 * 
 *
 * @class    WCPBC_PayPal_Express_AngellEYE
 * @version  1.6.4
 * @author   oscargare
 */
class WCPBC_PayPal_Express_AngellEYE {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){					
		add_action( 'woocommerce_init', array( __CLASS__, 'check_paypal_express_action' ) );		
	}
	
	/**
	 * Check if is a PayPal express Action
	 *
	 * @access public
	 */	
	public static function check_paypal_express_action() {

		if ( isset( $_GET['pp_action'] ) ) {
			
			if ( $_GET['pp_action'] == 'expresscheckout' && isset( $_GET['wc-api'] ) && $_GET['wc-api'] == 'WC_Gateway_PayPal_Express_AngellEYE' ) {			
				WC()->session->set( 'paypal_express_checkout_country', wcpbc_get_woocommerce_country() );		
			}			

			if ( $_GET['pp_action'] == 'revieworder' && isset( $_GET['token'] ) && isset( $_GET['PayerID'] ) && $pp_country = WC()->session->get( 'paypal_express_checkout_country') ) {				

				wcpbc_set_woocommerce_country( $pp_country );	

				add_action( 'parse_request', array( __CLASS__, 'override_paypal_express_country' ), 100);
			}
		}				
	}

	/**
	 * Override paypal country
	 *
	 * @access public
	 */	
	public static function override_paypal_express_country() {
		wcpbc_set_woocommerce_country( WC()->session->get( 'paypal_express_checkout_country') );	
	}
	
}

WCPBC_PayPal_Express_AngellEYE::init();