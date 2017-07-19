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
 * @version  1.6.14
 * @author   oscargare
 */
class WCPBC_Integrations {		
	
	/**
	 * Add 3rd-Party plugins integrations
	 */
	public static function add_third_party_plugin_integrations(){
		
		$third_party_integrations = array(			
			 'AngellEYE_Gateway_Paypal'		=> dirname( __FILE__ ) . '/integrations/class-wcpbc-paypal-express-angelleye.php'
			,'Sitepress'					=> dirname( __FILE__ ) . '/integrations/class-wcpbc-admin-translation-management.php'
			,'WC_Gateway_Twocheckout'		=> dirname( __FILE__ ) . '/integrations/class-wcpbc-gateway-2checkout.php'
			,'WC_Product_Addons'			=> dirname( __FILE__ ) . '/integrations/class-wcpbc-product-addons-basic.php'
		);
		
		foreach ($third_party_integrations as $class => $integration_file ) {

			if( class_exists( $class ) ) {
				include_once( $integration_file );
			}
		}
	}
}
add_action( 'plugins_loaded', array( 'WCPBC_Integrations', 'add_third_party_plugin_integrations' ) );