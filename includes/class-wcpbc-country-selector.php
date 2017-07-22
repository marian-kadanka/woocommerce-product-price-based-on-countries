<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WCPBC_Country_Selector class.
 *
 * @class 		WCPBC_Country_Selector
 * @version		1.6.16
 * @author 		oscargare
 */
class WCPBC_Country_Selector {
	
	/**
	 * Hook actions and shortcodes
	 */
	public static function init(){						
	
		add_shortcode( 'wcpbc_country_selector', array( __CLASS__ , 'shortcode_country_selector' ) );
		
		add_action( 'wcpbc_manual_country_selector', array( __CLASS__ , 'output_country_selector' ) );
	}
	
	/**
	 * Return installed countries
	 * @return array
	 */
	private static function get_countries(){
		
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
		
		$other_country = key( array_diff_key($all_countries, $countries ) );
		
		$countries[$other_country] = apply_filters( 'wcpbc_other_countries_text', __( 'Other countries' ) );
		
		return $countries;
	}
	
	/**
	 * Return manual country select form
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function shortcode_country_selector( $atts ){					

		$atts = shortcode_atts( array(			
			'other_countries_text'  	=> apply_filters( 'wcpbc_other_countries_text', __( 'Other countries', 'wc-price-based-country' ) ),
			'title' 					=> ''
		), $atts, 'wcpbc_country_selector' );

		ob_start();
		
		the_widget( 'WCPBC_Widget_Country_Selector', $atts, array( 
			'before_widget' => '',
			'after_widget' => ''
		) );		
		
		return ob_get_clean();
	}
	
	/**
	 * Output manual country select form
	 */
	public static function output_country_selector( $other_countries_text = '' ) {
		$atts = array();

		if ( ! empty( $other_countries_text ) ) {
			$atts = array(
				'other_countries_text' => $other_countries_text
			);
		}

		echo self::shortcode_country_selector( $atts );
	}
}

WCPBC_Country_Selector::init();