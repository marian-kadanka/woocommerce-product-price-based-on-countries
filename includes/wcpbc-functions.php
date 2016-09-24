<?php
/**
 * WC Product Price Based Country Functions
 *
 * General functions available on both the front-end and admin.
 *
 * @author 		oscargare
 * @version     1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get WooCommerce customer country	 
 *
 * @return string
 */
function wcpbc_get_woocommerce_country() {
	
	$_country = WC()->customer->get_country();	
	
	if ( $_country !== WC()->customer->get_shipping_country() && 'shipping' === get_option('wc_price_based_country_based_on', 'billing') ) {
		$_country = WC()->customer->get_shipping_country();	
	}		
	
	return $_country;
}

/**
 * Set WooCommerce customer country
 *
 * @param string $country	 		 
 */
function wcpbc_set_woocommerce_country( $country ) {
	
	$ship_to_different_address = get_option( 'woocommerce_ship_to_destination' ) === 'shipping' ? 1 : 0;

	if ( 
		WC()->customer->get_country() !== WC()->customer->get_shipping_country() && 
		'shipping' === get_option('wc_price_based_country_based_on', 'shipping') && 
		'1' == apply_filters( 'woocommerce_ship_to_different_address_checked', $ship_to_different_address ) 
		) 
	{
		WC()->customer->set_shipping_country( $country );
	} else {
		WC()->customer->set_country( $country );
		WC()->customer->set_shipping_country( $country );
	}
}

/**
 * Return base currency
 *
 * @retrun string
 */
function wcpbc_get_base_currency() {
	return get_option( 'woocommerce_currency');
}


/**
 * Return product prices meta keys
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_price_meta_keys() {
	return array_unique( apply_filters( 'wc_price_based_country_price_meta_keys', array( '_price', '_regular_price', '_sale_price' ) ) );
}

/**
 * Returns all meta keys that must be overwriten
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_overwrite_meta_keys() {
	
	$price_meta_keys = wcpbc_get_price_meta_keys();
	$meta_keys 		 = $price_meta_keys;
	
	foreach ( $price_meta_keys as $price_meta ) {
		array_push( $meta_keys, 
			"_min_variation{$price_meta}", 
			"_max_variation{$price_meta}", 
			"_min{$price_meta}_variation_id", 
			"_max{$price_meta}_variation_id" 
		);
	}
	
	return array_unique( apply_filters( 'wc_price_based_country_overwrite_meta_keys', $meta_keys ) );
}

/**
 * Returns variable product types
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_parent_product_types() {
	return array_unique( apply_filters( 'wc_price_based_country_parent_product_types', array( 'variable', 'grouped' ) ) );
}
/**
 * Get a max or min price in a postmeta row of children products
 *
 * @param string $zone_price_meta_key
 * @param int $parent_id
 * @param string $min_or_max
 * @return object
 */
function wcpbc_get_children_price( $zone_price_meta_key, $parent_id, $min_or_max = 'min' ){
	global $wpdb;	
	
	$query = array(
		'select'	=> 'SELECT _zone_price.post_id, _zone_price.meta_value as value', 
		'from'		=> "FROM {$wpdb->posts} posts INNER JOIN {$wpdb->postmeta} _zone_price ON posts.ID = _zone_price.post_id AND _zone_price.meta_key = %s",
		'where'		=> "WHERE _zone_price.meta_value <> '' AND posts.post_status = 'publish' AND posts.post_parent = %d",
		'order by'	=> "ORDER BY _zone_price.meta_value +0 " . ( $min_or_max == 'max' ? 'desc' : 'asc' ) . " LIMIT 1"
	);
	
	$query_params = array( $zone_price_meta_key, $parent_id );
	
	// Skip hidden products
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$notify_no_stock_amount = get_option( 'woocommerce_notify_no_stock_amount' );
		$query['from'] .= " LEFT JOIN {$wpdb->postmeta} _stock ON posts.ID = _stock.post_id AND _stock.meta_key = '_stock'";
		$query['where'] .= " AND ( IFNULL(_stock.meta_value, '') = '' OR _stock.meta_value<%s)" ;
		
		$query_params[] = $notify_no_stock_amount;
	}
	
	return $wpdb->get_row( $wpdb->prepare( implode(' ', $query), $query_params ) );	
}

