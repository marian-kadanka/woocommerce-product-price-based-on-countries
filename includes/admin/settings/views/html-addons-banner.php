<?php
	$features = array(
		'Automatic updates of exchange rates.',
		'Display the currency code next to price.',
		'Thousand separator, decimal separator and number of decimals by pricing zone.',
		'Currency switcher widget.',
		'Support to WooCommerce Subscriptions.',
		'Support to WooCommerce Product Bundles.',
		'Support to WooCommerce Product Add-ons.',
		'More features and integrations is coming.',
		'No ads.'
	);
?>
<div class="wc_addons_wrap wcpbc_addon_banner">
	<ul class="products">
		<li class="product" style="float:none;">
			<a href="http://www.pricebasedcountry.com/product/avanced-currency-options/?utm_source=pbc-settings&utm_medium=banner&utm_campaign=Extend" target="_blank">
				<h2><span class="dashicons dashicons-star-filled"></span><span class="feature_text">Upgrade to Pro version</span></h2>				
				<ul class="wcpbc_addon_banner_features">
					<?php 
					foreach ( $features as $feature ) {
						echo '<li><span class="dashicons dashicons-yes"></span><span class="feature_text">' . $feature . '</span></li>';					
					} 
					?>					
				</ul>
			</a>
		</li>		
	</ul>	
</div>
<div style="padding-left:20px;">
	<a class="button button-primary" href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro">Upgrade to Pro version now!</a>
</div>