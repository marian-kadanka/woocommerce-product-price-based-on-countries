<?php
/**
 * Country selector form
 *
 * @author 		oscargare
 * @version     1.6.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'wcpbc_manual_country_script' ) ) {

	function wcpbc_manual_country_script() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ){
				$('.wcpbc-widget-country-selecting').on('change', 'select.country', function(){
					$(this).closest('form').submit();
				} );
			} );	
		</script>
		<?php
	}
}

add_action( 'wp_print_footer_scripts', 'wcpbc_manual_country_script' );


if ( $countries ) : ?>
		
	<form method="post" class="wcpbc-widget-country-selecting">		
		<select class="country" name="wcpbc-manual-country">
			<?php foreach ($countries as $key => $value) : ?>
				<option value="<?php echo $key?>" <?php selected($key, $selected_country ); ?> ><?php echo $value; ?></option>
			<?php endforeach; ?>
		</select>					
	</form>			

<?php endif; ?>