/**
 * Sync product variation prices with parent for a pricing zone
 *
 * @since 1.6.0
 * @param string $zone_id
 * @param int $product_id
 */
function wcpbc_zone_variable_product_sync( $zone_id, $product_id ) {
	
	foreach ( wcpbc_get_price_meta_keys() as $price_type ) {
		
		$zone_price_meta_key = '_' . $zone_id . $price_type;
		
		// Min price
		$min_price_row = wcpbc_get_children_price( $zone_price_meta_key, $product_id );
		
		if ( $min_price_row ) {			
			
			// Store price
			update_post_meta( $product_id, '_' . $zone_id . '_min_variation' . $price_type, $min_price_row->value );			
			
			// Store id
			update_post_meta( $product_id, '_' . $zone_id . '_min' . $price_type . '_variation_id', $min_price_row->post_id );
			
			// Store min price in a variable
			${$price_type} = $min_price_row->value;				
			
		} else {
			// Store price
			update_post_meta( $product_id, '_' . $zone_id . '_min_variation' . $price_type, '' );			
			
			// Store id
			update_post_meta( $product_id, '_' . $zone_id . '_min' . $price_type . '_variation_id', '' );
		}
		
		// Max price
		$max_price_row = wcpbc_get_children_price( $zone_price_meta_key, $product_id, 'max' );
		
		if ( $max_price_row ) {
			
			// Store prices
			update_post_meta( $product_id, '_' . $zone_id . '_max_variation' . $price_type, $max_price_row->value );

			// Store ids		
			update_post_meta( $product_id, '_' . $zone_id . '_max' . $price_type . '_variation_id', $max_price_row->post_id );
			
		} else {
			// Store price
			update_post_meta( $product_id, '_' . $zone_id . '_max_variation' . $price_type, '' );			
			
			// Store id
			update_post_meta( $product_id, '_' . $zone_id . '_max' . $price_type . '_variation_id', '' );
		}						
	}
	
	if ( isset( $_price ) ) {
		update_post_meta( $product_id, '_' . $zone_id . '_price',  $_price );
	}	
	
	update_post_meta( $product_id, '_' . $zone_id . '_price_method',  'nothing' );
}

/**
 * Sync grouped products with the children lowest price for a pricing zone
 *
  * @since 1.6.0
 */
function wcpbc_zone_grouped_product_sync( $zone_id, $product_id ) {
	$min_price = wcpbc_get_children_price( '_' . $zone_id . '_price', $product_id );
	if ( $min_price ) {
		update_post_meta( $product_id, '_' . $zone_id . '_price', $min_price->value );
	} else {
		update_post_meta( $product_id, '_' . $zone_id . '_price', '' );
	}	
	
	update_post_meta( $product_id, '_' . $zone_id . '_price_method',  'nothing' );
}
	
/**
 * Sync product variation prices with parent
 *
  * @since 1.6.0
 */
function wcpbc_variable_product_sync( $product_id, $children ) {
	
	foreach ( array_keys( WCPBC()->get_regions() ) as $zone_id ) {		
		wcpbc_zone_variable_product_sync( $zone_id, $product_id );
	}
}
add_action( 'woocommerce_variable_product_sync', 'wcpbc_variable_product_sync', 10, 2 );

/**
 * Sync products prices by exchange rate for a pricing zone
 *
 * @since 1.6.0
 * @param  string $zone_id
 * @param  float $exchange_rate
 * @return array
 */
