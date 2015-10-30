<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Admin
 *
 * WooCommerce Price Based Country Admin 
 *
 * @class 		WCPBC_Admin
 * @version		1.4.2
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Admin {

	/**
	 * Hook actions and filters
	 */
	public static function init(){
		
		add_action( 'init', array( __CLASS__, 'includes' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_script' ) );	

		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'settings_price_based_country' ) );					

		add_filter( 'woocommerce_currency',  array( __CLASS__, 'order_currency' ) );			
		
		add_action( 'current_screen', array( __CLASS__, 'conditional_includes' ) );
		
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {				

		include_once('class-wcpbc-admin-product-data.php');					

		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {		
			include_once('class-wcpbc-admin-translation-management.php');					
		}
	}

	/**
	 * Include admin files conditionally
	 */
	public static function conditional_includes() {
		$screen = get_current_screen();

		if ( $screen->id == 'dashboard' ) {			
			include( 'class-wcpbc-admin-dashboard.php' );
		}
	}
	
	/**
	 * Add Price Based Country settings tab to woocommerce settings
	 */
	public static function settings_price_based_country( $settings ) {

		$settings[] = include( 'settings/class-wc-settings-price-based-country.php' );

		return $settings;
	}	

	
	/**
	 * default currency in order
	 */
	public static function order_currency( $currency )	{

		global $post;

		if ($post && $post->post_type == 'shop_order' ) {
			
			global $theorder;
			if ( $theorder ) 
				return $theorder->order_currency;

		}
			
		return $currency;
	}	

	public static function load_admin_script( ) {	

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'wc-price-based-country-admin', WCPBC()->plugin_url() . 'assets/js/wcpbc-admin' . $suffix . '.js', array('jquery'), WCPBC()->version, true );		

	}

}

WCPBC_Admin::init();
