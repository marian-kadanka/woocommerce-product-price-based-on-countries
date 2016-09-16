<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Admin_Product_Data 
 *
 * @class 		WCPBC_Admin_Product_Data
 * @version		1.6.0
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Admin_Product_Data {

	/**
	 * Hook in methods
	 */
	public static function init() {

		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_options_countries_prices' ) );
		
		add_action( 'woocommerce_process_product_meta_simple', array( __CLASS__, 'process_product_simple_countries_prices' ) ) ;						

		add_action( 'woocommerce_process_product_meta_external', array( __CLASS__, 'process_product_simple_countries_prices' ) ) ;						
		
		add_action( 'woocommerce_process_product_meta_grouped', array( __CLASS__, 'process_product_meta_grouped' ) ) ;						
		
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'product_variable_attributes_countries_prices') , 10, 3 );				
		
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_product_variation_countries_prices' ), 10, 2 );		
		
		add_action( 'woocommerce_product_quick_edit_save',  array( __CLASS__, 'product_quick_bulk_edit_save' ) );
		
		add_action( 'woocommerce_product_bulk_edit_save',  array( __CLASS__, 'product_quick_bulk_edit_save' ), 20 );
		
		add_action( 'woocommerce_bulk_edit_variations', array( __CLASS__, 'bulk_edit_variations' ), 20, 4 );
	}


	/**
	 * Add price input to product simple metabox
	 */
	public static function product_options_countries_prices() {					

		foreach ( WCPBC()->get_regions() as $key => $value ) {	

			$_id_prefix = '_' . $key;
			
			$_price_method = get_post_meta( get_the_ID(), $_id_prefix . '_price_method' , true );	
			$_regular_price = get_post_meta( get_the_ID(), $_id_prefix . '_regular_price' , true );
			$_sale_price = get_post_meta( get_the_ID(), $_id_prefix . '_sale_price' , true );
			$_sale_price_dates = get_post_meta( get_the_ID(), $_id_prefix . '_sale_price_dates' , true );							
			$_sale_price_dates_from = ( $date = get_post_meta( get_the_ID(), $_id_prefix . '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
			$_sale_price_dates_to   = ( $date = get_post_meta( get_the_ID(), $_id_prefix . '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
			
			$_price_method = empty($_price_method) ? 'exchange_rate' : $_price_method;				
			$_sale_price_dates = empty($_sale_price_dates) ? 'default' : $_sale_price_dates;							
							
			?>
				<div class="options_group pricing show_if_simple show_if_external wcpbc_pricing">					

					<?php
						woocommerce_wp_radio(
							array(
								'id' => $_id_prefix . '_price_method',
								'value' => $_price_method,
								'class' => 'wcpbc_price_method',
								'label' => __( 'Price for', 'wc-price-based-country' )  . ' ' . $value['name']. ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')',								
								'options' => array(
									'exchange_rate'	=> __('Calculate prices by exchange rate', 'wc-price-based-country'),
									'manual' 		=> __('Set prices manually', 'wc-price-based-country')
								)
							)
						);
					?>										
					
					<div style="display: <?php echo ($_price_method == 'exchange_rate' ? 'none' : 'block' ); ?>" class="wcpbc_show_if_manual">
						
						<?php do_action('wc_price_based_country_before_product_options_pricing', $_id_prefix, $value['currency'] ); ?>
																		
						<p class="form-field _regular_price_field">
							<label><?php echo __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
							<input type="text" id="<?php echo $_id_prefix . '_regular_price'; ?>" name="<?php echo $_id_prefix . '_regular_price'; ?>" value="<?php echo wc_format_localized_price( $_regular_price ); ?>" class="short wc_input_price" placeholder="" />
						</p>

						<p class="form-field _sale_price_field">								
							<label><?php echo __( 'Sale Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol($value['currency']) . ')'; ?></label>
							<input type="text" id="<?php echo $_id_prefix . '_sale_price'; ?>" name="<?php echo $_id_prefix . '_sale_price'; ?>" value="<?php echo wc_format_localized_price( $_sale_price ); ?>" class="short wc_input_price wcpbc_sale_price" />
						</p>
	
						<?php
							woocommerce_wp_radio(
								array(
									'id' => $_id_prefix . '_sale_price_dates',
									'value' => $_sale_price_dates,
									'class' => 'wcpbc_sale_price_dates',
									'wrapper_class' => 'wcpbc_wrapper_sale_price_dates',
									'label' => __( 'Sale price dates', 'wc-price-based-country' ),								
									'options' => array(
										'default'	=> __('Same as default price', 'wc-price-based-country'),
										'manual' 	=> __('Set specific dates', 'wc-price-based-country')
									)
								)
							);
							
							// Special Price date range							
							echo	'<p class="form-field sale_price_dates_fields wcpbc_show_if_manual" style="display: ' . ($_sale_price_dates == 'default' ? 'none' : 'block' ) . '">																				
										<input type="text" class="short sale_price_dates_from" name="' . $_id_prefix . '_sale_price_dates_from" id="' . $_id_prefix . '_sale_price_dates_from" value="' . esc_attr( $_sale_price_dates_from ) . '" placeholder="YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"/>										
										<input type="text" class="short sale_price_dates_to" name="' . $_id_prefix . '_sale_price_dates_to" id="' . $_id_prefix . '_sale_price_dates_to" value="' . esc_attr( $_sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'woocommerce' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />' . wc_help_tip( __( 'The sale will end at the beginning of the set date.', 'woocommerce' ) ) . '
									</p>';
						?>
						
					</div>

				</div>
				
			<?php		
		}								
	}

	
	/**
	 * Save meta data product
	 */
	public static function process_product_simple_countries_prices( $post_id, $i = false ) {				
		
		// Get product type
		$product_type = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
		
		foreach ( WCPBC()->get_regions() as $key => $value ) {
			
			$key_prefix = '_' . $key;						
			
			if ( $i === false ) {
								
				$_regular_price 		= wc_clean( $_POST[$key_prefix . '_regular_price'] );
				$_sale_price 			= wc_clean( $_POST[$key_prefix . '_sale_price'] );
				$_price_method 			= wc_clean( $_POST[$key_prefix . '_price_method'] );	
				$_sale_price_dates		= wc_clean( $_POST[$key_prefix . '_sale_price_dates'] );					
				$_sale_price_dates_from	= wc_clean( $_POST[$key_prefix . '_sale_price_dates_from'] );
				$_sale_price_dates_to	= wc_clean( $_POST[$key_prefix . '_sale_price_dates_to'] );
				
			} else {

				$_regular_price 		= wc_clean( $_POST[$key_prefix . '_variable_regular_price'][$i] );
				$_sale_price 			= wc_clean( $_POST[$key_prefix . '_variable_sale_price'][$i] );
				$_price_method 			= wc_clean( $_POST[$key_prefix . '_variable_price_method'][$i] );			
				$_sale_price_dates		= wc_clean( $_POST[$key_prefix . '_variable_sale_price_dates'][$i] );
				$_sale_price_dates_from	= wc_clean( $_POST[$key_prefix . '_variable_sale_price_dates_from'][$i] );
				$_sale_price_dates_to	= wc_clean( $_POST[$key_prefix . '_variable_sale_price_dates_to'][$i] );
			}			
					
			if ( $_sale_price_dates == 'default' ) {
				$_sale_price_dates_from	= get_post_meta( $post_id, '_sale_price_dates_from', true );
				$_sale_price_dates_to	= get_post_meta( $post_id, '_sale_price_dates_to', true );
			} else {
				$_sale_price_dates_from	= $_sale_price_dates_from ? strtotime( $_sale_price_dates_from ) : '';
				$_sale_price_dates_to	= $_sale_price_dates_to ? strtotime( $_sale_price_dates_to ) : '';
				
				if ( $_sale_price_dates_to && ! $_sale_price_dates_from ) {
					$_sale_price_dates_from = date( 'Y-m-d' );					
				}
			}
			
			if ( $_price_method == 'exchange_rate' ) {
				
				$_sale_price_dates 		= 'default';
				$_sale_price_dates_from = '';
				$_sale_price_dates_to 	= '';
				
				$_regular_price = get_post_meta( $post_id, '_regular_price', true );
				$_sale_price 	= get_post_meta( $post_id, '_sale_price', true );
				$_price 		= get_post_meta( $post_id, '_price', true );
				
				$_regular_price = ( $_regular_price !== '' ? $_regular_price * $value['exchange_rate'] : '' );
				$_sale_price 	= ( $_sale_price !== '' ? $_sale_price * $value['exchange_rate'] : '' );
				$_price 		= ( $_price !== '' ? $_price * $value['exchange_rate'] : '' );				

			} else {

				$_regular_price = wc_format_decimal( $_regular_price );
				$_sale_price 	= wc_format_decimal( $_sale_price );
				
				// Update price if on sale
				if ( '' !== $_sale_price && '' === $_sale_price_dates_to && '' === $_sale_price_dates_from ) {
					$_price = $_sale_price;
				} elseif ( '' !== $_sale_price && $_sale_price_dates_from && $_sale_price_dates_from <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					$_price = $_sale_price;
				} else {
					$_price = $_regular_price;					
				}

				if ( $_sale_price_dates_to && $_sale_price_dates_to < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					$_price = $_regular_price;
					$_sale_price = '';
					$_sale_price_dates_from = '';
					$_sale_price_dates_to 	= '';
				}							
			}

			update_post_meta( $post_id, $key_prefix . '_regular_price',  $_regular_price );							
			update_post_meta( $post_id, $key_prefix . '_sale_price', $_sale_price );				
			update_post_meta( $post_id, $key_prefix . '_price', $_price );							
			update_post_meta( $post_id, $key_prefix . '_price_method', $_price_method );
			update_post_meta( $post_id, $key_prefix . '_sale_price_dates', $_sale_price_dates );
			update_post_meta( $post_id, $key_prefix . '_sale_price_dates_from', $_sale_price_dates_from );
			update_post_meta( $post_id, $key_prefix . '_sale_price_dates_to', $_sale_price_dates_to );
			
			// Update parent if grouped so price sorting works and stays in sync with the cheapest child
			if ( isset( $_POST['parent_id'] ) && $_POST['parent_id'] > 0 ) {				
				wcpbc_zone_grouped_product_sync( $key, $_POST['parent_id'] );
			}
			if ( isset( $_POST['previous_parent_id'] ) &&  $_POST['previous_parent_id'] > 0 && ! ( isset( $_POST['parent_id'] ) && $_POST['parent_id'] == $_POST['previous_parent_id'] ) ) {				
				wcpbc_zone_grouped_product_sync( $key, $_POST['previous_parent_id'] );
			}
			
			do_action('wc_price_based_country_process_product_meta_' . $product_type , $post_id, $key_prefix, $value, $_price_method, $i );										
		}			
	}
	
	/**	
	 * Add price input to product variation metabox
	 */
	public static function product_variable_attributes_countries_prices( $loop, $variation_data, $variation ) {							

		foreach ( WCPBC()->get_regions() as $key => $value) {

			$_regular_price 		= wc_format_localized_price( get_post_meta( $variation->ID, '_' . $key . '_regular_price', true) );
			$_sale_price 			= wc_format_localized_price( get_post_meta( $variation->ID, '_' . $key . '_sale_price', true) );
			$_price_method 			= get_post_meta( $variation->ID, '_' . $key . '_price_method', true);
			$_sale_price_dates 		= get_post_meta( $variation->ID, '_' . $key . '_sale_price_dates' , true );							
			$_sale_price_dates_from = ( $date = get_post_meta( $variation->ID, '_' . $key . '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
			$_sale_price_dates_to   = ( $date = get_post_meta( $variation->ID, '_' . $key . '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
			
			$_price_method = empty($_price_method) ? 'exchange_rate' : $_price_method;			
			$_sale_price_dates = empty($_sale_price_dates) ? 'default' : $_sale_price_dates;
			
			?>
				<div class="wcpbc_pricing">					
					<p class="form-row form-row-first"><strong><?php echo __( 'Price for', 'wc-price-based-country' )  . ' ' . $value['name'] . ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')'; ?></strong></p>

					<div class="form-row form-row-last wcpbc_wrapper_variable_price_method <?php echo '_' . $key . '_variable_price_method_' . $loop . '_field'; ?>">
						<ul>
							<li>
								<label>
									<input name="<?php echo '_' . $key . '_variable_price_method[' . $loop . ']'; ?>" value="exchange_rate" class="wcpbc_price_method" <?php checked( $_price_method, 'exchange_rate' ); ?> type="radio">
									<?php _e('Calculate prices by exchange rate', 'wc-price-based-country') ?>
								</label>
							</li>
							<li>
								<label>
									<input name="<?php echo '_' . $key . '_variable_price_method[' . $loop . ']'; ?>" value="manual" class="wcpbc_price_method" <?php checked( $_price_method, 'manual' ); ?> type="radio">
									<?php _e('Set prices manually', 'wc-price-based-country') ?>
								</label>
							</li>
						</ul>
					</div>
					
					<div style="display: <?php echo ($_price_method == 'exchange_rate' ? 'none' : 'block' ); ?>" class="wcpbc_show_if_manual">
						
						<div class="wpbc_variable_pricing">
							
							<?php do_action('wc_price_based_country_before_product_variable_options_pricing', $key, $value['currency'], $loop, $variation ); ?>
							
							<p class="form-row form-row-first">
								<label><?php echo __( 'Regular Price:', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')'; ?></label>
								<input type="text" size="5" id="<?php echo '_' . $key . '_variable_regular_price_' . $loop; ?>" name="<?php echo '_' . $key . '_variable_regular_price[' . $loop. ']'; ?>" value="<?php if ( isset( $_regular_price ) ) echo esc_attr( $_regular_price ); ?>" class="wc_input_price" />
							</p>
							<p class="form-row form-row-last">
								<label><?php echo __( 'Sale Price:', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $value['currency'] ) . ')'; ?></label>
								<input type="text" size="5" id="<?php echo '_' . $key . '_variable_sale_price_' . $loop; ?>" name="<?php echo '_' . $key . '_variable_sale_price[' . $loop. ']'; ?>" value="<?php if ( isset( $_sale_price ) ) echo esc_attr( $_sale_price ); ?>" class="wc_input_price wcpbc_sale_price" />
							</p>						
														
							<p class="form-row form-row-first"><?php echo __( 'Sale price dates', 'wc-price-based-country' ); ?></p>
							<div class="form-row form-row-last wcpbc_wrapper_variable_sale_price_dates <?php echo '_' . $key . '_variable_sale_price_dates_' . $loop . '_field'; ?>">
								<ul>
									<li>
										<label>
											<input name="<?php echo '_' . $key . '_variable_sale_price_dates[' . $loop . ']'; ?>" value="default" class="wcpbc_sale_price_dates" <?php checked( $_sale_price_dates, 'default' ); ?> type="radio">
											<?php _e('Same as default price', 'wc-price-based-country') ?>
										</label>
									</li>
									<li>
										<label>
											<input name="<?php echo '_' . $key . '_variable_sale_price_dates[' . $loop . ']'; ?>" value="manual" class="wcpbc_sale_price_dates" <?php checked( $_sale_price_dates, 'manual' ); ?> type="radio">
											<?php _e('Set specific dates', 'wc-price-based-country') ?>
										</label>
									</li>
								</ul>
							</div>
							
							<div class="sale_price_dates_fields wcpbc_show_if_manual" style="display: <?php echo ($_sale_price_dates == 'default' ? 'none' : 'block' ); ?>">
								<p class="form-row form-row-first">
									<label><?php _e( 'Sale start date', 'woocommerce' ); ?></label>
									<input type="text" class="sale_price_dates_from" name="<?php echo '_' . $key . '_'; ?>variable_sale_price_dates_from[<?php echo $loop; ?>]" value="<?php echo $_sale_price_dates_from; ?>" placeholder="<?php echo esc_attr_x( 'From&hellip;', 'placeholder', 'woocommerce' ) ?> YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
								</p>
								<p class="form-row form-row-last">
									<label><?php _e( 'Sale end date', 'woocommerce' ); ?></label>
									<input type="text" class="sale_price_dates_to" name="<?php echo '_' . $key . '_'; ?>variable_sale_price_dates_to[<?php echo $loop; ?>]" value="<?php echo $_sale_price_dates_to; ?>" placeholder="<?php echo esc_attr_x('To&hellip;', 'placeholder', 'woocommerce') ?> YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
								</p>
							</div>					
							
						</div>

					</div>
					
				</div>

			<?php			
		}
	}	
	
	/**
	 * Save meta data product variation
	 */
	public static function save_product_variation_countries_prices( $variation_id, $i ) {		
		self::process_product_simple_countries_prices( $variation_id, $i);		
	}
	
	/**
	 * Save meta data product grouped
	 */
	public static function process_product_meta_grouped( $post_id ) {
		foreach ( WCPBC()->get_regions() as $key => $value ) {
			wcpbc_zone_grouped_product_sync( $key,  $post_id );
		}		
	}
	
	/**
	 * Quick and Bulk product edit.
	 */
	public static function product_quick_bulk_edit_save( $product ) {
						
		foreach ( WCPBC()->get_regions() as $key => $value ) {
				
			$key_prefix = '_' . $key;				
			$price_method_prop = $key . '_price_method';
						
			if ( $product->$price_method_prop == 'exchange_rate' || ! $product->$price_method_prop ) {				
				
				$regular_price 	= get_post_meta( $product->id, '_regular_price', true );
				$sale_price 	= get_post_meta( $product->id, '_sale_price', true );
				$price 			= get_post_meta( $product->id, '_price', true );
				
				update_post_meta( $product->id, $key_prefix . '_regular_price', $regular_price !== '' ? $regular_price * $value['exchange_rate'] : '' );							
				update_post_meta( $product->id, $key_prefix . '_sale_price', $sale_price !== '' ? $sale_price * $value['exchange_rate'] : '' );				
				update_post_meta( $product->id, $key_prefix . '_price', $price !== '' ? $price * $value['exchange_rate']  : '' );						
				
				// Update parent if grouped so price sorting works and stays in sync with the cheapest child
				if ( $product->get_parent() > 0 && $product->is_type('simple') ) {
					wcpbc_zone_grouped_product_sync( $key,  $product->get_parent() );
				}
				
				do_action( 'wc_price_based_country_quick_or_bulk_edit_save_' . $product->product_type, $product, $key_prefix, $value );
			}						
		}
	}
	
	/**
	 * Bulk edit variations via AJAX.
	 */
	public static function bulk_edit_variations( $bulk_action, $data, $product_id, $variations ) {		
		$actions = array('variable_regular_price', 'variable_sale_price', 'variable_sale_schedule', 'variable_regular_price_increase', 'variable_regular_price_decrease', 'variable_sale_price_increase', 'variable_sale_price_decrease');		
		
		if ( in_array( $bulk_action, $actions) ) {
			
			foreach ( WCPBC()->get_regions() as $zone_id => $zone_data ) {
				
				$meta_prefix = '_' . $zone_id;
				
				foreach ( $variations as $variation_id ) {
					$price_method = get_post_meta( $variation_id, $meta_prefix . '_price_method', true );
					if ( $price_method == 'exchange_rate' || ! $price_method ) {
						
						$price 			= get_post_meta( $variation_id, '_price', true );
						$regular_price 	= get_post_meta( $variation_id, '_regular_price', true );
						$sale_price 	= get_post_meta( $variation_id, '_sale_price', true );
						
						update_post_meta( $variation_id, $meta_prefix . '_regular_price', $regular_price !== '' ? $regular_price * $zone_data['exchange_rate'] : '' );							
						update_post_meta( $variation_id, $meta_prefix . '_sale_price', $sale_price !== '' ? $sale_price * $zone_data['exchange_rate'] : '' );				
						update_post_meta( $variation_id, $meta_prefix . '_price', $price !== '' ? $price * $zone_data['exchange_rate']  : '' );						
					}
				}				
			}
		}
	}
}

WCPBC_Admin_Product_Data::init();