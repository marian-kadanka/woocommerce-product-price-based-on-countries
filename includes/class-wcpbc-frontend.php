<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Frontend' ) ) :

require_once 'class-wcpbc-customer.php';	

/**
 * WCPBC_Frontend
 *
 * WooCommerce Price Based Country Front-End
 *
 * @class 		WCPBC_Frontend
 * @version		1.4.2
 * @author 		oscargare
 */
class WCPBC_Frontend {

	/**
	 * @var WCPBC_Customer $customer
	 */
	protected $customer = null;

	function __construct(){
		
		add_action( 'woocommerce_init', array(&$this, 'init') );		

		add_action( 'wp_enqueue_scripts', array( &$this, 'load_checkout_script' ) );

		add_action( 'woocommerce_checkout_update_order_review', array( &$this, 'checkout_country_update' ) );									

		add_action( 'wcpbc_manual_country_selector', array( &$this, 'country_select' ) );

		add_filter( 'woocommerce_currency',  array( &$this, 'currency' ) );

		/* WC_Product Price Filters */
		add_filter( 'woocommerce_get_price', array( &$this, 'get_price' ), 10, 2 );

		add_filter( 'woocommerce_get_regular_price', array( &$this, 'get_regular_price') , 10, 2 );

		add_filter( 'woocommerce_get_sale_price', array( &$this, 'get_sale_price') , 10, 2 );								

		/* WC_Product_Variable Price Filters */
		add_filter( 'woocommerce_variation_prices_price', array( &$this, 'get_price' ), 10, 3 );

		add_filter( 'woocommerce_variation_prices_regular_price', array( &$this, 'get_regular_price' ), 10, 3 );

		add_filter( 'woocommerce_variation_prices_sale_price', array( &$this, 'get_sale_price' ), 10, 3 );
						
		add_filter( 'woocommerce_get_variation_price', array( &$this, 'get_variation_price' ), 10, 4 );		

		add_filter( 'woocommerce_get_variation_regular_price', array( &$this, 'get_variation_regular_price' ), 10, 4 );	

		add_filter( 'woocommerce_get_variation_sale_price', array( &$this, 'get_variation_sale_price' ), 10, 4 );				
		
		/* Widget Price Filter */
		add_filter( 'woocommerce_price_filter_results', array( &$this, 'price_filter_results' ), 10, 3 );

		add_filter( 'woocommerce_price_filter_widget_min_amount', array( &$this, 'price_filter_widget_min_amount' ) );

		add_filter( 'woocommerce_price_filter_widget_max_amount', array( &$this, 'price_filter_widget_max_amount' ) );

		//shortcode country selector
		add_shortcode( 'wcpbc_country_selector', array( &$this, 'country_select' ) );

	}		

