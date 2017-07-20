<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! wcpbc_is_pro() && ! class_exists( 'WCPBC_Product_Addons_Basic' ) ) :

/** 
 *
 * @class WCPBC_Product_Addons
 * @version	1.6.14
 */
class WCPBC_Product_Addons_Basic {

	/**
	 * Hook actions and filters
	 *
	 * @since 1.6.14
	 */
	public static function init() {		
		add_action( 'product_page_global_addons', array( __CLASS__, 'global_addons_admin' ), 20 );		
		add_action( 'woocommerce_product_write_panel_tabs', array( __CLASS__, 'tab' ), 11 );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
	}

	/**
	 * Return Get Pro Now ad
	 */
	protected static function get_pro_now() {
		$ad  = '<div id="message" class="inline notice woocommerce-message" style="margin: 10px;">';
		$ad .= '<p style="font-size:14px;">' . sprintf( __( 'Upgrade to %sPrice Based on Country Pro%s and get full support for WooCommerce Product Add-ons.', 'wc-price-based-country' ), '<strong>','</strong>') . '</p>';
		$ad .= '<p><a class="button button-primary" target="_blank" href="https://www.pricebasedcountry.com/pricing/?utm_source=product-addons&utm_medium=banner&utm_campaign=Get_Pro">Upgrade to Price Based on Country Pro now!</a>';
		$ad .= '</p></div>';		

		return $ad;
	}

	/**
	 * Controls the global addons admin page.
	 */
	public static function global_addons_admin() {		

		if ( ! empty( $_GET['add'] ) || ! empty( $_GET['edit'] ) ) {
			$get_pro = self::get_pro_now();

			include( WCPBC()->plugin_path() . 'includes/admin/views/html-global-product-addon.php');	
		}
	}

	/**
	 * Add product tab.
	 */
	public static function tab() {
		?><li class="addons_tab product_addons"><a href="#wcpbc_product_addons_data"><span><?php _e( 'Add-ons zone pricing', 'wc-price-based-country' ); ?></span></a></li><?php
	}

	/**
	 * Add product panel.
	 */
	public static function panel() {
		echo '<div id="wcpbc_product_addons_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">';
		echo self::get_pro_now();
		echo '</div>';
	}

}

WCPBC_Product_Addons_Basic::init();

endif;
