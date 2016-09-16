<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WCPBC_Frontend_Pricing class.
 *
 * @class 		WCPBC_Frontend_Pricing
 * @version		1.6.0
 * @author 		oscargare
 */
class WCPBC_Frontend_Pricing {

	/**
	 * @var string
	 */
	private static $_meta_key_prefix;

	/**
	 * @var string
	 */
	private static $_currency;

	/**
	 * @var float
	 */
	private static $_exchange_rate;

	/**
	 * Hook actions and filters
	 *	 
	 * @param string $zone_id
	 */
	public static function init( $zone_id, $currency, $exchange_rate ) { 

		self::$_meta_key_prefix = '_' . $zone_id;
		self::$_currency = $currency;
		self::$_exchange_rate = $exchange_rate;

		add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata'), 10, 4 );
		add_filter( 'woocommerce_currency',  array( __CLASS__ , 'get_currency' ) );
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__ , 'get_variation_prices_hash' ), 10, 3 );		
		add_filter( 'woocommerce_get_catalog_ordering_args', array( __CLASS__ , 'get_catalog_ordering_args' ) );		
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__ , 'product_query_meta_query' ),10, 2 );
		add_filter( 'woocommerce_price_filter_meta_keys', array( __CLASS__ , 'price_filter_meta_keys' ) );
		add_filter( 'pre_transient_wc_products_onsale', array( __CLASS__ , 'product_ids_on_sale' ), 10, 2 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__ , 'package_rates' ), 10, 2 );
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__ , 'coupon_loaded' ) );	

		do_action( 'wc_price_based_country_frontend_princing_init' );
	}

	/**
	 * Return price meta data value
	 *	
	 * @param null|array|string $value     The value get_metadata() should return - a single metadata value or an array of values.
     * @param int               $object_id Object ID.
     * @param string            $meta_key  Meta key.
     * @param bool              $single    Whether to return only the first value of the specified $meta_key.
	 */
	public static function get_price_metadata( $meta_value, $object_id, $meta_key, $single ) {
		if ( $single && in_array( $meta_key, wcpbc_get_overwrite_meta_keys() ) ) {			
			
			// Remove filter to not going into a endless loop			
			remove_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata'), 10, 4 );
						
			// Check if price is correct
			if ( in_array( $meta_key, wcpbc_get_price_meta_keys() ) && 
				( ! ( $price_method = get_post_meta( $object_id, self::$_meta_key_prefix . '_price_method', true ) ) || $price_method == 'exchange_rate' ) &&
				( $meta_value = get_post_meta( $object_id, $meta_key , true ) ) &&				
				( get_post_meta( $object_id, self::$_meta_key_prefix . $meta_key , true ) != $meta_value * self::$_exchange_rate )
			) {
				// Set correct price
				update_post_meta( $object_id, self::$_meta_key_prefix . $meta_key , $meta_value * self::$_exchange_rate );
			}
			
			// Return value
			$meta_value = get_post_meta( $object_id, self::$_meta_key_prefix . $meta_key , true );			
			
			// Add filter			 
			add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata'), 10, 4 );
		}
		return $meta_value;
	}

	/**	 
	 * Get currency code.
	 *
	 * @param string $currency_code
	 * @return string
	 */	
	public static function get_currency( $currency_code ) {
		return self::$_currency;
	}

	/**
	 * Returns unique cache key to store variation child prices
	 * @param array $hash
	 * @param WC_Product $product
	 * @param bool $display
	 * @return array
	 */
	public static function get_variation_prices_hash( $price_hash, $product, $display ) {
		$price_hash[] = self::$_meta_key_prefix . self::$_currency . self::$_exchange_rate;
		return $price_hash;
	}
	
	/**
	 * Override _price metakey in array of arguments for ordering products based on the selected values.
	 * @param array $args	 
	 * @return array
	 */
	public static function get_catalog_ordering_args( $args ) {
		if ( isset( $args['meta_key'] ) && $args['meta_key'] == '_price' ) {
			$args['meta_key'] = self::$_meta_key_prefix . '_price';
		}		
		return $args;
	}
	
	/**
	 * Override _price metakey in meta query for filtering by price.
	 * @param array $args	 
	 * @return array
	 */
	public static function product_query_meta_query( $meta_query, $q ) {
		if ( isset( $meta_query['price_filter']['key'] ) && $meta_query['price_filter']['key'] == '_price' ) {
			$meta_query['price_filter']['key'] = self::$_meta_key_prefix . '_price';
		}		
		return $meta_query;
	}
	
	/**
	 * Override _price metakey for get filtered min and max price for current products.
	 * @param array $args	 
	 * @return array
	 */
	public static function price_filter_meta_keys( $meta_keys ) {
		return array( self::$_meta_key_prefix . '_price' );
	}
	
	/**
	 * Returns an array containing the IDs of the products that are on sale. Filter through get_transient
	 * @return array
	 */
	public static function product_ids_on_sale( $value, $transient = false ) {
		global $wpdb;
		
		$cache_key = 'wcpbc_products_onsale_' . self::$_meta_key_prefix;
			
		// Load from cache
		$product_ids_on_sale = get_transient( $cache_key );

		// Valid cache found
		if ( false !== $product_ids_on_sale ) {			
			return $product_ids_on_sale;
		}
		
		$on_sale_posts = $wpdb->get_results( $wpdb->prepare( "
			SELECT post.ID, post.post_parent FROM `{$wpdb->posts}` AS post
			LEFT JOIN `{$wpdb->postmeta}` AS meta ON post.ID = meta.post_id
			LEFT JOIN `{$wpdb->postmeta}` AS meta2 ON post.ID = meta2.post_id
			WHERE post.post_type IN ( 'product', 'product_variation' )
				AND post.post_status = 'publish'
				AND meta.meta_key = %s
				AND meta2.meta_key = %s
				AND CAST( meta.meta_value AS DECIMAL ) >= 0
				AND CAST( meta.meta_value AS CHAR ) != ''
				AND CAST( meta.meta_value AS DECIMAL ) = CAST( meta2.meta_value AS DECIMAL )
			GROUP BY post.ID
		", self::$_meta_key_prefix . '_sale_price', self::$_meta_key_prefix .'_price' ) );

		$product_ids_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

		set_transient( $cache_key, $product_ids_on_sale, DAY_IN_SECONDS * 30 );

		return $product_ids_on_sale;
	}
	
	/**
     * Apply exchange rate to shipping cost
     * @param array $rates
     * @param array $package cart items
     * @return float
     */
    public static function package_rates( $rates, $package ) {		
		
		if ( get_option( 'wc_price_based_country_shipping_exchange_rate', 'no') == 'yes' ) {
			
			foreach ( $rates as $rate ) {				
				$change = false;
			
				if ( ! isset( $rate->wcpbc_data ) ) {
					
					$rate->wcpbc_data = array(
						'exchange_rate' => self::$_exchange_rate,
						'orig_cost'		=> $rate->cost,
						'orig_taxes'	=> $rate->taxes
					);															
					$change = true;
					
				} elseif ( $rate->wcpbc_data['exchange_rate'] !== self::$_exchange_rate ) {				
					
					$rate->wcpbc_data['exchange_rate'] = self::$_exchange_rate;				
					$change = true;
					
				}	
				
				if ( $change ) {
					//Apply exchange rate
					$rate->cost = $rate->wcpbc_data['orig_cost'] * self::$_exchange_rate;
					//recalculate taxes
					foreach ( $rate->wcpbc_data['orig_taxes'] as $i => $tax ){
						$rate->taxes[$i] = ( $tax/$rate->wcpbc_data['orig_cost'] ) * $rate->cost;
					}
				}												
			}
			
		}		
		return $rates;				
	}
	
	/**
     * Apply exchange rate to coupon
     * @param WC_Coupon $coupon          
     */
    public static function coupon_loaded( $coupon ) {
		if ( 'exchange_rate' === get_post_meta( $coupon->id, 'zone_pricing_type', true ) ) {
			$coupon->coupon_amount = $coupon->coupon_amount * self::$_exchange_rate;
		}
	}
}