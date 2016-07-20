<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Integrations 
 *
 * Handle integrations between PBC and 3rd-Party plugins
 *
 * @class    WCPBC_Integrations
 * @version  1.5.13 
 * @author   oscargare
 */
class WCPBC_Integrations {		
	
	/**
	 * Add 3rd-Party plugins integrations
	 */
	public static function add_third_party_plugin_integrations(){
		
		$third_party_integrations = array(
			'Woo_Bulk_Discount_Plugin_t4m' => 'integrations/class-wcpbc-bulk-discount-t4m.php'		
		);
		
		foreach ($third_party_integrations as $class => $integration_file ) {
			if( class_exists( $class ) ) {
				include_once( $integration_file );
			}
		}
	}
}
add_action( 'plugins_loaded', array( 'WCPBC_Integrations', 'add_third_party_plugin_integrations' ) );