	/**
	 * Instance WCPBC Customer after WooCommerce init	 
	 */
	public function init() {
		
		if ( ! isset( $_POST['wcpbc-manual-country'] ) && get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {

			/* set test country */
			WC()->customer->set_country( $test_country );

			/* add test store message */
			add_action( 'wp_footer', array( &$this, 'test_store' ) );

		} elseif ( isset( $_POST['wcpbc-manual-country'] ) && $_POST['wcpbc-manual-country'] ) {			
			
			/* set customer WooCommerce customer country*/
			WC()->customer->set_country( $_POST['wcpbc-manual-country'] );
		}

		$this->customer = new WCPBC_Customer();								

	}

	/**
	 * Add script to checkout page	 
	 */
	public function load_checkout_script( ) {

		if ( is_checkout() ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( version_compare( WC()->version, '2.4', '<' ) ) {
				$version = '-2.3';
			} else {
				$version = '';
			}

			wp_enqueue_script( 'wc-price-based-country-checkout', WCPBC()->plugin_url() . 'assets/js/wcpbc-checkout' . $version . $suffix . '.js', array( 'wc-checkout', 'wc-cart-fragments' ), WC_VERSION, true );
		}

	}

	/**
	 * Update WCPBC Customer country when order review is update
	 */
	public function checkout_country_update( $post_data ) {			
		
		if ( isset( $_POST['country'] ) && ! in_array( $_POST['country'] , $this->customer->countries ) ) {
			
			$this->customer->set_country( $_POST['country'] );
						
		}
	}

	/**
	 * Output manual country select form
	 */
	public function country_select() {

		$all_countries = WC()->countries->get_countries();		
		$base_country = wc_get_base_location();			

		$countries[ $base_country['country'] ] = $all_countries[$base_country['country']];

		foreach ( WCPBC()->get_regions() as $region ) {
			
			foreach ( $region['countries'] as $country ) {

				if ( ! array_key_exists( $country, $countries ) ) {
					$countries[ $country ] = $all_countries[$country];					
				}
			}			
		}

		asort( $countries );
		
		$other_country = key( array_diff_key($all_countries, $countries ) );
		
		$countries[$other_country] = apply_filters( 'wcpbc_other_countries_text', __( 'Other countries' ) );	

		wc_get_template('country-selector.php', array( 'countries' => $countries ), 'woocommerce-product-price-based-on-countries/',  WCPBC()->plugin_path()  . '/templates/' );
	}

	/**
	 * Return test store message 
	 */
	public function test_store() {

		echo '<p class="demo_store">This is a demo store for testing purposes.</p>' ;
	}
	
	/**
	 * Return currency
	 * @return string currency
	 */
	public function currency( $currency ) {

		$wppbc_currency = $currency;
		
		if ( $this->customer->currency !== '' ) {
			
			$wppbc_currency = $this->customer->currency;
			
		}
		
		return $wppbc_currency;
	}		

	/**
	 * Returns the product price.
	 * @return string price
	 */
	protected function get_product_price ( $price, $product, $price_type ) {	
		
		$wcpbc_price = $price;
		
		if ( $this->customer->group_key ) {					
			
			$meta_key_preffix = '_' . $this->customer->group_key;

			if ( get_class( $product ) == 'WC_Product_Variation' ) {
				
				$post_id = $product->variation_id;	

				$meta_key_preffix .= '_variable';
				
			} else {
				$post_id = $product->id; 
			}
			
			$price_method = get_post_meta( $post_id, $meta_key_preffix . '_price_method', true ); 

			if ( $price_method === 'manual') {

				$wcpbc_price = get_post_meta( $post_id, $meta_key_preffix . $price_type, true );

			} elseif ( $this->customer->exchange_rate && !empty( $price ) ) {

					$wcpbc_price = ( $price * $this->customer->exchange_rate );							
			} 						
		}
			
		return $wcpbc_price;
	}

	/**
	 * Returns the product's price.
	 * @return string price
	 */
	public function get_price ($price, $product, $parent = NULL) {			
		return $this->get_product_price( $price, $product, '_price');
	}

	/**
	 * Returns the product's regular price.
	 * @return string price
	 */
	public function get_regular_price ($price, $product, $parent = NULL) {					
		return $this->get_product_price( $price, $product, '_regular_price');
	}	
	
	/**
	 * Returns the product's sale price
	 * @return string price
	 */
	public function get_sale_price ( $price, $product, $parent = NULL ) {			
		return $this->get_product_price( $price, $product, '_sale_price');
	}
	
	/**
	 * Get the min or max variation active price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */		
	public function get_variation_price( $price, $product, $min_or_max, $display, $price_type = '_price' ) {		
		$wcpbc_price = $price;
		
		if ( $this->customer->group_key ) {

			$variation_id = get_post_meta( $product->id, '_' . $this->customer->group_key . '_' . $min_or_max . $price_type . '_variation_id', true );

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

			} elseif( $wcpbc_price && $this->customer->exchange_rate && $price_type !== '_price') {

				$wcpbc_price = $wcpbc_price * $this->customer->exchange_rate;
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
	public function get_variation_regular_price( $price, $product, $min_or_max, $display ) {		
		
		return $this->get_variation_price( $price, $product, $min_or_max, $display, '_regular_price' );
	}		

	/**
	 * Get the min or max variation sale price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string price
	 */
	public function get_variation_sale_price( $price, $product, $min_or_max, $display ) {		
		
		return $this->get_variation_price( $price, $product, $min_or_max, $display, '_sale_price' );
	}	

	/**
	 * Return matched produts where price between min and max
	 *
	 * @param array $matched_products_query
	 * @param int $min 
	 * @param int $max
	 * @return array
	 */
	public function price_filter_results( $matched_products_query, $min, $max ){

		global $wpdb;

		if ( $this->customer->group_key ) {
			
			$_price_method = '_' . $this->customer->group_key . '_price_method';
			$_price = '_' . $this->customer->group_key . '_price';

			$sql = $wpdb->prepare('SELECT DISTINCT ID, post_parent, post_type FROM %1$s 
					INNER JOIN %2$s wc_price ON ID = wc_price.post_id and wc_price.meta_key = "_price" AND wc_price.meta_value != ""
					LEFT JOIN %2$s price_method ON ID = price_method.post_id and price_method.meta_key = "%3$s"
					LEFT JOIN %2$s price ON ID = price.post_id and price.meta_key = "%4$s" AND price.meta_value != ""
					WHERE post_type IN ( "product", "product_variation" )
					AND post_status = "publish"					
					AND IF(IFNULL(price_method.meta_value, "exchange_rate") = "exchange_rate", wc_price.meta_value * %5$s, price.meta_value + 0) BETWEEN %6$d AND %7$d'
			, $wpdb->posts, $wpdb->postmeta, $_price_method, $_price, $this->customer->exchange_rate, $min, $max);

			$matched_products_query = $wpdb->get_results( $sql, OBJECT_K );						
		}

		return $matched_products_query;
	}

	/**
	 * Return de min and max value of product prices
	 *
	 * @param string $min_or_max
	 * @param double $amount
	 * @return double
	 */	
	public function _price_min_amount( $amount, $min_or_max = 'min' ) {				

		global $wpdb;

		if ( $this->customer->group_key && $this->customer->exchange_rate ) {		

			$cache_key = 'wcpbc_amount' . md5( json_encode( array(						
				$min_or_max,
				$this->customer->group_key,
				$this->customer->exchange_rate,
				implode( ',', array_map( 'absint', WC()->query->layered_nav_product_ids ) ),
				WC_Cache_Helper::get_transient_version( 'product' )
			) ) );			

			if ( false === ( $amount = get_transient( $cache_key ) ) ) {					

				$_price_method = '_' . $this->customer->group_key . '_price_method';
				$_price = '_' . $this->customer->group_key . '_price';

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

				$sql = $wpdb->prepare( $sql, $this->customer->exchange_rate, $wpdb->posts, $wpdb->postmeta, $_price_method, $_price);								

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
	 public function price_filter_widget_min_amount( $amount ) {	 
	 	return floor( $this->_price_min_amount( $amount ) );	 	
	 }

	 /** 
	 * Filter for price_filter_widget_max_amount
	 * @param $amount Max amount
	 */
	 public function price_filter_widget_max_amount( $amount ) {	 
	 	 	return ceil( $this->_price_min_amount( $amount, 'max' ) );
	 }	 

}

endif;

$wcpbc_frontend = new WCPBC_Frontend();

?>
