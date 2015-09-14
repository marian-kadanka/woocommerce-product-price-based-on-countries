<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Translation_Management' ) ) :

/**
 * WCPBC_Translation_Management
 *
 * Translation Management for WooCommerce Price Based Country
 *
 * @class 		WCPBC_Tranlation_Management
 * @version		1.3.6
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Translation_Management {

	/**
	 * Get Custom plugin fields
	 */
	public static function custom_fields(){

		$custom_fields = array();

		$region_keys = array_keys( WCPBC()->get_regions() );

		foreach ( $region_keys as $key ) {

			foreach ( array( '_price', '_regular_price','_sale_price', '_price_method' ) as $field ) {

				$custom_fields[] = '_' . $key . $field;
				$custom_fields[] = '_' . $key . '_variable' . $field;

				if ( $field !== '_price_method' ) {
					foreach ( array('min', 'max') as $min_or_max ) {
						$custom_fields[] = '_' . $key . '_' . $min_or_max . $field . '_variation_id';	
					}					
				}
			}
		}

		return $custom_fields;
	}

	/**
	 *  Add customs fields to WPML Translation management 
	 */
	public static function add_custom_fields() {		
		
		global $iclTranslationManagement;		

		$change = false;

		foreach ( self::custom_fields() as $field) {
			
			if ( ! isset( $iclTranslationManagement->settings['custom_fields_translation'][$field] ) ) {
							
				$iclTranslationManagement->settings['custom_fields_translation'][$field] = 1;	//copy

				$change = true;
			}
		}

		if ( $change ) {
			$iclTranslationManagement->save_settings();
		}
	}

	/**
	 * Enqueue scripts
	 */
	public static function wpml_scripts() {

		global $woocommerce_wpml, $pagenow;

		if ( isset($woocommerce_wpml) &&  is_object( $woocommerce_wpml ) && get_class($woocommerce_wpml) == 'woocommerce_wpml' ) {

			if( ($pagenow == 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) == 'product' && !$woocommerce_wpml->products->is_original_product($_GET['post']) ) ||
            	($pagenow == 'post-new.php' && isset($_GET['source_lang']) && isset($_GET['post_type']) && $_GET['post_type'] == 'product') && 
            	! $woocommerce_wpml->settings['trnsl_interface'] ) {

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	            wp_register_script('wcpbc-lock-fields', WCPBC()->plugin_url() . 'assets/js/wcpbc-admin-lock-fields' . $suffix . '.js', array('jquery'), WCPBC()->version, true );

	        	wp_localize_script( 'wcpbc-lock-fields', 'wcpbc_regions_keys', array_keys( WCPBC()->get_regions() ) );

        		wp_enqueue_script( 'wcpbc-lock-fields' );

        	}

		}
	}		

}

add_action( 'init', 'WCPBC_Translation_Management::add_custom_fields', 1510 );
add_action( 'admin_enqueue_scripts', 'WCPBC_Translation_Management::wpml_scripts' );

endif;

?>
