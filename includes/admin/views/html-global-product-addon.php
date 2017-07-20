<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table style="display:none;">	
	<tr id="wcpbc-addon-pricing">
		<th>
			<label for="wcpbc-addons"><?php _e( 'Add-ons zone pricing', 'wc-price-based-country' ); ?></label>
		</th>
		<td class="postbox">
			<?php echo $get_pro; ?>
		</td>
	</tr>
</table>
<script type="text/javascript">
	jQuery(document).ready(function($){			
		// Insert addon pricing
		$('#poststuff').closest('tr').after('<tr id="clear"><th></th><td></td></tr>')		
		$('#wcpbc-addon-pricing').insertAfter($('#clear'));		
		
	});
</script>