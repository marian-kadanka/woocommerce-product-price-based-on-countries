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
?>

<div class="wrap about-wrap">

	<h1><?php printf( __( 'Welcome to Price Based on Country %s', 'wc-price-based-country' ), WCPBC()->version ); ?></h1>
	
	<p class="about-text"><?php printf( __( 'Thank you for updating to the latest version. Price Based on Country %s includes much-needed improvements to make WooCommerce multi-currency.', 'wc-price-based-country' ), WCPBC()->version ); ?></p>	
	<div class="wcpbc-badge"><?php printf( __( 'Version %s' ), WCPBC()->version ); ?></div>
	
	<h2 class="nav-tab-wrapper wp-clearfix">
		<a href="<?php echo admin_url( 'admin.php?page=wcpbc-about' ); ?>" class="nav-tab <?php echo ! isset( $_GET['tab'] ) ? 'nav-tab-active' : ''; ?>"><?php _e( "What's New", 'wc-price-based-country' ) ; ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=wcpbc-about&tab=credits' ); ?>" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] ==='credits' ) ? 'nav-tab-active' : ''; ?>"><?php _e( 'Credits', 'wc-price-based-country' ); ?></a>
	</h2>	
	
	<?php if ( isset( $_GET['tab'] ) && $_GET['tab'] ==='credits' ) : ?>
		<p class="about-description"><?php printf( __( 'Hi!, I’m Oscar Gare, the brain and hands behind Price Based on Country.%sI built Price Based on Country with the goal of help you to sell your products to anywhere of world without losing the control over prices.', 'wc-price-based-country' ), '<br />' ); ?></p>		
		<p class="about-description"><?php printf( __( 'If you have any questions or just want to say hi, %scontact me!%s', 'wc-price-based-country' ), '<a href="http://www.pricebasedcountry.com/contact/">', '</a>'); ?></p>
		
	<?php else : ?>
	
	<div class="feature-section one-col">
		<h2><?php _e( 'Schedule Sale Price', 'wc-price-based-country' ); ?></h2>		
		<p><?php printf( __( 'Schedule the sale prices is a powerful feature included by WooCommerce. PBC extends this feature to allows you to set different %ssale price dates%s by zone.', 'wc-price-based-country' ), '<em>', '</em>'); ?></p>
		<img style="box-shadow: 0 1px 3px rgba(0,0,0,0.2);" src="https://lh3.googleusercontent.com/41rz83hEH8ngsFzuDYwzpayDMs8gBFmPxLC68-7e3sT8QxTvE3AahXgbkwGh-E6Y9-WnKqI-98NX=w1050-h574-no" />		
	</div>
	
	<hr />
	
	<div class="feature-section two-col">
		<h2><?php _e( 'Multi-currency Improvements', 'wc-price-based-country' ); ?></h2>
		<div class="col">
			<h3><?php _e( 'Reports', 'wc-price-based-country' ); ?></h3>
			<img src="https://lh3.googleusercontent.com/XaqkgCrlBBiDSzJc8rx-KY0JwZyAuRZ_aWllAviHjWcjQgyisS1H8HNTPRav-Lr4hMeXlWXBsJNpRj2SfA1B7KvHEogMQbsQmCI5JtPmuBYytMS5NfzM4jfK5kJxBm21yd7HZc6CipBLMB7T_uv5EC5IzuSrBhk4ZyyAAuZqa1VZOOueyg8Pn0_41xBArS6nJnEBBmM04uRRoZWpCpo9dKLZfdNF0UFEhhBzHZKXtuCgTmdbTxDh_APsiqtB_4wJzO9niijA0JeiyyrsXdn7McqNZrT4Zeq_pmc98DJgLBZYaY64Q1dFvu3lZAVbTyk85s0MdpVF_ypIdO9jGHugrUB9Li_m9tBN5lKFEFFxTDQ2mn_kYLqcfh7FHF02U6q7zlsim1N2SyLf-6mNzV947yrHRhyOXEPJstSad8VZZYdwTthBxLA6e_2Qd3Rdpk_zsy7YqavVPXb8THZBtIOE_5BjKb05QjiFeStsjdear92p2QS1njMJYzX3QwVIPqOTVMMkv6NROHFy2ylxbspFsT336CFVQJRUSFMQE5XvurOTgbALuG4V_YEyKLYUTOJRlGNZs0gqQOhgdLzeqdXIuyuciZ4knDhiuWgpCM-eMLVA6Vrd=w1000-h600-no" />		
			<p><?php printf( __( 'The older versions don\'t support sales reports for multi-currency, showing a wrong result. Version 1.6 shows sales reports in your base currency and calculates amounts of orders made in a different currency by exchange rate.', 'wc-price-based-country' ), WCPBC()->version ) ; ?></p>
		</div>
		<div class="col">
			<h3><?php _e( 'Coupons', 'wc-price-based-country' ); ?></h3>
			<img src="https://lh3.googleusercontent.com/TYJsBEzgOJeZsMLzWv_BagMBonJbrbCEUPn7i16ZXummFge2ZrP6Kk_hwJHkfymc3WDZVZxBxw9e89019t1Jw72sGl3CKPwfJfYD62hzwyFwQqCFnkMBcyMawsC_YM83rpyOJLW1DAv3xXng0h_O5xTKBFxJmoq_lIDnYKCDi61UPEmbwCh5Q-RVT4rfJbiRYG-DNWz0TzqsuiDDl6u3XqqOL6Fl2Bk1oj4GyfjmD87kuL18gHTOwkHLaSey8lOBZj1xQQelt05hmS50McaWxKlc23HCV8OiYQHE-BVS8gsRb_gb3cFLmtFV5FFjzdjT5HhPEZLfZF1x8ND8iMbrwLJxcsI5TxQkV1h94qNjKsVF3M1nYIYMD7e_R994_R4QPL4gv20UezMbM4GCGWTCcdrEbIudWgP2vhNLNaANEGitdt8LCgkX0IuSPFztTG4qGXITk7lVn86lfTriaUmvaN0fhiPiPSNcmV8xzuSP3hmvmfvUo04jyQcDcMvWTzNZEeNLUsrzZkhtNOWmR9JbU2wyX5arU6v8ve-zMa03ZCouOCsfQNGGVmXx-8x2PVdjX9AD-W3igVHOYOiww6qbEt_69PmCXKMBpdgls0_8TUpYmZdN=w885-h531-no" />		
			<p><?php _e( 'You can choose if the coupon amount should be calculated using exchange rate. Great for multi-currency stores!', 'wc-price-based-country' ); ?></p>
		</div>
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
	
	<?php endif; ?>
	
</div>