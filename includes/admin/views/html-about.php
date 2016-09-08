<?php
/**
 * About page for WooCommerce Price Based Country 1.6.0
 *
 * @author		oscargare
 * @category	Admin 
 * @version		1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings_page = admin_url( 'admin.php?page=wc-settings&tab=price-based-country' );
$major_version = substr( WCPBC()->version, 0, 3 );
?>

<div class="wrap about-wrap">

	<h1><?php printf( __( 'Welcome to Price Based On Country %s', 'wc-price-based-country' ), $major_version ); ?></h1>
	
	<p class="about-text"><?php printf( __( 'Thank you for updating to the latest version. Price Based On Country %s includes much-needed improvements to make WooCommerce multi-currency.', 'wc-price-based-country' ), $major_version ); ?></p>	
	<div class="wcpbc-badge"><?php printf( __( 'Version %s' ), $major_version ); ?></div>
	
	<h2 class="nav-tab-wrapper wp-clearfix">
		<span href="#" class="nav-tab nav-tab-active"><?php _e( 'What&#8217;s New', 'wc-price-based-country' ); ?></span>
	</h2>	
	
	<div class="feature-section one-col">
		<h2><?php _e( 'Schedule Sale Price' ); ?></h2>		
		<img src="" />
		<p><?php printf( __( 'Schedule the sale prices is a powerful feature included by WooCommerce. PBC extends this feature to allows you to set different %ssale price dates%s by zone.' ), '<em>', '</em>'); ?></p>
	</div>
	
	<div class="feature-section one-col">
		<h2><?php _e( 'Reports' ); ?></h2>		
		<img src="" />
		<p><?php printf( __( 'The older versions don\'t support sales reports for multi-currency, showing a wrong result. PBC %s shows sales reports in your base currency and calculates amounts of orders made in a different currency by exchange rate.' ), $major_version ); ?></p>
	</div>
	
	<div class="feature-section one-col">
		<h2><?php _e( 'Coupons' ); ?></h2>		
		<img src="" />
		<p><?php _e( 'For countries included in a zone pricing you can choose if the coupon amount should be calculated using exchange rate. Great for multi-currency stores.' ); ?></p>
	</div>
	
	<hr />

	<div class="changelog">
		<h2><?php _e( 'Under the Hood' ); ?></h2>
		
		<div class="feature-section two-col">
			
			<div class="col">
				<h3><?php _e( 'New Core', 'wc-price-based-country' ); ?></h3>
				<p><?php  _e( 'The plugin core has been rewritten with the goal that make PBC more compatible with most WooCommerce plugins, especially with discount and shipping plugins.', 'wc-price-based-country' ); ?></p>
			</div>
			
			
			<div class="col">
				<h3><?php _e( 'Developer Friendly', 'wc-price-based-country' ); ?></h3>
				<p><?php _e( 'New filters and actions does the plugin more extendible and adaptable than ever before. I hope you enjoy using it!', 'wc-price-based-country' ) ; ?></p>	
			</div>				
			
		</div>
		
	</div>			
	
	<hr />
	
	<div class="return-to-dashboard">
		<a href="<?php echo esc_url( $settings_page ); ?>"><?php _e( 'Go to Price Based on Country settings', 'wc-price-based-country' ); ?></a>		
	</div>
	
</div>