<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin Report.
 *  
 * @author      OscarGare
 * @category    Admin 
 * @version     1.6.1
 */
class WCPBC_Admin_Report {	
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){
		add_filter( 'woocommerce_reports_get_order_report_query', array( __CLASS__, 'reports_get_order_report_query' ) );
		add_action( 'admin_notices', array( __CLASS__, 'report_notice' ) );
	}
	
	/**
	 *
	 */
	public static function report_notice() {
		$screen = get_current_screen();
		$screen = $screen ? $screen->id : '';

		$base_currency = wcpbc_get_base_currency();
		
		if ( in_array( $screen, array( 'woocommerce_page_wc-reports', 'toplevel_page_wc-reports' ) ) && ( ! isset( $_GET['tab'] ) || $_GET['tab'] == 'orders' ) ) {			
			echo '<div class="notice notice-info"><p>';
			printf( __( 'Totals in different currency to %s has been calculate by following exchange rates: %s', 'wc-price-based-country' ), $base_currency, self::get_currency_rates_string() ) ; 
			if ( ! class_exists( 'WCPBC_Avanced_Currency_Options' ) ) {
				printf( __( '%sIf you need more accuracy install %sAdvanced Currency Options%s extension.', 'wc-price-based-country' ), '<br />', '<a href="http://www.pricebasedcountry.com/product/avanced-currency-options/?utm_source=wc_reports_page&utm_medium=banner&utm_campaign=Extend">', '</a>' );
			}
			echo '</p></div>';				
		}
	}
	
	/**
	 * Return currency exchange rates
	 * @return array
	 */
	private static function get_currency_rates() {				
		$currency_rates = array();			
		
		$base_currency = wcpbc_get_base_currency();						
		
		foreach ( WCPBC()->get_regions() as $zone ) {
			if ( $zone['currency'] != $base_currency && $zone['exchange_rate'] ) {
				$currency_rates[ $zone['currency'] ] = $zone['exchange_rate'];
			}
		}			
		
		return $currency_rates;
	}
	
	/**
	 * Return exchange rates as string
	 * @return string
	 */
	private static function get_currency_rates_string() {				
		$base_currency = wcpbc_get_base_currency();
		$rates = self::get_currency_rates();
		$srates = array();
		foreach ( $rates as $currency => $rate ) {
			$srates[] = "$base_currency/$currency $rate";
		}		
		return '<strong>' . implode( '</strong> - <strong>', $srates ) . '</strong>';
	}
	
	/**
	 * Return a SELECT CASE expression for order item value
	 * @return string
	 */
	private static function caseex( $field, $currency_rates ) {
		
		$case_ex = ' CASE meta__order_currency.meta_value ';
		foreach ( $currency_rates as $currency => $rate ) {
			$case_ex .= "WHEN '{$currency}' THEN ( {$field} / ({$rate})) ";
		}
		$case_ex .= "ELSE {$field} END ";
		
		return $case_ex;
	}
	
	/**
	 * Replace report line item totals amount in report query	 
	 */
	public static function reports_get_order_report_query( $query ) {
		global $wpdb;
		
		$currency_rates = self::get_currency_rates();
		if ( $currency_rates ) {
				
			$change = false;
		
			$fields = array(
				' meta__order_total.meta_value', 
				' meta__order_shipping.meta_value', 
				' meta__order_tax.meta_value', 
				' meta__order_shipping_tax.meta_value', 
				' meta__refund_amount.meta_value ',
				' order_item_meta_discount_amount.meta_value',
				' order_item_meta__line_total.meta_value',
				'parent_meta__order_total.meta_value',
				'parent_meta__order_shipping.meta_value',
				'parent_meta__order_tax.meta_value',
				'parent_meta__order_shipping_tax.meta_value'
			);
			
			foreach ( $fields as $field ) {
				
				if ( strpos( $query['select'], $field ) !== FALSE ) {
					
					$case_ex = self::caseex( $field, $currency_rates );
					$query['select'] = str_replace( $field, $case_ex, $query['select'] );
					$change = true;
				}
			}
			
			if ( $change ) {
				$query['join'] .= " INNER JOIN {$wpdb->postmeta} AS meta__order_currency ON ( posts.ID = meta__order_currency.post_id AND meta__order_currency.meta_key = '_order_currency' ) ";
			}
		}
		
		return $query;		
	}
	 
}

WCPBC_Admin_Report::init();