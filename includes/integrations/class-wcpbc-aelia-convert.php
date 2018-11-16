<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCPBC_Aelia_Convert {

	static $rates;

	/**
	 * Hook actions and filters
	 */
	public static function init() {
		self::$rates = array_column( WCPBC()->get_regions(), 'exchange_rate', 'currency' );
		add_filter( 'wc_aelia_cs_convert', array( __CLASS__ , 'support_wc_aelia_cs_convert' ), 10, 3 );
	}

	/**
     * Basic support for 'wc_aelia_cs_convert' filter hook
     */
	public static function support_wc_aelia_cs_convert( $value, $from_currency, $to_currency ) {

		if ( $value === 0 || $from_currency !== wcpbc_get_base_currency() || ! array_key_exists( $to_currency, self::$rates ) || $from_currency === $to_currency ) {
			return $value;
		}
		else {
			return $value * self::$rates[$to_currency];
		}
	}

}

WCPBC_Aelia_Convert::init();
