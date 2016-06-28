<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WCPBC_Product_Price class.
 *
 * @class 		WCPBC_Product_Price
 * @version		1.5.11
 * @author 		oscargare
 */
class WCPBC_Product_Price {
	
	private static $current_product_data = null;

	/**
	 * Hook actions and filters
	 */
	public static function init(){
		global $wp_version;
		
		/* Currency */
		add_filter( 'woocommerce_currency',  array( __CLASS__ , 'currency' ) );

		/* WC_Product */
		add_filter( 'woocommerce_get_price', array( __CLASS__ , 'get_price' ), 10, 2 );

		add_filter( 'woocommerce_get_regular_price', array( __CLASS__ , 'get_regular_price') , 10, 2 );

		add_filter( 'woocommerce_get_sale_price', array( __CLASS__ , 'get_sale_price') , 10, 2 );								

		add_filter( 'woocommerce_adjust_non_base_location_prices', array( __CLASS__, 'adjust_non_base_location_prices' ) );

		/* WC_Product_Variable */
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__ , 'get_variation_prices_hash' ), 10, 3 );
		
		add_filter( 'woocommerce_variation_prices_price', array( __CLASS__ , 'get_price' ), 10, 3 );

		add_filter( 'woocommerce_variation_prices_regular_price', array( __CLASS__ , 'get_regular_price' ), 10, 3 );

		add_filter( 'woocommerce_variation_prices_sale_price', array( __CLASS__ , 'get_sale_price' ), 10, 3 );
		
		/* WC_Product_Variation */		
		add_filter( 'woocommerce_get_variation_price', array( __CLASS__ , 'get_variation_price' ), 10, 4 );		

		add_filter( 'woocommerce_get_variation_regular_price', array( __CLASS__ , 'get_variation_regular_price' ), 10, 4 );	

		add_filter( 'woocommerce_get_variation_sale_price', array( __CLASS__ , 'get_variation_sale_price' ), 10, 4 );									
		
		/* Flat rate shipping */
		add_action( 'woocommerce_flat_rate_shipping_add_rate', array( __CLASS__ , 'flat_rate_shipping_conversion' ), 30, 3 );

		/* Free shipping min amount */		
		add_action( 'init', array( __CLASS__ , 'init_free_shipping_min_amount' ) );		

		/* Widget Price Filter */		
		add_filter( 'woocommerce_price_filter_widget_min_amount', array( __CLASS__ , 'price_filter_widget_min_amount' ) );

		add_filter( 'woocommerce_price_filter_widget_max_amount', array( __CLASS__ , 'price_filter_widget_max_amount' ) );				

		add_action ( 'woocommerce_product_query', array( __CLASS__ , 'price_filter_product_query' ), 10, 2 );
		
		/* Products on sale */
		add_filter( 'pre_transient_wc_products_onsale', array( __CLASS__ , 'product_ids_on_sale' ), 10, ( version_compare( $wp_version, '4.4', '<' ) ? 1 : 2 ) );

		/* Legacy for WooCommerce < 2.6 */
		add_action( 'init', array( __CLASS__ , 'init_legacy_hooks' ) );		
	}
	
	/**
	 * Return currency
	 * @return string currency
	 */
	public static function currency( $currency ) {

		$_currency = $currency;

		if ( WCPBC()->customer && WCPBC()->customer->currency ) {
			$_currency = WCPBC()->customer->currency;
		}

		return $_currency;
	}		
	
	/**
	 * Returns WCPBC price.
	 * @return string price
	 */
	private static function wcpbc_get_price( $meta_key_preffix, $price_type, $post_id, $price ){
		
		$wcpbc_price = $price;
		
		$price_method = get_post_meta( $post_id, $meta_key_preffix . '_price_method', true ); 

		if ( $price_method === 'manual') {

			$wcpbc_price = get_post_meta( $post_id, $meta_key_preffix . $price_type, true );

		} elseif ( WCPBC()->customer->exchange_rate && !empty( $price ) ) {

				$wcpbc_price = ( $price * WCPBC()->customer->exchange_rate );							
		}
		
		return $wcpbc_price;
	}

	/**
	 * Returns the product price.
	 * @param decimal $price
	 * @param WC_Product $product
	 * @param string $price_type
	 * @return string
	 */
	public static function get_product_price( $price, $product, $price_type ) {	
		
		$wcpbc_price = $price;					
		
		if ( WCPBC()->customer->group_key && apply_filters( 'wc_price_based_country_get_product_price', true, $product ) ) {

			$meta_key_prefix = '_' . WCPBC()->customer->group_key;

			if ( isset( $product->variation_id ) ) {
				
				$post_id = $product->variation_id;	

				$meta_key_prefix .= '_variable';
				
			} else {
				$post_id = $product->id; 
			}
			
			$wcpbc_price = self::wcpbc_get_price( $meta_key_prefix, $price_type, $post_id, $price );			
		}

		return $wcpbc_price;
	}

	/**
	 * Returns the product's price.
	 * @return string price
	 */
	public static function get_price( $price, $product, $parent = NULL) {	
		/**
		 * Store product data for later use in filter "woocommerce_adjust_non_base_location_prices", 
		 * $product must be a parameter in this filter
		 */		
		self::$current_product_data = array(
			'product_id' 		=> isset( $product->variation_id ) ? $product->variation_id : $product->id,
			'variable_prefix' 	=> isset( $product->variation_id ) ? '_variable' : ''
		);

		return self::get_product_price( $price, $product, '_price');
	}

	/**
	 * Returns the product's regular price.
	 * @return string price
	 */
	public static function get_regular_price($price, $product, $parent = NULL) {					
		return self::get_product_price( $price, $product, '_regular_price');
	}	
	
	/**
	 * Returns the product's sale price
	 * @return string price
	 */
	public static function get_sale_price( $price, $product, $parent = NULL ) {			
		return self::get_product_price( $price, $product, '_sale_price');
	}
	
	/**
     * Stop base taxes being taken off when dealing with out of base locations
     * @param bool $adjust    
     * @return bool
     */   
    public static function adjust_non_base_location_prices( $adjust ){        
        if ( $region_key = WCPBC()->customer->group_key ) {        	       
            $adjust = ( get_post_meta( self::$current_product_data['product_id'] , '_' . $region_key . self::$current_product_data['variable_prefix'] . '_price_method', true ) !== 'manual' );
        }   
        return $adjust;
    }

	/**
	 * Returns unique cache key to store variation child prices
	 * @param array $hash
	 * @param WC_Product $product
	 * @param  bool $display
	 * @return array
	 */
	public static function get_variation_prices_hash( $hash, $product, $display ) {			
		
		if ( WCPBC()->customer->group_key ) {

			$hash[] = 'wcpbc_region_key_' . WCPBC()->customer->group_key;
			$hash[] = 'wcpbc_exchange_rate_' . WCPBC()->customer->exchange_rate;
		}

		return $hash;
	}
	
	/**
	 * Get the min or max variation active price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */		
	public static function get_variation_price( $price, $product, $min_or_max, $display, $price_type = '_price' ) {		
		
		$wcpbc_price = $price;	
		
		if ( WCPBC()->customer->group_key ) {

			$variation_id = get_post_meta( $product->id, '_' . WCPBC()->customer->group_key . '_' . $min_or_max . $price_type . '_variation_id', true );

			if ( $variation_id ) {

				$variation = $product->get_child( $variation_id );

				if ( $variation) {

					$price_function = 'get' . $price_type;

					$wcpbc_price = $variation->$price_function();

					if ( $display ) {
						$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
						$wcpbc_price      = $tax_display_mode == 'incl' ? $variation->get_price_including_tax( 1, $wcpbc_price ) : $variation->get_price_excluding_tax( 1, $wcpbc_price );
					}
				}

			} elseif( $wcpbc_price && WCPBC()->customer->exchange_rate && $price_type !== '_price') {

				$wcpbc_price = $wcpbc_price * WCPBC()->customer->exchange_rate;
			}		
		}

		return $wcpbc_price;
	}	
	
	/**
	 * Get the min or max variation regular price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */
	public static function get_variation_regular_price( $price, $product, $min_or_max, $display ) {		
		
		return self::get_variation_price( $price, $product, $min_or_max, $display, '_regular_price' );
	}		

	/**
	 * Get the min or max variation sale price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */
	public static function get_variation_sale_price( $price, $product, $min_or_max, $display ) {		
		
		return self::get_variation_price( $price, $product, $min_or_max, $display, '_sale_price' );
	}	
	
	/**
	 * Returns the price in html format for product grouped	 	
	 * @param string $price
	 * @param WC_Product_Grouped $product
	 * @return string
	 */
	public static function get_grouped_price_html( $price, $product ) {
		
		if ( get_class( $product ) == 'WC_Product_Grouped' && WCPBC()->customer->group_key ) {	
		
			$price = '';
			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
			$child_prices     = array();
			$meta_key_preffix = '_' . WCPBC()->customer->group_key;
			
			foreach ( $product->get_children() as $child_id ){			
				$child_prices[] = self::wcpbc_get_price( $meta_key_preffix, '_price', $child_id, get_post_meta( $child_id, '_price', true ) );
			}			 

			$child_prices     = array_unique( $child_prices );
			$get_price_method = 'get_price_' . $tax_display_mode . 'uding_tax';

			if ( ! empty( $child_prices ) ) {
				$min_price = min( $child_prices );
				$max_price = max( $child_prices );
			} else {
				$min_price = '';
				$max_price = '';
			}

			if ( $min_price ) {
				if ( $min_price == $max_price ) {
					$display_price = wc_price( $product->$get_price_method( 1, $min_price ) );
				} else {
					$from          = wc_price( $product->$get_price_method( 1, $min_price ) );
					$to            = wc_price( $product->$get_price_method( 1, $max_price ) );
					$display_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), $from, $to );
				}

				$price .= $display_price . $product->get_price_suffix();			
			}
		}
		
		return $price;		
	}
	
	/**
     * Apply currency conversion to flat rate shipping method
     * @param WC_Shipping_Flat_Rate $method
     * @param array $rate     
     * @param array $packages          
     */
    public static function flat_rate_shipping_conversion( $method, $rate, $packages = array() ) {       

        if ( WCPBC()->customer->exchange_rate && WCPBC()->customer->exchange_rate != '1' && get_option('wc_price_based_shipping_conversion') === 'yes' ) {           
        	
            foreach( $method->rates as $index => $rate ) {
				if ( in_array( $rate->method_id, array( 'flat_rate', 'legacy_flat_rate', 'international_delivery', 'legacy_international_delivery' ) ) ) {
					$method->rates[$index]->cost = $method->rates[$index]->cost * WCPBC()->customer->exchange_rate;                                                   					
				}
            }                       
        }       
    }

    /**
     * Init free shipping hooks
     */
    public static function init_free_shipping_min_amount() {
    	global $wpdb;

    	add_filter( 'option_woocommerce_free_shipping_settings', array( __CLASS__ , 'free_shipping_min_amount' ), 10, 2 );
						
		if ( version_compare( WC()->version, '2.6', '>=' ) ) {
			$free_shippings =  $wpdb->get_results( "SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE is_enabled = 1 AND method_id = 'free_shipping';" );
			foreach ( $free_shippings as $free_shipping ) {
				add_filter( 'option_woocommerce_free_shipping_'. $free_shipping->instance_id . '_settings', array( __CLASS__ , 'free_shipping_min_amount' ), 10, 2 );
			}
		}
    }	

    /**
     * Apply currency conversion to free shipping min amount
     * @param array $value
     * @param string $option          
     */
    public static function free_shipping_min_amount( $value, $option ) {		

		if ( WCPBC()->customer->exchange_rate && WCPBC()->customer->exchange_rate != '1' && isset( $value['min_amount'] ) && $value['min_amount'] > 0 ) {
			$value['min_amount'] = $value['min_amount'] * WCPBC()->customer->exchange_rate;
		}
		
		return $value;
	}
	
	/**
	 * Return matched produts where price between min and max
	 *
	 * @param array $matched_products_query
	 * @param int $min 
	 * @param int $max
	 * @return array
	 */
	public static function price_filter_results( $matched_products_query, $min, $max ){		
		
		if ( WCPBC()->customer->group_key && WCPBC()->customer->exchange_rate )	{
			
			global $wpdb;

			$_price_method = '_' . WCPBC()->customer->group_key . '_price_method';
			$_price = '_' . WCPBC()->customer->group_key . '_price';

			$sql = $wpdb->prepare('SELECT DISTINCT ID, post_parent, post_type FROM %1$s 
					INNER JOIN %2$s wc_price ON ID = wc_price.post_id and wc_price.meta_key = "_price" AND wc_price.meta_value != ""
					LEFT JOIN %2$s price_method ON ID = price_method.post_id and price_method.meta_key = "%3$s"
					LEFT JOIN %2$s price ON ID = price.post_id and price.meta_key = "%4$s" AND price.meta_value != ""
					WHERE post_type IN ( "product", "product_variation" )
					AND post_status = "publish"					
					AND IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", wc_price.meta_value * %5$s, price.meta_value + 0) BETWEEN %6$d AND %7$d'
			, $wpdb->posts, $wpdb->postmeta, $_price_method, $_price, WCPBC()->customer->exchange_rate, $min, $max);

			$matched_products_query = $wpdb->get_results( $sql, OBJECT_K );						
		}

		return $matched_products_query;
	}

	/**
	 * Set price filter query args
	 *	 
	 * @param WP_Query $q
	 * @param WC_Query $wc_query	 
	 */
	public static function price_filter_product_query( $q, $wc_query ) {
		$args = $q->query_vars;
		if ( WCPBC()->customer->group_key && WCPBC()->customer->exchange_rate && isset( $args['meta_query'] ) )	{
			$args = $args['meta_query'];

			foreach ($args as $i => $query) {

				if ( isset( $query['price_filter'] ) && $query['price_filter'] ) {

					$min = $query['value'][0]; 
					$max = $query['value'][1];

					$matched_products = array();
					$matched_products_query = self::price_filter_results( $matched_products, $min, $max );

					if ( $matched_products_query ) {
						foreach ( $matched_products_query as $product ) {
							if ( $product->post_type == 'product' ) {
								$matched_products[] = $product->ID;
							}
							if ( $product->post_parent > 0 ) {
								$matched_products[] = $product->post_parent;
							}
						}					
					
						$q->set( 'post__in', array_unique( $matched_products ) );
					}

					unset($q->query_vars['meta_query'][$i]);

					break;
				}
			}
		}		
	}

	/**
	 * Return de min and max value of product prices	
	 *
	 * @param string $min_or_max
	 * @param double $amount
	 * @return double
	 */	
	private static function _price_min_amount( $amount, $min_or_max = 'min' ) {				

		global $wpdb, $wp_the_query;;

		if ( WCPBC()->customer->group_key && WCPBC()->customer->exchange_rate ) {					

			$cache_key = md5( json_encode( array(						
				'wcpbc_amount',
				$min_or_max,
				WCPBC()->customer->group_key,
				WCPBC()->customer->exchange_rate,
				json_encode( $wp_the_query->query_vars ),				
			) ) ) . WC_Cache_Helper::get_transient_version( 'product' );			

			if ( false === ( $amount = get_transient( $cache_key ) ) ) {					

				$_price_method = '_' . WCPBC()->customer->group_key . '_price_method';
				$_price = '_' . WCPBC()->customer->group_key . '_price';

				$args       = $wp_the_query->query_vars;
				$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
				$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

				if ( ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
					$tax_query[] = array(
						'taxonomy' => $args['taxonomy'],
						'terms'    => array( $args['term'] ),
						'field'    => 'slug',
					);
				}

				foreach ( $meta_query as $key => $query ) {
					if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
						unset( $meta_query[ $key ] );
					}
				}

				$meta_query = new WP_Meta_Query( $meta_query );
				$tax_query  = new WP_Tax_Query( $tax_query );

				$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
				$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

				$sql = 'SELECT ' . $min_or_max .'( IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", wc_price.meta_value * %1$s, price.meta_value + 0) ) as amount ';
				$sql .= 'FROM %2$s ' . $tax_query_sql['join'] . $meta_query_sql['join'];
				$sql .= 'INNER JOIN %3$s wc_price ON ID = wc_price.post_id and wc_price.meta_key = "_price" AND wc_price.meta_value != "" ';
				$sql .= 'LEFT JOIN %3$s price_method ON ID = price_method.post_id and price_method.meta_key = "%4$s" ';
				$sql .= 'LEFT JOIN %3$s price ON ID = price.post_id and price.meta_key = "%5$s" AND price.meta_value != "" ';
				$sql .= 'WHERE post_type IN ( "product", "product_variation" ) AND post_status = "publish" ';

				$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];								

				$sql = $wpdb->prepare( $sql, WCPBC()->customer->exchange_rate, $wpdb->posts, $wpdb->postmeta, $_price_method, $_price);								

				$amount = $wpdb->get_var( $sql);

				set_transient( $cache_key, $amount, DAY_IN_SECONDS );
			}
		}

		return $amount;
	}

	/**
	 * Return de min and max value of product prices
	 * This function is here for backwards commpatility with WooCommerce < 2.6.
	 *
	 * @param string $min_or_max
	 * @param double $amount
	 * @return double
	 */	
	private static function _legacy_price_min_amount( $amount, $min_or_max = 'min' ) {				

		global $wpdb;

		if ( WCPBC()->customer->group_key && WCPBC()->customer->exchange_rate ) {		

			$cache_key = md5( json_encode( array(						
				'wcpbc_amount',
				$min_or_max,
				WCPBC()->customer->group_key,
				WCPBC()->customer->exchange_rate,
				implode( ',', array_map( 'absint', WC()->query->layered_nav_product_ids ) ),				
			) ) ) . WC_Cache_Helper::get_transient_version( 'product' );			

			if ( false === ( $amount = get_transient( $cache_key ) ) ) {					

				$_price_method = '_' . WCPBC()->customer->group_key . '_price_method';
				$_price = '_' . WCPBC()->customer->group_key . '_price';

				$sql = 'SELECT ' . $min_or_max .'( IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", wc_price.meta_value * %1$s, price.meta_value + 0) ) as amount
						FROM %2$s posts
						INNER JOIN %3$s wc_price ON ID = wc_price.post_id and wc_price.meta_key = "_price" AND wc_price.meta_value != ""
						LEFT JOIN %3$s price_method ON ID = price_method.post_id and price_method.meta_key = "%4$s"
						LEFT JOIN %3$s price ON ID = price.post_id and price.meta_key = "%5$s" AND price.meta_value != ""
						WHERE post_type IN ( "product", "product_variation" ) AND post_status = "publish"';

				if ( count( WC()->query->layered_nav_product_ids )!== 0 ) {

					$sql .= ' AND ( ';
					$sql .= ' posts.ID IN (' . implode( ',', array_map( 'absint', WC()->query->layered_nav_product_ids ) ) . ')';
					$sql .= ' OR ( posts.post_parent IN (' . implode( ',', array_map( 'absint', WC()->query->layered_nav_product_ids ) ) . ')';
					$sql .= ' AND posts.post_parent != 0 )';
					$sql .= ' )';
				}

				$sql = $wpdb->prepare( $sql, WCPBC()->customer->exchange_rate, $wpdb->posts, $wpdb->postmeta, $_price_method, $_price);								

				$amount = $wpdb->get_var( $sql);

				set_transient( $cache_key, $amount, HOUR_IN_SECONDS );
			}
		}

		return $amount;
	}
	
	/** 
	 * Filter for price_filter_widget_min_amount
	 * @param $amount Min amount
	 */
	 public static function price_filter_widget_min_amount( $amount ) {	 
	 	if ( version_compare( WC()->version, '2.6', '<' ) ) {
	 		return floor( self::_legacy_price_min_amount( $amount ) );	 	
	 	}
	 	
	 	return floor( self::_price_min_amount( $amount ) );	 	
	 }

	 /** 
	 * Filter for price_filter_widget_max_amount
	 * @param $amount Max amount
	 */
	 public static function price_filter_widget_max_amount( $amount ) {	 
	 	if ( version_compare( WC()->version, '2.6', '<' ) ) {
	 		return ceil( self::_legacy_price_min_amount( $amount, 'max' ) );	 	
	 	}
	 	return ceil( self::_price_min_amount( $amount, 'max' ) );
	 }	 
	 
	/**
	 * Returns an array containing the IDs of the products that are on sale. Filter through get_transient
	 * @return array
	 */
	public static function product_ids_on_sale( $value, $transient = false ) {
		global $wpdb;

		if ( WCPBC()->customer->group_key ) {

			$cache_key = 'wcpbc_products_onsale_' . WCPBC()->customer->group_key;
			
			// Load from cache
			$product_ids_on_sale = get_transient( $cache_key );

			// Valid cache found
			if ( false !== $product_ids_on_sale ) {			
				return $product_ids_on_sale;
			}
			
			$_price_method = '_' . WCPBC()->customer->group_key . '_price_method';
			$_variable_price_method = '_' . WCPBC()->customer->group_key . '_variable_price_method';
			$_price = '_' . WCPBC()->customer->group_key . '_price';				
			$_variable_price = '_' . WCPBC()->customer->group_key . '_variable_price';
			$_sale_price = '_' . WCPBC()->customer->group_key . '_sale_price';
			$_variable_sale_price = '_' . WCPBC()->customer->group_key . '_variable_sale_price';
			
			
			$sql = $wpdb->prepare( '
				SELECT posts.ID, posts.post_parent FROM %1$s AS posts 
				LEFT JOIN %2$s AS wc_price ON wc_price.post_id = posts.ID AND wc_price.meta_key = "_price"
				LEFT JOIN %2$s AS wc_sale_price ON wc_sale_price.post_id = posts.ID AND wc_sale_price.meta_key = "_sale_price"
				LEFT JOIN %2$s AS price_method ON price_method.post_id = posts.ID AND price_method.meta_key in ("%3$s" , "%4$s")
				LEFT JOIN %2$s AS price ON price.post_id = posts.ID AND price.meta_key in ("%5$s" , "%6$s")
				LEFT JOIN %2$s AS sale_price ON sale_price.post_id = posts.ID AND sale_price.meta_key in ("%7$s" , "%8$s")
				WHERE posts.post_type IN ( "product", "product_variation" ) AND posts.post_status = "publish"
				AND ( IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", (IFNULL(wc_price.meta_value, 0) + 0) * %9$s, IFNULL(price.meta_value, 0) + 0) ) != 0
				AND	( IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", (IFNULL(wc_price.meta_value, 0) + 0) * %9$s, IFNULL(price.meta_value, 0) + 0) ) = 
					( IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", (IFNULL(wc_sale_price.meta_value, 0) + 0) * %9$s, IFNULL(sale_price.meta_value, 0) + 0) )
			', $wpdb->posts, $wpdb->postmeta, $_price_method, $_variable_price_method, $_price, $_variable_price, $_sale_price, $_variable_sale_price, WCPBC()->customer->exchange_rate ) ;
			
			$on_sale_posts = $wpdb->get_results( $sql );
			
			$product_ids_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

			set_transient( $cache_key, $product_ids_on_sale, DAY_IN_SECONDS * 30 );

			return $product_ids_on_sale;

		} else {
			return false;
		}
	}

	/**
	 * Init legacy hooks
	 * @return array
	 */
	public static function init_legacy_hooks() {
		
		if ( version_compare( WC()->version, '2.6', '<' ) ) {

			add_filter( 'woocommerce_price_filter_results', array( __CLASS__ , 'price_filter_results' ), 10, 3 );	

			add_filter( 'woocommerce_get_price_html', array( __CLASS__ , 'get_grouped_price_html' ), 10, 2 );
		}	
	}
}

WCPBC_Product_Price::init();