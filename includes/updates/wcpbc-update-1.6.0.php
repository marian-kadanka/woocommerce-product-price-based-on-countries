<?php
/**
 * Update WCPBC to 1.6.0
 *
 * @author 		OscarGare
 * @category 	Admin
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

global $wpdb;

$regions = get_option( 'wc_price_based_country_regions', array() );

foreach ( $regions as $region_id => $region ) {
	
	/**
	 * Remove "_variable" prefix for prices meta keys 
	 */
	$wpdb->query( "update {$wpdb->postmeta} set meta_key = replace(meta_key, '_variable', '') where meta_key like '_{$region_id}_variable_%'" );
	
	/**
	 * Sync product prices 
	 */
	wcpbc_sync_exchange_rate_prices( $region_id, $region['exchange_rate'] );
}

/**
 * Update shipping option
 */
$wc_price_based_shipping_conversion = get_option('wc_price_based_shipping_conversion', 'no' );
update_option( 'wc_price_based_country_shipping_exchange_rate', $wc_price_based_shipping_conversion );

/**
 * Delete deprecated option
 */ 
 delete_option( 'wc_price_based_country_hide_ads' );
 delete_option( 'wc_price_based_shipping_conversion' );

?>