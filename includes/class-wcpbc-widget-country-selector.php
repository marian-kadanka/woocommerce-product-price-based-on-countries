<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Country Selector Widget
 *
 * @author   OscarGare
 * @category Widgets 
 * @version  1.5.0
 * @extends  WC_Widget
 */
class WCPBC_Widget_Country_Selector extends WC_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {		
		$this->widget_description = __( 'A country selector for your store.', 'wc-price-based-country' );
		$this->widget_id          = 'wcpbc_country_selector';
		$this->widget_name        = __( 'WooCommerce Country Selector', 'wc-price-based-country' );
		$this->settings           = array(
			'other_countries_text'  => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Other countries text', 'wc-price-based-country' )
			)
		);

		parent::__construct();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 */
	function widget( $args, $instance ) {		

		add_filter('wcpbc_other_countries_text', function ( $value ) use ($instance) { 			
			return $instance['other_countries_text'];
		});

		$this->widget_start( $args, $instance );
		
		do_action('wcpbc_manual_country_selector');

		$this->widget_end( $args );
	}
}

?>