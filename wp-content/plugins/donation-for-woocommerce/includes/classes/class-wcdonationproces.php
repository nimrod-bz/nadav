<?php

class WcdonationProces {

	public static $addDonationToOrderActionName;
	
	public function __construct() {
		
		self::$addDonationToOrderActionName = 'donation_to_order';

		// loading scripts and css on frontend and backend
		add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminScripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'loadFrontendScripts' ) );

		//display donation on cart & checkout page
		add_action('woocommerce_after_cart_table', array($this, 'display_wc_donation_on_cart'), 10, 0);
		add_action('woocommerce_review_order_before_payment', array($this, 'display_wc_donation_on_checkout'), 10, 0);

		//display roundoff donation before order proceed.
		add_action('wp_ajax_add_popup_before_order', array( $this, 'add_popup_before_order' ), 5, 0 );
		add_action('wp_ajax_nopriv_add_popup_before_order', array( $this, 'add_popup_before_order' ), 5, 0 );
		add_action('wp_footer', array( $this, 'render_donation_popup' ), 10 );

		// add donation values on shop order page list.
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_donation_in_order_column_header_admin'), 20 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_donation_values_in_order_column_admin'), 25 );
	}

	/**
	 * Adds 'Profit' column header to 'Orders' page immediately after 'Total' column in wp admin.
	 *
	 * @param string[] $columns
	 * @return string[] $new_columns
	 */
	public function add_donation_in_order_column_header_admin ( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'order_status' === $column_name ) {
				$new_columns['order_donation'] = __( 'Donation', 'wc-donation' );				
			}
		}

		return $new_columns;
	}

	/**
	 * Set values for each 'donation' column in Orders in wp admin
	 */
	public function add_donation_values_in_order_column_admin ( $column ) {
		global $post;

		if ( 'order_donation' === $column ) { 
			$order    = wc_get_order( $post->ID );
			$donation = 0;
			//$currency = get_woocommerce_currency_symbol();			
			$currency = get_woocommerce_currency_symbol($order->get_currency());			

			foreach ( $order->get_items() as $item_id => $item ) { 
				$product_id = $item->get_product_id();

				$type = get_post_meta($product_id, 'is_wc_donation', true);

				if ( ! empty($type) && 'donation' == $type ) {
					$total = $item->get_total();
					$donation += $total;
				}
				
				// $type = wc_get_order_item_meta( $item_id, 'campaign_type', true);
				
				// if ( ! empty(trim( $type )) ) {
				// 	$total = $item->get_total();
				// 	$donation += $total;
				// }
			}

			/*echo '<pre>';
				print_r($order->get_items());
			echo '</pre>';*/
			if ($donation > 0) {
				echo '<mark class="wc-order-donation-completed "><span>' . esc_html__($currency . $donation, 'wc-donation') . '<span></mark>';
			} else {
				echo '-';
			}
			
		}
	}

	public function render_donation_popup () {
		
		$donation_roundoff = get_option( 'wc-donation-on-round' );
		$campaign_id = !empty( esc_attr( get_option( 'wc-donation-round-product' ))) ? esc_attr( get_option( 'wc-donation-round-product' )) : '';
		$object = WcdonationCampaignSetting::get_product_by_campaign($campaign_id);
		
		if ( 'yes' === $donation_roundoff && is_checkout() ) {
			require_once( WC_DONATION_PATH . 'includes/views/frontend/frontend-round-off-donation.php' );
		}
	}


	private function closestNumber ( $n, $m ) {  
		// find the quotient  
		$q = (int) ( $n / $m );  
		  
		// 1st possible closest number  
		//$n1 = $m * $q;  
		  
		// 2nd possible closest number  
		$n2 = ( $n * $m ) > 0 ? ( $m * ( $q + 1 ) ) : ( $m * ( $q - 1 ) );  
		  
		// if true, then n1 is the  
		// required closest number  
		//if (abs($n - $n1) < abs($n - $n2))  
			//return $n1;  
		  
		// else n2 is the required  
		// closest number  
		return $n2;  
	} 

	public function add_popup_before_order () {

		if ( isset( $_POST['add_popup_before_order_nonce'] ) && wp_verify_nonce(sanitize_text_field($_POST['add_popup_before_order_nonce']), self::$addDonationToOrderActionName )) {
			exit( 'Not Authorized' );
		}

		do_action ('add_popup_before_order');
		
		$donation_roundoff = get_option( 'wc-donation-on-round' );
		
		if ( 'yes' == $donation_roundoff ) { //&& !class_exists('WCCS') 
			$campaign_id = !empty( esc_attr( get_option( 'wc-donation-round-product' ))) ? esc_attr( get_option( 'wc-donation-round-product' )) : '';
			
			$post_exist = get_post( $campaign_id );
			if ( empty($post_exist) || ( isset($post_exist->post_status) && 'trash' == $post_exist->post_status ) ) {
				$response['status'] = 'failed';
				$response['campaign_id'] = $campaign_id;
				echo json_encode ($response);
				wp_die();
			}
			
			global $woocommerce;  
			$total_price = $woocommerce->cart->total;

			$price_break = explode( '.', $total_price);

			if ( ''!=$price_break[1] ) { 
				$decimal_num = (int) $price_break[1];
				if ( $decimal_num > 0 ) { 
					//check if decimal place set is 1 or 2 in woo setting
					$decimal_place = get_option('woocommerce_price_num_decimals');

					//roundoff multiplier
					$round_multiplier = absint(get_option('wc-donation-round-multiplier'));

					//if tries to pass number less than 0
					if ($round_multiplier <= 0) {
						$round_multiplier = 1;
					}

					$num = $this->closestNumber($total_price, $round_multiplier);

					$added_donation_price = $num - $total_price;

					if ($added_donation_price > 0 ) {
						$response['status'] = 'success';
						$response['donation'] = round($added_donation_price, $decimal_place);
						$response['campaign_id'] = $campaign_id;
					} else {
						$response['status'] = 'failed';
						$response['donation'] = 0;
						$response['campaign_id'] = $campaign_id;
						
					}
					echo json_encode ($response);
					wp_die();
				} else {
					$response['status'] = 'failed';
					$response['campaign_id'] = $campaign_id;
					echo json_encode ($response);
					wp_die();
				}
			} else {
				$response['status'] = 'failed';
				$response['campaign_id'] = $campaign_id;
				echo json_encode ($response);
				wp_die();
			}

		} else { 
			$response['status'] = 'failed';
			$response['campaign_id'] = $campaign_id;
			echo json_encode ($response);
			wp_die();
		}
		
	}

	public function loadAdminScripts() {

		$parameters = array (
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'amount_l_text' => esc_html__( 'Amount', 'wc-donation' ),
			'amount_p_text' => esc_html__( 'Enter Amount', 'wc-donation' ),
			'label_l_text' => esc_html__( 'Label', 'wc-donation' ),
			'label_p_text' => esc_html__( 'Enter Label', 'wc-donation' ),
			'donation_level_text' => esc_html__( 'Donation Level', 'wc-donation' ),
			'no_cause_img' => WC_DONATION_URL . 'assets/images/no-image-cause.jpg'
		);
		wp_register_script( 'wc-donation-admin-script', WC_DONATION_URL . 'assets/js/admin.js', array( 'jquery' ), WC_DONATION_VERSION . '&t=' . gmdate('dmYhis'), true );		
		wp_localize_script( 'wc-donation-admin-script', 'wcds', $parameters );
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script( 'wc-donation-admin-script' );
		wp_enqueue_script( 'wc-donation-jscolor-script', WC_DONATION_URL . 'assets/js/jscolor.js', array( 'jquery' ), WC_DONATION_VERSION . '&t=' . gmdate('dmYhis'), true );		
		wp_enqueue_style( 'trustseal_style', WC_DONATION_URL . '/assets/css/admin-wc-donation-form.css', array(), WC_DONATION_VERSION );
		wp_enqueue_style( 'wc-donation-jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), WC_DONATION_VERSION );
	}

	public function loadFrontendScripts() {
		$parameters = array(
			'donationToOrder' =>
			apply_filters ( 'wc_donation_localize_script', array(
				'action'  => self::$addDonationToOrderActionName,
				'nonce'   => wp_create_nonce( self::$addDonationToOrderActionName ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'is_roundOff' => get_option('wc-donation-on-round'),
				'other_amount_placeholder' => apply_filters( 'wc_donation_other_amount_placeholder', esc_html__( 'Enter amount between %min% - %max%', 'wc-donation'), '%min%', '%max%'),
			)),
		);
		wp_register_script( 'wc-donation-frontend-script', WC_DONATION_URL . 'assets/js/frontend.js', array( 'jquery' ), WC_DONATION_VERSION . '&t=' . gmdate('dmYhis'), true );
		wp_localize_script( 'wc-donation-frontend-script', 'wcOrderScript', $parameters );
		wp_enqueue_style( 'trustseal_style', WC_DONATION_URL . '/assets/css/user-wc-donation-form.css', array(), WC_DONATION_VERSION . '&t=' . gmdate('dmYhis') );
		wp_enqueue_script( 'wc-donation-frontend-script' );
	}

	public function display_wc_donation_on_checkout () {

		$donation_on_checkout = get_option( 'wc-donation-on-checkout' );
		$campaign_id = !empty( esc_attr( get_option( 'wc-donation-checkout-product' ))) ? esc_attr( get_option( 'wc-donation-checkout-product' )) : '';

		if ( 'yes' === $donation_on_checkout ) {
			$post_exist = get_post( $campaign_id );
			if ( !empty($post_exist) && ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$object = WcdonationCampaignSetting::get_product_by_campaign($campaign_id);
				$type = 'checkout';
				echo '<div class="wc_donation_on_checkout" id="wc_donation_on_checkout">';
				do_action ('wc_donation_before_checkout_add_donation', $campaign_id);
				if ( 'yes' === $donation_on_checkout ) {	
					require_once(WC_DONATION_PATH . '/includes/views/frontend/frontend-order-donation.php');
				}
				do_action ('wc_donation_after_checkout_add_donation', $campaign_id);
				echo '</div>';
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				return;
			}
		}
		
	}

	public function display_wc_donation_on_cart () {

		$donation_on_cart = get_option( 'wc-donation-on-cart' );
		$campaign_id = !empty( esc_attr( get_option( 'wc-donation-cart-product' ))) ? esc_attr( get_option( 'wc-donation-cart-product' )) : '';

		if ( 'yes' === $donation_on_cart ) {
			$post_exist = get_post( $campaign_id );
			if ( !empty($post_exist) && ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$object = WcdonationCampaignSetting::get_product_by_campaign($campaign_id);
				$type = 'cart';
				echo '<div class="wc_donation_on_cart" id="wc_donation_on_cart">';
				do_action ('wc_donation_before_cart_add_donation', $campaign_id);
				if ( 'yes' === $donation_on_cart ) {
					require_once(WC_DONATION_PATH . '/includes/views/frontend/frontend-order-donation.php');
				}
				do_action ('wc_donation_after_cart_add_donation', $campaign_id);
				echo '</div>';
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				return;
			}
		}
	}
}

new WcdonationProces();