function wcpbc_sync_exchange_rate_prices( $zone_id, $exchange_rate ){
	global $wpdb;		
	
	if ( ! $exchange_rate ) {
		return ;
	}

	$price_method_meta_key = '_' . $zone_id . '_price_method';
	
	// variable products must haven't a price method
	$parent_product_types = wcpbc_get_parent_product_types();
	
	$parent_product_ids = $wpdb->get_col( $wpdb->prepare( "
		SELECT t_r.object_id
		FROM {$wpdb->term_relationships} t_r 
		INNER JOIN {$wpdb->term_taxonomy} t_t ON t_r.term_taxonomy_id = t_t.term_taxonomy_id AND t_t.taxonomy = 'product_type'
		INNER JOIN {$wpdb->terms} t ON t.term_id = t_t.term_id
		LEFT JOIN {$wpdb->postmeta} _price_method ON _price_method.post_id = t_r.object_id AND _price_method.meta_key = %s
		WHERE t.slug IN (" .  implode(', ', array_fill(0, count($parent_product_types), '%s') ) . ") and ifnull(_price_method.meta_value, '')<>'nothing'
	", array_merge( array( $price_method_meta_key ), $parent_product_types ) ) );
	
	if ( $parent_product_ids ) {
		foreach ( $parent_product_ids as $parent_product_id ) {
			update_post_meta( $parent_product_id, $price_method_meta_key, 'nothing' );
		}
	}			
	
	// sync products prices
	foreach ( wcpbc_get_price_meta_keys() as $price_meta_key ) {
		
		$zone_price_meta_key = '_' . $zone_id . $price_meta_key;		
		
		// Add region price meta key if not exists
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
			SELECT post.ID, %s, '0'
			FROM {$wpdb->posts} post
			LEFT JOIN {$wpdb->postmeta} _zone_price_meta_key ON _zone_price_meta_key.post_id = post.ID AND _zone_price_meta_key.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} _price_method on post.ID = _price_method.post_id AND _price_method.meta_key = %s
			WHERE post.post_type IN ( 'product', 'product_variation' ) AND post.post_status = 'publish' 
			AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate' AND _zone_price_meta_key.meta_id is null
		", $zone_price_meta_key, $zone_price_meta_key, $price_method_meta_key ) );
				
		// Update region price meta key by exchange_rate
		$wpdb->query( $wpdb->prepare( "
			UPDATE {$wpdb->postmeta} _zone_price_meta_key 
			INNER JOIN {$wpdb->posts} posts on posts.ID = _zone_price_meta_key.post_id 
			INNER JOIN {$wpdb->postmeta} _price_meta_key on posts.ID = _price_meta_key.post_id AND _price_meta_key.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id AND _price_method.meta_key = %s
			SET _zone_price_meta_key.meta_value = CASE ifnull(_price_meta_key.meta_value, '') when '' THEN '' ELSE (_price_meta_key.meta_value + 0) * %f END
			WHERE _zone_price_meta_key.meta_key = %s AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate'
			AND _zone_price_meta_key.meta_value <> CASE ifnull(_price_meta_key.meta_value, '') when '' THEN '' ELSE (_price_meta_key.meta_value + 0) * %f END
		" , $price_meta_key, $price_method_meta_key, floatval( $exchange_rate ), $zone_price_meta_key, floatval( $exchange_rate ) ) );	
	}		
	
	//sync parents product prices
	$parent_products = $wpdb->get_results( $wpdb->prepare( "
		SELECT DISTINCT posts.post_parent AS id, posts.post_type as child_post_type
		FROM {$wpdb->posts} posts
		INNER JOIN {$wpdb->term_relationships} t_r ON t_r.object_id = posts.post_parent
		INNER JOIN {$wpdb->term_taxonomy} t_t ON t_r.term_taxonomy_id = t_t.term_taxonomy_id AND t_t.taxonomy = 'product_type'
		INNER JOIN {$wpdb->terms} t ON t.term_id = t_t.term_id
		LEFT JOIN {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id AND _price_method.meta_key = %s
		WHERE t.slug IN (" .  implode(', ', array_fill(0, count($parent_product_types), '%s') ) . ") 
		AND posts.post_type in ('product_variation', 'product') and posts.post_status = 'publish'
		AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate';
	", array_merge( array( $price_method_meta_key ), $parent_product_types ) ) );		

	if ( $parent_products ) {
		foreach ( $parent_products as $parent_product ) {	
		
			if ( $parent_product->child_post_type == 'product_variation' ) {
				// Clear prices transient for variable products.
				delete_transient( 'wc_var_prices_' .  $parent_product->id );
				
				// Sync variable product price
				wcpbc_zone_variable_product_sync( $zone_id, $parent_product->id );
				
			} else {
				// Sync grouped product price
				wcpbc_zone_grouped_product_sync( $zone_id, $parent_product->id );				
			}
		}		
	}
		
	// Clear all transients cache for product data
	wc_delete_product_transients();
}		

/**
 * Function which handles the start and end of scheduled sales via cron. 
 *
  * @since 1.6.0
 */
function wcpbc_scheduled_sales() {	
	global $wpdb;
	
	foreach ( WCPBC()->get_regions() as $zone_id => $zone ) {
		
		$key_sale_price_dates_from = '_' . $zone_id . '_sale_price_dates_from';
		$key_sale_price_dates_to = '_' . $zone_id . '_sale_price_dates_to';
		$key_price_method = '_' . $zone_id . '_price_method';
		$key_regular_price = '_' . $zone_id . '_regular_price';
		$key_sale_price = '_' . $zone_id . '_sale_price';
		$key_price = '_' . $zone_id . '_price';
		
		$parents = array();
		
		// Sales which are due to start
		$products = $wpdb->get_results( $wpdb->prepare( "
			SELECT posts.ID as id, posts.post_parent, posts.post_type, _sale_price.meta_value as sale_price, _price.meta_value as price
			FROM {$wpdb->posts} posts 
			INNER JOIN {$wpdb->postmeta} _sale_price_from on posts.ID = _sale_price_from.post_id and _sale_price_from.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id and _price_method.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _sale_price on posts.ID = _sale_price.post_id and _sale_price.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price on posts.ID = _price.post_id and _price.meta_key = %s
			WHERE _price_method.meta_value = 'manual' AND _sale_price.meta_value != _price.meta_value
				AND _sale_price_from.meta_value > 0 and _sale_price_from.meta_value < %s 
		", $key_sale_price_dates_from, $key_price_method, $key_sale_price, $key_price, current_time( 'timestamp' ) ) );
		
		if ( $products ) {
			foreach ( $products as $product ) {				
				if ( $product->sale_price ) {
					update_post_meta( $product->id, $key_price, $product->sale_price );
				} else {
					// No sale price!
					update_post_meta( $product->id, $key_sale_price_dates_from, '' );
					update_post_meta( $product->id, $key_sale_price_dates_to, '' );
				}			
			
				// Store parent for sync
				if ( $product->post_parent ) {
					if ( ! isset( $parents[$product->post_type] ) ) {
						$parents[$product->post_type] = array();
					}
					$parents[$product->post_type][] = $product->post_parent;
				}			
			}
		}
		
		// Sales which are due to end
		$products = $wpdb->get_results( $wpdb->prepare( "
			SELECT posts.ID as id, posts.post_parent, posts.post_type, _regular_price.meta_value as regular_price, _price.meta_value as price
			FROM {$wpdb->posts} posts 
			INNER JOIN {$wpdb->postmeta} _sale_price_to on posts.ID = _sale_price_to.post_id and _sale_price_to.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id and _price_method.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _regular_price on posts.ID = _regular_price.post_id and _regular_price.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price on posts.ID = _price.post_id and _price.meta_key = %s
			WHERE _price_method.meta_value = 'manual' AND _regular_price.meta_value != _price.meta_value
				AND _sale_price_to.meta_value > 0 and _sale_price_to.meta_value < %s 
		", $key_sale_price_dates_to, $key_price_method, $key_regular_price, $key_price, current_time( 'timestamp' ) ) );
		
		if ( $products ) {
			foreach ( $products as $product ) {								
				update_post_meta( $product->id, $key_price, $product->regular_price );
				update_post_meta( $product->id, $key_sale_price, '' );
				update_post_meta( $product->id, $key_sale_price_dates_from, '' );
				update_post_meta( $product->id, $key_sale_price_dates_to, '' );				
						
				// Store parent for sync
				if ( $product->post_parent ) {
					if ( ! isset( $parents[$product->post_type] ) ) {
						$parents[$product->post_type] = array();
					}
					$parents[$product->post_type][] = $product->post_parent;
				}			
			}
		}
		
		// Sync parents
		foreach ( $parents as $post_type => $parent_ids ) {
			if ( $post_type == 'product' ) {
				
				foreach ( array_unique( $parent_ids ) as $parent_id ) {
					// Sync grouped product price
					wcpbc_zone_grouped_product_sync( $zone_id, $parent_id );	
				}
			} elseif ( $post_type == 'product_variation' ) {
				
				foreach ( array_unique( $parent_ids ) as $parent_id ) {					
					// Clear prices transient for variable products.
					delete_transient( 'wc_var_prices_' .  $parent_id );
					
					// Sync variable product price
					wcpbc_zone_variable_product_sync( $zone_id, $parent_id );
				}
			}
		}
				
		// Sync exchange rate prices
		wcpbc_sync_exchange_rate_prices( $zone_id, $zone['exchange_rate'] );				
	}
	
}
add_action( 'woocommerce_scheduled_sales', 'wcpbc_scheduled_sales', 20 );

/**
 * Clear all WCPBC transients cache for product data.
 *
 * @param int $post_id (default: 0)
 */
function wcpbc_delete_product_transients( $post_id = 0 ) {
	
	$transients_to_clear = array(
		'wcpbc_products_onsale_'
	);
	
	foreach ( array_keys( WCPBC()->get_regions() ) as $region_key ) {
		foreach ( $transients_to_clear as $transient ) {
			delete_transient( $transient . $region_key );
		}
	}
}
add_action( 'woocommerce_delete_product_transients', 'wcpbc_delete_product_transients' );

/**
 * Return a a array with all currencies avaiables in WooCommerce with associate countries 
 *
 * @return array
 */
function wcpbc_get_currencies() {

	return array_unique( 
		apply_filters( 'wcpbc_currencies', 
			array(
				'AED' => array('AE'),
				'ARS' => array('AR'),
				'AUD' => array('AU', 'CC', 'CX', 'HM', 'KI', 'NF', 'NR', 'TV'),
				'BDT' => array('BD'),
				'BRL' => array('BR'),
				'BGN' => array('BG'),
				'CAD' => array('CA'),
				'CLP' => array('CL'),
				'CNY' => array('CN'),
				'COP' => array('CO'),
				'CZK' => array('CZ'),
				'DKK' => array('DK', 'FO', 'GL'),
				'DOP' => array('DO'),
				'EUR' => array('AD', 'AT', 'AX', 'BE', 'BL', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GF', 'GP', 'GR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MC', 'ME', 'MF', 'MQ', 'MT', 'NL', 'PM', 'PT', 'RE', 'SI', 'SK', 'SM', 'TF', 'VA', 'YT'),
				'HKD' => array('HK'),
				'HRK' => array('HR'),
				'HUF' => array('HU'),
				'ISK' => array('IS'),
				'IDR' => array('ID'),
				'INR' => array('IN'),
				'NPR' => array('NP'),
				'ILS' => array('IL'),
				'JPY' => array('JP'),
				'KIP' => array('LA'),
				'KRW' => array('KR'),
				'MYR' => array('MY'),
				'MXN' => array('MX'),
				'NGN' => array('NG'),
				'NOK' => array('BV', 'NO', 'SJ'),
				'NZD' => array('CK', 'NU', 'NZ', 'PN', 'TK'),
				'PYG' => array('PY'),
				'PHP' => array('PH'),
				'PLN' => array('PL'),
				'GBP' => array('GB', 'GG', 'GS', 'IM', 'JE'),
				'RON' => array('RO'),
				'RUB' => array('RU'),
				'SGD' => array('SG'),
				'ZAR' => array('ZA'),
				'SEK' => array('SE'),
				'CHF' => array('LI'),
				'TWD' => array('TW'),
				'THB' => array('TH'),
				'TRY' => array('TR'),
				'UAH' => array('UA'),
				'USD' => array('BQ', 'EC', 'FM', 'IO', 'MH', 'PW', 'TC', 'TL', 'US', 'VG'),
				'VND' => array('VN'),
				'EGP' => array('EG')
			)
		)
	);
}

/**
 * Sort a array with locale-sensitive
 *
 * @since 1.6.0
 * @param array $arr 
 * return true
 */
function wcpbc_maybe_asort_locale( &$arr ) {
	
	if ( class_exists('Collator') ) {		
		return collator_asort( new Collator( get_locale() ), $arr );
	} else {
		return asort( $arr );
	}
}