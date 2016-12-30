<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Customer' ) ) :

/**
 * WCPBC_Customer
 *
 * Store WCPBC frontend data Handler
 *
 * @class 		WCPBC_Customer
 * @version		1.6.5
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Customer {

	/** Stores customer price based on country data as an array */
	protected $_data;

	/** Stores bool when data is changed */
	private $_changed = false;

	/**
	 * Constructor for the wcpbc_customer class loads the data.
	 *
	 * @access public
	 */
	public function __construct() {		
		
		$this->_data = WC()->session->get( 'wcpbc_customer' );	
		
		$wc_customer_country = wcpbc_get_woocommerce_country();					

		if ( $this->is_new_country( $wc_customer_country ) ) {
			$this->set_country( $wc_customer_country );
		}

		//set customer session cookie after headers has been send
		add_action( 'send_headers', array( $this, 'init_session' ), 10 );		

		// When leaving or ending page load, store data
		add_action( 'shutdown', array( $this, 'save_data' ), 10 );	
	}	

	/**
	 * Is a new country
	 *
	 * @return bool
	 * @access public
	 */
	private function is_new_country( $wc_customer_country ) {
		return apply_filters( 'wc_price_based_country_is_new_country', 
			empty( $this->_data ) || ! in_array( $wc_customer_country, $this->countries ) || ( $this->timestamp < get_option( 'wc_price_based_country_timestamp' ) )
		);
	}

	/**
	 * init customer session
	 *
	 * @since 1.6.5
	 * @access public
	 */
	public function init_session(){
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie(true);
		}
	}

	/**
	 * save_data function.
	 *
	 * @access public
	 */
	public function save_data() {
		
		if ( $this->_changed ) {
			WC()->session->set( 'wcpbc_customer', $this->_data );				
		}	

	}

	/**
	 * __get function.
	 *
	 * @access public
	 * @param string $property
	 * @return string
	 */
	public function __get( $property ) {

		$value = isset( $this->_data[ $property ] ) ? $this->_data[ $property ] : '';

		if ( $property === 'countries' && ! $value) {
			$value = array();			
		}

		return $value;
	}

	/**
	 * Sets wcpbc data form country.
	 *
	 * @access public
	 * @param mixed $country
	 * @return boolean
	 */
	public function set_country( $country ) {
		
		$has_region = false;

		$this->_data = array();	
		
		foreach ( WCPBC()->get_regions() as $key => $group_data ) {				

			if ( in_array( $country, $group_data['countries'] ) ) {
				$this->_data = array_merge( $group_data, array( 'zone_id' => $key, 'timestamp' => time() ) );
				$has_region = true;
				break;
			}			
					
		}
		
		$this->_changed = true;

		return $has_region;
	}		
}

endif;

?>