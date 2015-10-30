<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Currency
 * 
 *
 * @class 		WCPBC_Currency
 * @version		1.4.2
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Currency {

	/**
	 * Return a a array with all currencies avaiables in WooCommerce with associate countries 
	 * @return array
	 */
	public static function get_currencies() {

		return array_unique( 
			apply_filters( 'wcpbc_currencies', 
				array(
					'AED' => array('AE'),
					'ARS' => array('AR'),
					'AUD' => array('AU', 'CC', 'CX', 'HM', 'KI', 'NF', 'NR', 'TV'),
					'BDT' => array('BD'),
					'BRL' => array('BR'),
					'BGN' => array('BG'),
					'CAD' => array('CA'),
					'CLP' => array('CL'),
					'CNY' => array('CN'),
					'COP' => array('CO'),
					'CZK' => array('CZ'),
					'DKK' => array('DK', 'FO', 'GL'),
					'DOP' => array('DO'),
					'EUR' => array('AD', 'AT', 'AX', 'BE', 'BL', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GF', 'GP', 'GR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MC', 'ME', 'MF', 'MQ', 'MT', 'NL', 'PM', 'PT', 'RE', 'SI', 'SK', 'SM', 'TF', 'VA', 'YT'),
					'HKD' => array('HK'),
					'HRK' => array('HR'),
					'HUF' => array('HU'),
					'ISK' => array('IS'),
					'IDR' => array('ID'),
					'INR' => array('IN'),
					'NPR' => array('NP'),
					'ILS' => array('IL'),
					'JPY' => array('JP'),
					'KIP' => array('LA'),
					'KRW' => array('KR'),
					'MYR' => array('MY'),
					'MXN' => array('MX'),
					'NGN' => array('NG'),
					'NOK' => array('BV', 'NO', 'SJ'),
					'NZD' => array('CK', 'NU', 'NZ', 'PN', 'TK'),
					'PYG' => array('PY'),
					'PHP' => array('PH'),
					'PLN' => array('PL'),
					'GBP' => array('GB', 'GG', 'GS', 'IM', 'JE'),
					'RON' => array('RO'),
					'RUB' => array('RU'),
					'SGD' => array('SG'),
					'ZAR' => array('ZA'),
					'SEK' => array('SE'),
					'CHF' => array('LI'),
					'TWD' => array('TW'),
					'THB' => array('TH'),
					'TRY' => array('TR'),
					'UAH' => array('UA'),
					'USD' => array('BQ', 'EC', 'FM', 'IO', 'MH', 'PW', 'TC', 'TL', 'US', 'VG'),
					'VND' => array('VN'),
					'EGP' => array('EG')
				)
			)
		);
	}

	/**
	 * Return base currency
	 */
	public static function get_base_currency() {
		return get_option( 'woocommerce_currency');
	}

	/**
	 * Return installed currencies
	 */
	public static function get_installed_currencies() {
		
		$installed_currencies = array();

		foreach (WCPBC()->get_regions() as $region) {
			
			if ( ! in_array($region['currency'], $installed_currencies) ) {
				$installed_currencies[] = $region['currency'];
			}
		}

		return array_unique( apply_filters( 'wcpbc_installed_currencies', $installed_currencies ) );
	}
}	

