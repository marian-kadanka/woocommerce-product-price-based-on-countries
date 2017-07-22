<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Country Selector Widget
 *
 * @author   OscarGare
 * @category Widgets 
 * @version  1.6.16
 * @extends  WC_Widget
 */
class WCPBC_Widget_Country_Selector extends WC_Widget {

	/**
	 * @var string
	 */
	private static $_other_countries_text = '';

	/**
	 * Constructor
	 */
	public function __construct() {		
		$this->widget_description = __( 'A country switcher for your store.', 'wc-price-based-country' );
		$this->widget_id          = 'wcpbc_country_selector';
		$this->widget_name        = __( 'WooCommerce Country Switcher', 'wc-price-based-country' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Country', 'wc-price-based-country' ),
				'label' => __( 'Title', 'wc-price-based-country' )
			),
			'other_countries_text'  => array(
				'type'  => 'text',
				'std'   => __( 'Other countries', 'wc-price-based-country' ) ,
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
	public function widget( $args, $instance ) {				

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

		wcpbc_maybe_asort_locale( $countries );
				
		//add other countries
		$other_country = key( array_diff_key($all_countries, $countries ) );		
		$countries[$other_country] = isset( $instance['other_countries_text']) ? $instance['other_countries_text'] : $this->settings['other_countries_text']['std'] ;				

		$selected_country = wcpbc_get_woocommerce_country();

		if ( ! array_key_exists( $selected_country, $countries ) ) {
			$selected_country = $other_country;
		}						

		$this->widget_start( $args, $instance );						

		echo '<div class="wc-price-based-country">';
		wc_get_template('country-selector.php', array( 'countries' => $countries, 'selected_country' => $selected_country ), 'woocommerce-product-price-based-on-countries/',  WCPBC()->plugin_path()  . '/templates/' );
		echo '</div>';

		$this->widget_end( $args );
	}	
	
}

?>