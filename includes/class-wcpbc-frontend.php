<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Frontend
 *
 * WooCommerce Price Based Country Front-End
 *
 * @class 		WCPBC_Frontend
 * @version		1.6.8
 * @author 		oscargare
 */
class WCPBC_Frontend {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){						

		add_action( 'wp_footer', array( __CLASS__, 'test_store_message' ) );
		
		add_filter( 'woocommerce_customer_default_location_array', array( __CLASS__, 'test_default_location' ) );
		
		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'check_manual_country_widget'), 20 );		

		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'checkout_country_update'), 20 );		
		
		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'calculate_shipping_country_update'), 20 );		
		
		add_action( 'wc_ajax_wc_price_based_country_refresh_cart', array( __CLASS__, 'get_refreshed_fragments' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'load_scripts' ), 20 );			
	}	
		
	/**
	 * Print test store message 
	 */
	public static function test_store_message() {
		if ( get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {
			$country = WC()->countries->countries[ $test_country ];		
			echo '<p class="demo_store">' . sprintf( __( '%sPrice Based Country%s test mode enabled for testing %s. You should do tests on private browsing mode. Browse in private with %sFirefox%s, %sChrome%s and %sSafari%s', 'wc-price-based-country'), '<strong>', '</strong>', $country, '<a href="https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history">', '</a>', '<a href="https://support.google.com/chrome/answer/95464?hl=en">', '</a>', '<a href="https://support.apple.com/kb/ph19216?locale=en_US">', '</a>' ) . '</p>';
		}
	}
	
	/**
	 * Return Test country as default location
	 */
	public static function test_default_location( $location ) {	
		if ( get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {	
			$location = wc_format_country_state_string( get_option('wc_price_based_country_test_country') );		
		}
		return $location;
	}	

	/**
	 * Check manual country widget
	 */	
	public static function check_manual_country_widget(){				

		if ( isset( $_REQUEST['wcpbc-manual-country'] ) && $_REQUEST['wcpbc-manual-country'] ) {			
			
			//set WC country
			wcpbc_set_woocommerce_country( wc_clean( $_REQUEST['wcpbc-manual-country'] ) );			
			
			//trigger refresh mini cart
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_frontend_script' ), 5 );
		}
	}			
	
	/**
	 * Get a refreshed cart fragment.
	 */
	public static function get_refreshed_fragments() {

		if ( ! WC()->cart->is_empty() ) {
			WC()->cart->calculate_totals();
		}

		// Get mini cart
		ob_start();

		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();

		// Fragments and mini cart are returned
		$data = array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() )
		);

		wp_send_json( $data );
	}

	/**
	 * Add scripts
	 */
	public static function load_scripts( ) {

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-price-based-country-frontend', WCPBC()->plugin_url() . 'assets/js/wcpbc-frontend' . $suffix . '.js', array( 'woocommerce' ), WCPBC()->version, true );		
		wp_enqueue_script( 'wc-price-based-country-frontend' );

		if ( is_checkout() ) {		

			if ( version_compare( WC()->version, '2.4', '<' ) ) {
				$version = '-2.3';
			} else {
				$version = '';
			}

			wp_enqueue_script( 'wc-price-based-country-checkout', WCPBC()->plugin_url() . 'assets/js/wcpbc-checkout' . $version . $suffix . '.js', array( 'wc-checkout', 'wc-price-based-country-frontend' ), WCPBC()->version, true );
		}
	}

	/**
	 * Localize frontend script.
	 */
	public static function localize_frontend_script() {
		
		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		wp_localize_script( 'wc-price-based-country-frontend', 'wcpbc_frontend_params', array('refresh_cart' => 'true' ) );
	}

	/**
	 * Update WooCommerce Customer country on checkout
	 */
	public static function checkout_country_update( $post_data = array() ) {			
		
		if ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_order_review' == $_GET['wc-ajax'] ) {
			
			if ( isset( $_POST['country'] ) ) {
				wcpbc_set_wc_biling_country( $_POST['country'] );
			}
			
			if ( wc_ship_to_billing_address_only() ) {
				if ( isset( $_POST['country'] ) ) {
					WC()->customer->set_shipping_country( $_POST['country'] );
				}
			} else {
				if ( isset( $_POST['s_country'] ) ) {
					WC()->customer->set_shipping_country( $_POST['s_country'] );
				}
			}		
		}				
	}

	/**
	 * Update WooCommerce Customer country on calculate shipping
	 */
	public static function calculate_shipping_country_update(){

		if ( isset( $_POST['calc_shipping'] ) && $_POST['calc_shipping'] ) {
			if ( isset( $_POST['calc_shipping_country'] ) && $country = wc_clean( $_POST['calc_shipping_country'] ) ) {
				
				wcpbc_set_wc_biling_country( $country );	
				WC()->customer->set_shipping_country( $country );

			} else{
				WC()->customer->set_to_base();
				WC()->customer->set_shipping_to_base();
			}
		} 
	}	
}

WCPBC_Frontend::init();