<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 *  WooCommerce Bulk Discount Integration 
 * 
 *
 * @class    WCPBC_Bulk_Discount_t4m
 * @version  1.5.13 
 * @author   oscargare
 */
class WCPBC_Bulk_Discount_t4m{
	
	/**
	 * @var array
	 */
	private static $cart_products_hash = array();
	
	/**
	 * @var array
	 */
	private static $cart_original_prices = array();
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'store_cart_original_prices' ), 5 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'add_product_hash' ), 100 );	
		add_filter( 'wc_price_based_country_get_product_price', array( __CLASS__ , 'is_adjusted_product_in_cart'), 10, 2 );	
	}
	
	/**
	 * Add product original price to array
	 */
	public static function store_cart_original_prices( $cart ) {				

		self::$cart_original_prices = array();
		
		foreach ( $cart->cart_contents as $cart_item_key => $values ) {
			self::$cart_original_prices[$cart_item_key] = $values['data']->price;
		}
	}
		
	/**
	 * Add product hash to array
	 */
	public static function add_product_hash( $cart ) {

		foreach ( $cart->cart_contents as $cart_item_key => $values ) {
			if ( isset( self::$cart_original_prices[$cart_item_key] ) && self::$cart_original_prices[$cart_item_key] !== $values['data']->price ) {
					self::$cart_products_hash[] = spl_object_hash( WC()->cart->cart_contents[$cart_item_key]['data'] );			
			}
		}		
	}
	
	/**
	 * Is product in cart with adjusted price
	 */
	public static function is_adjusted_product_in_cart( $is_adjusted_product, $product ) {

		return ! in_array( spl_object_hash( $product ), self::$cart_products_hash );
	}
	
}

WCPBC_Bulk_Discount_t4m::init();