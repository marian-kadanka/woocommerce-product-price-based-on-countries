<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Frontend
 *
 * WooCommerce Price Based Country Front-End
 *
 * @class 		WCPBC_Frontend
 * @version		1.5.9
 * @author 		oscargare
 */
class WCPBC_Frontend {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){						
		
		add_action( 'woocommerce_init', array( __CLASS__ , 'check_test_mode'), 10 );
		
		add_action( 'woocommerce_init', array( __CLASS__ , 'check_manual_country_widget'), 20 );		

		add_action( 'woocommerce_init', array( __CLASS__ , 'checkout_country_update'), 20 );		
		
		add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'load_scripts' ) );			
	}	
	
	/**
	 * Check test mode
	 */	
	public static function check_test_mode(){
		
		if ( get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {
			
			wcpbc_set_woocommerce_country( $test_country );
			
			/* add test store message */
			add_action( 'wp_footer', array( __CLASS__, 'test_store_message' ) );
		}		
	}
	
	/**
	 * Return test store message 
	 */
	public static function test_store_message() {
		echo '<p class="demo_store">' . __( 'This is a demo store for testing purposes', 'wc-price-based-country') . '</p>';
	}

	/**
	 * Check manual country widget
	 */	
	public static function check_manual_country_widget(){
				
		if ( isset( $_POST['wcpbc-manual-country'] ) && $_POST['wcpbc-manual-country'] ) {			
			
			wcpbc_set_woocommerce_country( wc_clean( $_POST['wcpbc-manual-country'] ) );			

			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_frontend_script' ), 5 );
		}
	}			
	
	/**
	 * Add scripts
	 */
	public static function load_scripts( ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-price-based-country-frontend', WCPBC()->plugin_url() . 'assets/js/wcpbc-frontend' . $suffix . '.js', array( 'wc-cart-fragments' ), WCPBC()->version, true );		
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
			
		wp_localize_script( 'wc-price-based-country-frontend', 'wcpbc_frontend_params', array('refresh_cart' => 'true' ) );
	}

	/**
	 * Update WCPBC Customer country when order review is update
	 */
	public static function checkout_country_update( $post_data = array() ) {			
		
		if ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_order_review' == $_GET['wc-ajax'] ) {
			
			if ( isset( $_POST['country'] ) ) {
				WC()->customer->set_country( $_POST['country'] );
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
     * Replace WooCommerce Free Shipping Method
     */
    public static function overwrite_free_shipping_class( $shipping_methods ) {
       	
       	include_once( 'class-wcpbc-shipping-free-shipping.php' );

        $shipping_methods['legacy_free_shipping'] = 'WCPBC_Shipping_Free_Shipping';       	
       	
        return $shipping_methods;
    }
}

WCPBC_Frontend::init();