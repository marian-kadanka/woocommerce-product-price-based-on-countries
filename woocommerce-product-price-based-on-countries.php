<?php

/*
 Plugin Name: WooCommerce Product Price Based on Countries
 Plugin URI: https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
 Description: Sets products prices based on country of your site's visitor.
 Author: Oscar Gare
 Version: 1.5.1
 Author URI: google.com/+OscarGarciaArenas
 License: GPLv2
*/

 /*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :	


if ( ! class_exists( 'WC_Product_Price_Based_Country' ) ) :

/**
 * Main WC Product Price Based Country Class
 *
 * @class WC_Product_Price_Based_Country
 * @version	1.5.1
 */
class WC_Product_Price_Based_Country {

	/**
	 * @var string
	 */
	public $version = '1.5.1';

	/**
	 * @var The single instance of the class		 
	 */
	protected static $_instance = null;

	/**
	 * @var $regions
	 */
	protected $regions = null;
	
	/**
	 * @var $customer
	 */
	public $customer = null;
	
	/**
	 * Main WC_Product_Price_Based_Country Instance
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *	 
	 * @static
	 * @see WCPBC()
	 * @return Product Price Based Country - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WC_Product_Price_Based_Country Constructor.
	 */
	public function __construct() {						
		$this->includes();	
		$this->init_hooks();		
	}		
	
	/**
	 * Include required files used in admin and on the frontend.
	 */
	private function includes() {		

		include_once( 'includes/wcpbc-functions.php' );				
		if ( $this->is_request( 'admin') ) {
			include_once( 'includes/class-wcpbc-install.php' );
			include_once( 'includes/admin/class-wcpbc-admin.php' );												

		} elseif ( $this->is_request( 'frontend') ) {
			include_once( 'includes/class-wcpbc-frontend.php' );
			include_once( 'includes/class-wcpbc-customer.php' );			
			include_once( 'includes/class-wcpbc-product-price.php' );
			include_once( 'includes/class-wcpbc-country-selector.php' );			
		}
	}
	
	/**
	 * Hook actions
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'WCPBC_Install', 'install' ) );
		add_action( 'widgets_init', array($this, 'register_widgets') );		
		if ( ! $this->is_request( 'admin') && $this->is_request( 'frontend') ) {			
			add_action( 'woocommerce_init', array( $this , 'frontend_init'), 50 );				
		}
	}
	
	/**
	 * What type of request is this?
	 * string $type frontend or admin
	 * @return bool
	 */
	private function is_request( $type ) {

		$is_ajax = defined('DOING_AJAX') && DOING_AJAX;

		switch ( $type ) {
			case 'admin' :							
				$ajax_allow_actions = array( 'woocommerce_add_variation', 'woocommerce_load_variations', 'woocommerce_save_variations' );
				return ( is_admin() && !$is_ajax ) || ( is_admin() && $is_ajax && isset( $_POST['action'] ) && in_array( $_POST['action'], $ajax_allow_actions ) );
			
			case 'frontend' :
				return ! $this->is_request('bot') && ( ! is_admin() || ( is_admin() && $is_ajax ) ) && ! defined( 'DOING_CRON' );			

			case 'bot':
				$user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
				return preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent );
		}
	}
	
	/**
	 * Register Widgets
	 *
	 * @since 1.5.0
	 */
	 public function register_widgets(){	 	
	 	include_once( 'includes/class-wcpbc-widget-country-selector.php' );	
	 	register_widget( 'WCPBC_Widget_Country_Selector' );
	 }

	/**
	 * Init front-end variables
	 */
	 public function frontend_init(){	 	
		$this->customer = new WCPBC_Customer();		
	 }
	 
	/**
	 * Get regions
	 * @return array
	 */
	public function get_regions(){
		if ( is_null( $this->regions ) ) {
			$this->regions = get_option( 'wc_price_based_country_regions', array() );			
		}		
		return $this->regions;
	}
	
	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {		
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	
	public function plugin_path(){
		return plugin_dir_path( __FILE__ );
	}

}	//End Class

/**
 * Returns the main instance of WC_Product_Price_Based_Country to prevent the need to use globals.
 *
 * @since  1.3.0
 * @return WC_Product_Price_Based_Country
 */
function WCPBC() {
	return WC_Product_Price_Based_Country::instance();
}

$wc_product_price_based_country = WCPBC();

endif; // ! class_exists( 'WC_Product_Price_Based_Country' )
	
	
else :
	
	add_action( 'admin_init', 'oga_wppbc_deactivate' );
	
	function oga_wppbc_deactivate () {
		
		deactivate_plugins( plugin_basename( __FILE__ ) );
		
	}
	
   add_action( 'admin_notices', 'oga_wppbc_no_woocommerce_admin_notice' );
   
   function oga_wppbc_no_woocommerce_admin_notice () {
	   	?>
	   	<div class="updated">
	   		<p><strong>WooCommerce Product Price Based on Countries</strong> has been deactivated because <a href="http://woothemes.com/">Woocommerce plugin</a> is required</p>
	   	</div>
	   	<?php
    }	
      	
   
endif;



?>