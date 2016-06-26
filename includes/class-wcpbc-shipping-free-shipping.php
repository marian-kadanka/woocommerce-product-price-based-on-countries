<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PBC Free Shipping Method.
 *
 * Extend WooCommerce Free Shipping Method to apply exchange rate to min_amount
 * This class is here for backwards commpatility for methods existing before zones existed.
 *
 * @class   WCPBC_Shipping_Free_Shipping
 * @version 1.0.0 
 * @author  oscargare
 */
class WCPBC_Shipping_Free_Shipping extends WC_Shipping_Legacy_Free_Shipping {
	
	/**
	 * get_option function.
	 *
	 * Gets and option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param  string $key
	 * @param  mixed  $empty_value
	 * @return mixed  The value specified for the option or a default value for the option.
	 */
	public function get_option( $key, $empty_value = null ) {
		
		$value = parent::get_option( $key, $empty_value );
		
		if ( $key === 'min_amount' && $value > 0 && WCPBC()->customer->exchange_rate && WCPBC()->customer->exchange_rate != '1' ) {
			$value = $value * WCPBC()->customer->exchange_rate;
		}
		
		return $value;
	}
}