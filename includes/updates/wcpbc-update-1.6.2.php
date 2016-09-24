<?php
/**
 * Update WCPBC to 1.6.2
 *
 * @author 		OscarGare
 * @category 	Admin
 * @version     1.6.2
 */

if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

global $wpdb;

$regions = get_option( 'wc_price_based_country_regions', array() );

foreach ( $regions as $region_id => $region ) {
	

	/**
	 * Get variable products without price
	 */
	$products = get_posts( array(
			'numberposts' => -1,
			'post_type'   => 'product',
			'meta_query'    => array(
				'relation'  => 'AND',
				array(
                    'key'     => "_{$region_id}_price_method",
                    'value'   => 'nothing',
                    'compare'   => '='
	            ),
	            array(
	            	'key'     => "_{$region_id}_price",                    
                    'compare' => 'NOT EXISTS'
	            )
			)			
		)  );
	
	foreach ( $products as $product ) {
		wcpbc_zone_variable_product_sync( $region_id, $product->ID );
	}	
}


?>