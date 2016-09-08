<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PayPal Express by AngellEYE Integration 
 * 
 *
 * @class    WCPBC_PayPal_Express_AngellEYE
 * @version  1.6.0 
 * @author   oscargare
 */
class WCPBC_PayPal_Express_AngellEYE {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){				
		add_filter( 'wc_price_based_country_is_new_country', array( __CLASS__, 'is_new_country' ) );
	}
	

	/**
	 * Is a new country
	 *
	 * @return bool
	 * @access public
	 */
	public static function is_new_country( $is_new ) {
		return $is_new && !self::is_paypal_express_action();
	}

	/**
	 * Is paypal express action
	 *
	 * @return boolean
	 */
	private static function is_paypal_express_action(){
		return ( isset( $_GET['pp_action'] ) && in_array( $_GET['pp_action'], array('revieworder', 'payaction') ) && defined('WOOCOMMERCE_CHECKOUT') );
	}
}

WCPBC_PayPal_Express_AngellEYE::init();