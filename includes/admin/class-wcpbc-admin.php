<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Admin
 *
 * WooCommerce Price Based Country Admin 
 *
 * @class 		WCPBC_Admin
 * @version		1.6.6
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Admin {

	/**
	 * Hook actions and filters
	 */
	public static function init(){
		
		add_action( 'init', array( __CLASS__, 'includes' ) );
		add_action( 'init', array( __CLASS__, 'about_hooks' ) );						
		add_action( 'admin_init', array( __CLASS__, 'admin_redirects' ) );		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );	
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );	
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_options' ) );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_options_save' ) );		
		add_action( 'woocommerce_system_status_report', array( __CLASS__, 'system_status_report' ) );		
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'settings_price_based_country' ) );					
		add_filter( 'woocommerce_paypal_supported_currencies', array( __CLASS__, 'paypal_supported_currencies' ) );						
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {				

		include_once('class-wcpbc-admin-product-data.php');					
		include_once('class-wcpbc-admin-report.php');
		
		do_action('wc_price_based_country_admin_init');
	}
	
	/**
	 * Add hooks to manage about page
	 */
	public static function about_hooks() {
		if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'wcpbc-about' ) {
			add_action( 'admin_menu', array( __CLASS__, 'create_about_page' ) );			
		}
	}		
	
	/**
	 * Handle redirects to welcome page after install and updates.
	 *
	 * Transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public static function admin_redirects() {
		if ( ! get_transient( '_wcpbc_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_wcpbc_activation_redirect' );

		if ( ( ! empty( $_GET['page'] ) && $_GET['page'] === 'wcpbc-about' ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		
		wp_safe_redirect( admin_url( 'index.php?page=wcpbc-about' ) );
		exit;

	}
	
	/**
	 * Add Price Based Country settings tab to woocommerce settings
	 */
	public static function settings_price_based_country( $settings ) {

		$settings[] = include( 'settings/class-wc-settings-price-based-country.php' );

		return $settings;
	}			
	
	/**
	 * PayPal supported currencies
	 *
	 * @since 1.6.4
	 */
	public static function paypal_supported_currencies( $paypal_currencies ){

		$base_currency = wcpbc_get_base_currency();

		if ( ! in_array( $base_currency, $paypal_currencies ) ) {
			foreach ( WCPBC()->get_regions() as $zone ) {
				if ( in_array( $zone['currency'], $paypal_currencies ) ) {
					$paypal_currencies[] = $base_currency;
					break;
				}
			}	
		}
		
		return $paypal_currencies;
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.6
	 */
	public static function admin_styles() {
		// Register admin styles
		wp_enqueue_style( 'wc-price-based-country-admin-styles', WCPBC()->plugin_url() . '/assets/css/admin.css', array(), WCPBC()->version );
	}
	
	/**
	 * Enqueue scripts.	 
	 */	
	public static function admin_scripts( ) {	

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		// Register scripts		
		wp_enqueue_script( 'wc-price-based-country-admin', WCPBC()->plugin_url() . 'assets/js/wcpbc-admin' . $suffix . '.js', array('jquery'), WCPBC()->version, true );		
	}
	
	
	/**
	 * Display coupon amount options.
	 *
	 * @since 1.6
	 */
	public static function coupon_options(){
		woocommerce_wp_checkbox( array( 'id' => 'zone_pricing_type', 'cbvalue' => 'exchange_rate', 'label' => __( 'Calculate amount by exchange rate', 'wc-price-based-country' ), 'description' => __( 'Check this box if for the countries defined in zone pricing the coupon amount should be calculated using exchange rate.', 'wc-price-based-country' ) ) );	
	}
	
	/**
	 * Save coupon amount options.
	 *
	 * @since 1.6
	 */
	public static function coupon_options_save( $post_id ){
		$type = get_post_meta( $post_id, 'discount_type' , true );
		$zone_pricing_type = in_array( $type, array( 'fixed_cart', 'fixed_product' ) ) && isset( $_POST['zone_pricing_type'] ) ? 'exchange_rate' : 'nothig';
		update_post_meta( $post_id, 'zone_pricing_type', $zone_pricing_type ) ;
	}
	
	/**
	 * Add plugin info to WooCommerce System Status Report
	 *
	 * @since 1.6.3
	 */
	public static function system_status_report(){
		include_once( 'views/html-admin-page-status-report.php' );
	}
		
	/**
	 * Display the welcome/about page after successfully upgrading to the latest version.
	 *
	 * @since 1.5
	 */
	public static function create_about_page() {
		$about_page = add_dashboard_page( __( 'About Price Based on Country', 'wc-price-based-country' ), __( 'About Price Based on Country', 'wc-price-based-country' ), 'manage_options', 'wcpbc-about', array( __CLASS__, 'about_screen' ) );		
		add_action( 'admin_head', array( __CLASS__, 'remove_about_page_link' ) );
	}
	
	/**
	 * Output the about screen.
	 */
	public static function about_screen() {		
		include_once( 'views/html-about.php' );
	}
	
	/**
	 * Remove dashboard about page link.
	 */
	public static function remove_about_page_link() {
		remove_submenu_page( 'index.php', 'wcpbc-about' );
	}			

}

WCPBC_Admin::init();
