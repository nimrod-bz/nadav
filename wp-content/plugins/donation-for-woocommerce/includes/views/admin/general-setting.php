<form method="post" class="wc-donation-form" action="options.php">
	<?php
	/**
	 * Admin settings.
	 *
	 * @package donation
	 */

	settings_fields( 'wc-donation-general-settings-group' );
	do_settings_sections( 'wc-donation-general-settings-group' );
	$campaigns = get_posts(array(
		'fields'          => 'ids',
		'posts_per_page'  => -1,
		'post_type' => 'wc-donation'
	));
	$donation_on_checkout = get_option( 'wc-donation-on-checkout' );
	$donation_on_cart = get_option( 'wc-donation-on-cart' );
	$donation_on_round = get_option( 'wc-donation-on-round' );
	$donation_card_fee = get_option( 'wc-donation-card-fee' );
	$donation_recommended_products = get_option( 'wc-donation-recommended' );

	//print_r(get_option('wc-donation-cart-product', true));
	//wp_die('stop');

	$donation_round_multiplier = get_option( 'wc-donation-round-multiplier' );
	$donation_fees_percent = get_option( 'wc-donation-fees-percent' );
	$donation_label  = !empty( esc_attr( get_option( 'wc-donation-round-field-label' ))) ? esc_attr( get_option( 'wc-donation-round-field-label' )) : 'Donation';
	$donation_message  = !empty( esc_attr( get_option( 'wc-donation-round-field-message' ))) ? esc_attr( get_option( 'wc-donation-round-field-message' )) : '';
	$donation_fees_message  = !empty( esc_attr( get_option( 'wc-donation-fees-field-message' ))) ? esc_attr( get_option( 'wc-donation-fees-field-message' )) : '';
	$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-round-button-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-text' )) : 'Donate';
	$donation_button_cancel_text  = !empty( esc_attr( get_option( 'wc-donation-round-button-cancel-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-cancel-text' )) : 'Skip';
	$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-color' ))) ? esc_attr( get_option( 'wc-donation-round-button-color' )) : 'd5d5d5';
	$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-text-color' ))) ? esc_attr( get_option( 'wc-donation-round-button-text-color' )) : '000000';

	?>

	<h1 class="wc-main-title"><?php echo esc_html__('General Setting', 'wc-donation'); ?></h1>
	<div class="sep20px">&nbsp;</div>

	<div class="wc-block-setting-wrapper">
		<div class="wc-block-setting">
			<h3><?php echo esc_html__('Cart Donation', 'wc-donation'); ?></h3>
			<?php 
			if ( 'yes' === $donation_on_cart ) {
				$checked_for_cart = 'checked';
			} 
			
			?>
			<div class="select-wrapper">
				<label class="wc-donation-switch">
					<input id="wc-donation-on-cart" name="wc-donation-on-cart" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_cart); ?>>
					<span class="wc-slider round"></span>
				</label>
				<label for="wc-donation-on-cart" class="wc-text-label"><?php echo esc_attr( __( 'Show Donation form on cart', 'wc-donation' ) ); ?></label>
			</div>

			<div class="select-wrapper">
				<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-cart-product'>
					<option><?php echo esc_html(__('select Campaign', 'wc-donation')); ?></option>
					<?php
					foreach ( $campaigns as $campaign ) {
						echo '<option value="' . esc_attr( $campaign ) . '"' .
						selected( get_option( 'wc-donation-cart-product' ), $campaign ) . '>' .
						esc_attr( get_the_title($campaign) ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>

		<div class="wc-block-setting">
			<h3><?php echo esc_html__('Checkout Donation', 'wc-donation'); ?></h3>
			<?php 
			if ( 'yes' === $donation_on_checkout ) {
				$checked_for_checkout = 'checked';
			} 
			
			?>
			<div class="select-wrapper">
				<label class="wc-donation-switch">
					<input id="wc-donation-on-checkout" name="wc-donation-on-checkout" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_checkout); ?>>
					<span class="wc-slider round"></span>
				</label>
				<label for="wc-donation-on-checkout" class="wc-text-label"><?php echo esc_attr( __( 'Show Donation form on checkout', 'wc-donation' ) ); ?></label>
			</div>

			<div class="select-wrapper">
				<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-checkout-product'>
					<option><?php echo esc_html(__('Select Campaign', 'wc-donation')); ?></option>
					<?php
					foreach ( $campaigns as $campaign ) {
						echo '<option value="' . esc_attr( $campaign ) . '"' .
						selected( get_option( 'wc-donation-checkout-product' ), $campaign ) . '>' .
						esc_attr( get_the_title($campaign) ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>

		<?php /*if ( !class_exists('WCCS') ) { */ ?>

		<div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Round Off Donation', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_on_round ) {
					$checked_for_round = 'checked';
				} 
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-on-round" name="wc-donation-on-round" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_round); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-on-round" class="wc-text-label"><?php echo esc_attr( __( 'Round Off Donation', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
					<select class='select short' style="width:200px;" name='wc-donation-round-product'>
						<option><?php echo esc_html(__('select Campaign', 'wc-donation')); ?></option>
						<?php
						foreach ( $campaigns as $campaign ) {
							$object = WcdonationCampaignSetting::get_product_by_campaign($campaign);
							$goalDisp = !empty( $object->goal['display'] ) ? $object->goal['display'] : '';
							if ( 'enabled' !== $goalDisp ) {
								?>
								<option value="<?php echo esc_attr($campaign); ?>" <?php selected( get_option( 'wc-donation-round-product'), $campaign ); ?>><?php echo esc_attr( get_the_title($campaign) ); ?></option>
								<?php
							}							
						}
						?>
					</select>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Round Off Multiplier', 'wc-donation' ); ?><br><small style="color:#777"><?php echo esc_attr__( '(number should be greater than 0. if empty or other value than integer, it will be considered as 1)', 'wc-donation' ); ?></small></label>
					<input type="number" min="1" value="<?php echo esc_attr($donation_round_multiplier); ?>" name="wc-donation-round-multiplier" id="wc-donation-round-multiplier" />
				</div>

				<!-- <div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Popup title', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_label); ?>" name="wc-donation-round-field-label" id="wc-donation-round-field-label" />
				</div> -->				

			</div>

			<div class="wc-block-setting">

				<!-- <div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Button Text', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_button_text); ?>" name="wc-donation-round-button-text" id="wc-donation-round-button-text" />
				</div> -->

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Popup Message (use %amount% to show dynamic donation value in message)', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-round-field-message" id="wc-donation-round-field-message" cols="30" rows="5" style="resize:none"><?php echo esc_attr($donation_message); ?></textarea>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Cancel Button Text', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_button_cancel_text); ?>" name="wc-donation-round-button-cancel-text" id="wc-donation-round-button-cancel-text" />
				</div>

				<!-- <div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Button Color', 'wc-donation' ); ?></label>
					<input type="text" class="jscolor" value="<?php echo esc_attr($donation_button_color); ?>" name="wc-donation-round-button-color" id="wc-donation-round-button-color" />
				</div> -->

				<!-- <div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Button Text Color', 'wc-donation' ); ?></label>
					<input type="text" class="jscolor" value="<?php echo esc_attr($donation_button_text_color); ?>" name="wc-donation-round-button-text-color" id="wc-donation-round-button-text-color" />
				</div> -->

			</div>

		</div>

		<div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Credit Card Processing Fees', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_card_fee ) {
					$checked_for_fee = 'checked';
				} 
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-card-fee" name="wc-donation-card-fee" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_fee); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-card-fee" class="wc-text-label"><?php echo esc_attr( __( 'Credit Card Processing Fees', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label><?php echo esc_attr( __( 'Select Campaigns', 'wc-donation' ) ); ?></label>
					<?php //wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' ); ?>
					<?php //wp_enqueue_style('woocommerce_admin_styles'); ?>
					<?php wp_enqueue_script('selectWoo'); ?>
					<select class='select short fee_campaign' name='wc-donation-fees-product[]' multiple>
						
						<?php
						foreach ( $campaigns as $campaign ) {
							$get_fee_campaign = get_option('wc-donation-fees-product');
							if ( !is_array( $get_fee_campaign ) ) {
								$get_fee_campaign = array();
							}
							?>
							<option value="<?php echo esc_attr($campaign); ?>" <?php selected( in_array( $campaign, $get_fee_campaign ) ); ?>><?php echo esc_attr( get_the_title($campaign) ); ?></option>
							<?php							
						}
						?>
					</select>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Enter Percentage', 'wc-donation' ); ?><br><small style="color:#777"><?php echo esc_attr__( '(Enter processing fees percentage to be charged)', 'wc-donation' ); ?></small></label>
					<input type="number" min="1" value="<?php echo esc_attr($donation_fees_percent); ?>" name="wc-donation-fees-percent" id="wc-donation-fees-percent" />
				</div>

			</div>

			<div class="wc-block-setting">

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Enter Processing Fees Text', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-fees-field-message" id="wc-donation-fees-field-message" cols="30" rows="5" style="resize:none"><?php echo esc_attr($donation_fees_message); ?></textarea>
				</div>
			</div>
		</div>
		<!-- <div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Turn Off Donation', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_recommended_products ) {
					$checked_for_recommended = 'checked';
				} 
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-recommended" name="wc-donation-recommended" type="checkbox" value="yes" <?php echo esc_html__( @$checked_for_recommended ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-recommended" class="wc-text-label"><?php echo esc_html__( 'Turn off donation in cart page', 'wc-donation' ); ?></label>
				</div>

				<div class="select-wrapper">
					<label><?php echo esc_html__( 'Turn off recommended donation products on cart page if donation is already added in the cart', 'wc-donation' ); ?></label>
				</div>

			</div>
		</div> -->

		<?php /*} else { */ ?>

			<!-- <div class="wc-block-setting wc-grid-full">
				<div class="wc-block-setting">
					<h3><?php echo esc_html__('Round Off Donation', 'wc-donation'); ?></h3>

					<p class="wc-donation-error"><?php echo esc_html__('You cannot use round off donation with wc currency switcher.', 'wc-donation'); ?></p>
				</div>
			</div> -->
		
		<?php /*}*/ ?>

	</div> <!--end of wrapper-->

	<?php submit_button(); ?>

</form>
