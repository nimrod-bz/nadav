<?php 
/**
 * Form Setting HTML
 */

$DonationDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-display-donation-type', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-display-donation-type', true  ) : 'select'; 
$currencyPos = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-currency-position', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-currency-position', true  ) : 'before'; 
$donationTitle = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-title', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-title', true  ) : ''; 

$donationBtnTxt = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-text', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-text', true  ) : 'Donate'; 
$donationBtnTxtColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-text-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-text-color', true  ) : 'FFFFFF'; 
$donationBtnBgColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-bg-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-bg-color', true  ) : '333333'; 
?>

<div class="select-wrapper">
	<label for="wc-donation-display-donation-type" class="wc-donation-label"><?php echo esc_attr( __( 'Display Type', 'wc-donation' ) ); ?></label>
	<select name='wc-donation-display-donation-type' id="wc-donation-display-donation-type">
		<option value="">--Select Display Type--</option>
		<?php
		foreach ( WcDonation::DISPLAY_DONATION_TYPE() as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' .
			selected( $DonationDisp, $key ) . '>' .
			esc_attr( $value ) . '</option>';
		}
		?>
	</select>
</div>

<div class="select-wrapper">
	<label for="wc-donation-currency-position" class="wc-donation-label"><?php echo esc_attr( __( 'Currency Position', 'wc-donation' ) ); ?></label>
	<select name='wc-donation-currency-position' id="wc-donation-currency-position">
		<option value="">--Select Currency Position--</option>
		<?php
		foreach ( WcDonation::CURRENCY_SIMBOL() as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' .
			selected( $currencyPos, $key ) . '>' .
			esc_attr( $value ) . '</option>';
		}
		?>
	</select>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-title"><?php echo esc_attr( __( 'Donation Field Label', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-title" Placeholder="<?php echo esc_html__('Enter Donation Field Label', 'wc-donation'); ?>" name="wc-donation-title" value="<?php echo esc_attr($donationTitle); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-text"><?php echo esc_attr( __( 'Donation Button Label', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-button-text" Placeholder="<?php echo esc_html__('Enter Donation Button Label', 'wc-donation'); ?>" name="wc-donation-button-text" value="<?php echo esc_attr($donationBtnTxt); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-text-color"><?php echo esc_attr( __( 'Donation Button Text Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-button-text-color" name="wc-donation-button-text-color" value="<?php echo esc_attr($donationBtnTxtColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-bg-color"><?php echo esc_attr( __( 'Donation Button Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-button-bg-color" name="wc-donation-button-bg-color" value="<?php echo esc_attr($donationBtnBgColor); ?>">
</div>
