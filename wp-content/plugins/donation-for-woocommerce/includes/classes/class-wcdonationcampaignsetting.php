<?php
/**
 * File to  define settings for each campaign.
 *
 * @package donation
 */

 /**
 *  Class   WcdonationCampaignSetting.
 *  Add plugin settings .
 */
class WcdonationCampaignSetting {

	/**
	 * Campaign ID .
	 *
	 * @var type
	 */
	private $campaign_id;

	/**
	 * Constructor function .
	 */
	public function __construct () {

		$this->campaign_id = isset( $_REQUEST['post'] ) ? sanitize_text_field($_REQUEST['post']) : '';

		// adding metabox for dispaly shop/single page setting.
		add_action( 'add_meta_boxes', array( $this, 'wc_donation_meta') );

		// saving details for each campaign on save post and update post.
		add_action( 'post_updated', array( $this, 'wc_donation_save_campaigns_details'), 99, 3 );

		add_action( 'before_delete_post', array( $this, 'wc_donation_delete_campaign_from_db' ), 99, 2 );

		add_action( 'init', array( $this, 'register_render_wc_donation_campaign_shortcode') );

		//add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'woocommerce_simple_add_to_cart_remove'), 29 );

		//add_action('wp_enqueue_scripts', array( $this, 'remove_loop_button') );
		add_filter('woocommerce_loop_add_to_cart_link', array( $this, 'remove_loop_button'), 99, 3);

		add_action ( 'template_redirect', array( $this, 'no_ways'), 999 );

		add_action( 'init', array( $this, 'wc_donation_register_gutenberg_blocks') );
		
	}

	public function wc_donation_register_gutenberg_blocks () {
		$args = array(
			'numberposts' => -1,
			'post_type'   => 'wc-donation',
		);
		$all_campaigns = get_posts( $args );
		$donation_forms = array();
		$count = 0;
		foreach ( $all_campaigns as $campaign ) {
			$donation_forms[$count]['ID'] = $campaign->ID;
			$donation_forms[$count]['title'] = $campaign->post_title;
			$count++;
		}
		wp_register_script( 'wc_shortcode_block', WC_DONATION_URL . 'assets/js/gutenberg_shortcode_block/build/index.js', array( 'wp-blocks' ), WC_DONATION_VERSION );
		wp_enqueue_script( 'wc_shortcode_block' );
		$wc_donation_forms = array(
			'forms' => $donation_forms,
		);
		wp_localize_script( 'wc_shortcode_block', 'wc_donation_forms', $wc_donation_forms );
		
		register_block_type( 'wc-donation/shortcode', array(
			'editor_script'   => 'wc_shortcode_block',
			'render_callback' => array($this, 'wc_donation_custom_gutenberg_render_html'),
		));
	}

	public function wc_donation_custom_gutenberg_render_html ( $attributes ) {
		$shortcode = '[wc_woo_donation id="' . $attributes['type'] . '"]';
		return $shortcode;
	}

	public function remove_loop_button ( $button, $product, $arg = '' ) {
		
		if ( !empty( $product ) ) {
			
			$prodID = $product->get_id();
			$is_wc_donation = get_post_meta($prodID, 'is_wc_donation', true);
			$url = get_permalink( $prodID );

			if ( 'donation' == $is_wc_donation ) {

				$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($prodID);
				$object = self::get_product_by_campaign($campaign_id);
				
				if ( isset($object) && ! empty($object) && isset($object->campaign['donationBtnTxt']) ) { 
					
					do_action ('wc_donation_before_archive_add_donation_button');
					
					$button = sprintf( '<a href="%s" data-quantity="1" class="%s">%s</a>',
					esc_url( $url ),
					esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
					$object->campaign['donationBtnTxt'] );
					
					do_action ('wc_donation_after_archive_add_donation_button');

				}   
			}
		}

		return $button;
	}

	public function woocommerce_simple_add_to_cart_remove() {

		global $post;
		
		if ( !empty( $post ) ) { 
			$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
			$object = self::get_product_by_campaign($campaign_id);

			if ( isset($object) && ! empty($object) ) { 
				
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_subscription_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

				if ( class_exists('WC_Subscriptions') ) {
					remove_action( 'woocommerce_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
					remove_action( 'woocommerce_variable-subscription_add_to_cart', 'WC_Subscriptions::variable_subscription_add_to_cart', 30 );
				}

				add_action('woocommerce_single_product_summary', array( $this, 'wc_donation_single_template' ), 999 );  
				
			}
		}
	}

	public function no_ways () {

		global $post;		
		if ( !empty( $post ) ) { 
			
			$prod_id = get_post_meta( $post->ID, 'wc_donation_product', true );
			if ( ! empty($prod_id) ) {
				$url = get_permalink( $prod_id );
				wp_safe_redirect($url);
				exit();
			}

			$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
			$object = self::get_product_by_campaign($campaign_id);
			if ( isset($object->product['is_single']) && 'no' == $object->product['is_single'] ) {				
				$url = home_url();
				wp_safe_redirect($url);
				exit();       
				
			}
		}
	}

	public function wc_donation_single_template () {
		
		global $post;

		if ( !empty( $post ) ) { 
			$post_exist = get_post( $post->ID );
			if ( !empty($post_exist) && ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
				$object = self::get_product_by_campaign($campaign_id);
				$type = 'single';
				echo '<div class="widget_wc-donation-widget" id="wc_donation_on_single_' . esc_html($campaign_id) . '">';
				do_action ('wc_donation_before_single_add_donation', $campaign_id, $post->ID);
				require WC_DONATION_PATH . '/includes/views/frontend/frontend-order-donation.php';
				do_action ('wc_donation_after_single_add_donation', $campaign_id, $post->ID);
				echo '</div>';
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				return;
			}
		}
	}

	public function register_render_wc_donation_campaign_shortcode () {

		add_shortcode( 'wc_woo_donation', array( $this, 'render_wc_donation_campaign') );

	}

	public function render_wc_donation_campaign ( $atts ) {

		ob_start();

		//checking backward compatibility
		$old_product_id = get_option( 'wc-donation-widget-product');
		if ( !is_admin() && !isset($atts['id']) && !empty($old_product_id) ) {			
			$atts = array();
			$atts['id'] = get_post_meta($old_product_id, 'wc_donation_campaign', true);
		}

		if ( !is_admin() && isset( $atts['id'] ) && ! empty( $atts['id'] ) ) { 

			$post_exist = get_post( $atts['id'] );

			if ( !empty($post_exist) || ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$campaign_id = $atts['id'];
				$object = self::get_product_by_campaign($campaign_id);
				$type = 'shortcode';
				echo '<div class="widget_wc-donation-widget" id="wc_donation_on_shortcode_' . esc_html($campaign_id) . '">';
				do_action ('wc_donation_before_shortcode_add_donation', $campaign_id);
				require WC_DONATION_PATH . '/includes/views/frontend/frontend-order-donation.php';
				do_action ('wc_donation_after_shortcode_add_donation', $campaign_id);
				echo '</div>';
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
			}
		} else {
			/* translators: %1$s refers to html tag, %2$s refers to html tag */
			printf(esc_html__('%1$s Please enter correct shortcode %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
		}

		return ob_get_clean();

	}

	/**
	 * Delete all data related to campaign
	 */
	public function wc_donation_delete_campaign_from_db ( $post_id = '', $post = '' ) {
		// We check if the global post type isn't ours and just return
		global $post_type;

		if ( 'wc-donation' !== $post_type ) {
			return;
		}

		$prod_id = get_post_meta( $post_id, 'wc_donation_product', true );

		$cart_donation = get_option('wc-donation-cart-product');
		$checkout_donation = get_option('wc-donation-checkout-product');
		$round_donation = get_option('wc-donation-round-product');

		if ( $cart_donation == $post_id ) {
			update_option ('wc-donation-cart-product', '');
			update_option ('wc-donation-on-cart', 'no');
		}

		if ( $checkout_donation == $post_id ) {
			update_option ('wc-donation-checkout-product', '');
			update_option ('wc-donation-on-checkout', 'no');
		}

		if ( $round_donation == $post_id ) {
			update_option ('wc-donation-round-product', '');
			update_option ('wc-donation-on-round', 'no');
		}

		wp_delete_post( $prod_id, true); // Set to False if you want to send them to Trash.
	}

	/**
	 * Saving campaign postmeta
	 */
	public function wc_donation_save_campaigns_details ( $post_id, $post_after, $post_before ) {

		if ( 'trash' == $post_before->post_status  ) {
			return;
		} 

		if ( 'wc-donation' == $post_after->post_type && 'publish' == $post_after->post_status ) { 
			
			if ( !isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), '_wpnonce') ) {
				exit( 'Not Authorized' );
			}

			do_action ('wc_donation_before_save_campaign_meta', $post_id);

			if ( isset( $_POST['wc-donation-tablink'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-tablink'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-tablink', sanitize_text_field( $_POST['wc-donation-tablink'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-tablink', 'tab-1'  );
			}

			if ( isset( $_POST['wc-donation-disp-single-page'] ) && 'yes' == sanitize_text_field( $_POST['wc-donation-disp-single-page'] ) ) {
				update_post_meta ( $post_id, 'wc-donation-disp-single-page', 'yes'  );

				if ( isset( $_POST['wc-donation-disp-shop-page'] ) && 'yes' == sanitize_text_field( $_POST['wc-donation-disp-shop-page'] ) ) {
					update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'yes'  );
				} else {
					update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'no'  );
				}
			} else {
				update_post_meta ( $post_id, 'wc-donation-disp-single-page', 'no'  );
				update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'no'  );
			}

			if ( isset( $_POST['wc-donation-amount-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-amount-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-amount-display-option', sanitize_text_field( $_POST['wc-donation-amount-display-option'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-amount-display-option', 'both'  );
			}

			if ( isset( $_POST['pred-amount'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-amount'] ) ) ) ) { 
				update_post_meta ( $post_id, 'pred-amount', array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-amount'] ) )  );
			} else {
				update_post_meta ( $post_id, 'pred-amount', ''  );
			}

			if ( isset( $_POST['pred-label'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-label'] ) ) ) ) { 
				update_post_meta ( $post_id, 'pred-label', array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-label'] ) )  );
			} else {
				update_post_meta ( $post_id, 'pred-label', ''  );
			}
			
			if ( isset( $_POST['free-min-amount'] ) && ! empty( sanitize_text_field( $_POST['free-min-amount'] ) ) ) { 
				update_post_meta ( $post_id, 'free-min-amount', sanitize_text_field( $_POST['free-min-amount'] )  );
			} else {
				update_post_meta ( $post_id, 'free-min-amount', ''  );
			}

			if ( isset( $_POST['free-max-amount'] ) && ! empty( sanitize_text_field( $_POST['free-max-amount'] ) ) ) { 
				update_post_meta ( $post_id, 'free-max-amount', sanitize_text_field( $_POST['free-max-amount'] )  );
			} else {
				update_post_meta ( $post_id, 'free-max-amount', ''  );
			}

			if ( isset( $_POST['wc-donation-display-donation-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-display-donation-type'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-display-donation-type', sanitize_text_field( $_POST['wc-donation-display-donation-type'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-display-donation-type', 'select'  );
			}
			
			if ( isset( $_POST['wc-donation-currency-position'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-currency-position'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-currency-position', sanitize_text_field( $_POST['wc-donation-currency-position'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-currency-position', 'before'  );
			}
			
			if ( isset( $_POST['wc-donation-title'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-title'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-title', sanitize_text_field( $_POST['wc-donation-title'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-title', '' );
			}
			
			if ( isset( $_POST['wc-donation-button-text'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-text'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-text', sanitize_text_field( $_POST['wc-donation-button-text'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-button-text', 'Donate'  );
			}
			
			if ( isset( $_POST['wc-donation-button-text-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-text-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-text-color', sanitize_text_field( $_POST['wc-donation-button-text-color'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-button-text-color', 'FFFFFF'  );
			}
			
			if ( isset( $_POST['wc-donation-button-bg-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-bg-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-bg-color', sanitize_text_field( $_POST['wc-donation-button-bg-color'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-button-bg-color', '333333'  );
			}
			
			if ( isset( $_POST['wc-donation-recurring'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-recurring'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-recurring', sanitize_text_field( $_POST['wc-donation-recurring'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-recurring', 'disabled'  );
			}
			
			if ( isset( $_POST['_subscription_period_interval'] ) && ! empty( sanitize_text_field( $_POST['_subscription_period_interval'] ) ) ) { 
				update_post_meta ( $post_id, '_subscription_period_interval', sanitize_text_field( $_POST['_subscription_period_interval'] )  );
			} else {
				update_post_meta ( $post_id, '_subscription_period_interval', ''  );
			}

			if ( isset( $_POST['_subscription_period'] ) && ! empty( sanitize_text_field( $_POST['_subscription_period'] ) ) ) { 
				update_post_meta ( $post_id, '_subscription_period', sanitize_text_field( $_POST['_subscription_period'] )  );
			} else {
				update_post_meta ( $post_id, '_subscription_period', ''  );
			}

			if ( isset( $_POST['_subscription_length'] ) ) { 
				update_post_meta ( $post_id, '_subscription_length', sanitize_text_field( $_POST['_subscription_length'] )  );
			} else {
				update_post_meta ( $post_id, '_subscription_length', ''  );
			}
			if ( isset( $_POST['wc-donation-recurring'] ) && 'user' === sanitize_text_field( $_POST['wc-donation-recurring'] ) ) {
				if ( isset( $_POST['wc-donation-recurring-txt'] ) ) {
					update_post_meta ( $post_id, 'wc-donation-recurring-txt', sanitize_text_field( $_POST['wc-donation-recurring-txt'] ) );
				}
				update_post_meta ( $post_id, '_subscription_length', '1' );
				update_post_meta ( $post_id, '_subscription_period', 'day' );
				update_post_meta ( $post_id, '_subscription_period_interval', '1' );
			}
			if ( isset( $_POST['wc-donation-recurring'] ) && 'disabled' !== sanitize_text_field( $_POST['wc-donation-recurring'] ) ) {
				
				$interval = !empty( get_post_meta ( $post_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $post_id, '_subscription_period_interval', true  ) : '1';
				$period = !empty( get_post_meta ( $post_id, '_subscription_period', true  ) ) ? get_post_meta ( $post_id, '_subscription_period', true  ) : 'day';
				$length = !empty( get_post_meta ( $post_id, '_subscription_length', true  ) ) ? get_post_meta ( $post_id, '_subscription_length', true  ) : '1';
				$prod_id = get_post_meta( $post_id, 'wc_donation_product', true );
				
				// $subscription_scheme[0] = array(
				//     'subscription_period_interval' => $interval,
				//     'subscription_period' => $period,
				//     'subscription_length' => $length,
				//     'subscription_pricing_method' => 'inherit',
				//     'subscription_regular_price' => '',
				//     'subscription_sale_price' => '',
				//     'subscription_discount' => '',
				//     'position' => 0,
				//     'subscription_price' => 0,
				//     'subscription_payment_sync_date' => 0
				// );

				// echo '<pre>';
				// print_r($subscription_scheme);
				// echo '</pre>';
				// wp_die('ruk!!');
				
				// update_post_meta ( $prod_id, '_subscription_one_time_shipping', 'no' );
				// update_post_meta ( $prod_id, '_wcsatt_default_status', 'subscription' );
				// update_post_meta ( $prod_id, '_wcsatt_force_subscription', 'no' );
				// update_post_meta ( $prod_id, '_wcsatt_schemes', $subscription_scheme );
			}

			//donation goal settings
			if ( isset( $_POST['wc-donation-goal-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-option', sanitize_text_field( $_POST['wc-donation-goal-display-option'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-display-option', 'disabled'  );
			}

			if ( isset( $_POST['wc-donation-goal-display-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-type'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-type', sanitize_text_field( $_POST['wc-donation-goal-display-type'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-display-type', 'fixed_amount'  );
			}

			if ( isset( $_POST['wc-donation-goal-fixed-amount-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-fixed-amount-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-fixed-amount-field', sanitize_text_field( $_POST['wc-donation-goal-fixed-amount-field'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-fixed-amount-field', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-no-of-donation-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-no-of-donation-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-no-of-donation-field', sanitize_text_field( $_POST['wc-donation-goal-no-of-donation-field'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-no-of-donation-field', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-no-of-days-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-no-of-days-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-no-of-days-field', sanitize_text_field( $_POST['wc-donation-goal-no-of-days-field'] )  );
				$end_date = gmdate('Y-m-d', strtotime(sanitize_text_field( $_POST['wc-donation-goal-no-of-days-field'] )));
				$current_date = gmdate('Y-m-d');
				$date1 = new DateTime($current_date);  //current date or any date
				$date2 = new DateTime($end_date);   //Future date
				$totalDays = $date2->diff($date1)->format('%a');  //find difference
				update_post_meta ( $post_id, 'wc-donation-goal-total-days', $totalDays );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-no-of-days-field', ''  );
				update_post_meta ( $post_id, 'wc-donation-goal-total-days', 0 );
			}

			if ( isset( $_POST['wc-donation-goal-progress-bar-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-progress-bar-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-progress-bar-color', sanitize_text_field( $_POST['wc-donation-goal-progress-bar-color'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-progress-bar-color', '333333'  );
			}

			if ( isset( $_POST['wc-donation-goal-display-donor-count'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-donor-count'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-donor-count', sanitize_text_field( $_POST['wc-donation-goal-display-donor-count'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-display-donor-count', 'disabled'  );
			}

			if ( isset( $_POST['wc-donation-goal-close-form'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-close-form'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-close-form', sanitize_text_field( $_POST['wc-donation-goal-close-form'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-close-form', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-close-form-text'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-close-form-text'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-close-form-text', sanitize_text_field( $_POST['wc-donation-goal-close-form-text'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-goal-close-form-text', ''  );
			}

			//donation cause settings
			if ( isset( $_POST['wc-donation-cause-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-cause-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-cause-display-option', sanitize_text_field( $_POST['wc-donation-cause-display-option'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-cause-display-option', 'hide'  );
			}

			if ( isset( $_POST['donation-cause-name'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-name'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-names', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-name'] ) )  );
			} else {
				update_post_meta ( $post_id, 'donation-cause-names', ''  );
			}
			if ( isset( $_POST['donation-cause-desc'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-desc'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-desc', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-desc'] ) )  );
			} else {
				update_post_meta ( $post_id, 'donation-cause-desc', ''  );
			}
			if ( isset( $_POST['donation-cause-img'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-img'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-img', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-img'] ) )  );
			} else {
				update_post_meta ( $post_id, 'donation-cause-img', ''  );
			}
			do_action ('wc_donation_after_save_campaign_meta', $post_id);
		
		}

	}

	/**
	 * Adding setting for shop page / single page chexkbox
	 */
	public function wc_donation_meta () {
		
		add_meta_box ( 
			'wc_donation_meta__1', 
			'Display Donation Product',  
			array( $this, 'render_wc_donation_meta__1_html'), 
			'wc-donation', 
			'side', 
			'high'
		);
		
		add_meta_box ( 
			'wc_donation_meta__2', 
			'Campaign Shortcode',  
			array( $this, 'render_wc_donation_meta__2_html'), 
			'wc-donation', 
			'side', 
			'high'
		);
		
		add_meta_box ( 
			'wc_donation_meta__3', 
			'Campaign',  
			array( $this, 'render_wc_donation_meta__3_html'), 
			'wc-donation', 
			'advanced', 
			'high'
		);
	}

	/**
	 * Rendering HTMl for meta 1
	 */
	public function render_wc_donation_meta__1_html () {
		require_once WC_DONATION_PATH . '/includes/views/admin/display_donatoion_product.php';
	}

	/**
	 * Rendering HTMl for meta 2
	 */
	public function render_wc_donation_meta__2_html () {
		require_once WC_DONATION_PATH . '/includes/views/admin/campaign_shortcode.php';
	}
	
	/**
	 * Rendering HTMl for meta 3
	 */
	public function render_wc_donation_meta__3_html () {
		require_once WC_DONATION_PATH . '/includes/views/admin/single_campaign.php';
	}

	public static function get_product_by_campaign ( $campaign_id = '' ) {

		$campaign_id = absint($campaign_id);
		
		if ( empty ( $campaign_id ) || 0 == $campaign_id ) {
			return;
		}

		$prod_id = get_post_meta( $campaign_id, 'wc_donation_product', true );
		$product = wc_get_product( $prod_id );

		if ( $product instanceof WC_Product ) {

			$product_name = $product->get_name();
			$product_type = $product->get_type();
			$product_slug = $product->get_slug();
			$product_permalink = get_permalink( $prod_id );

			$campaign_name = get_the_title( $campaign_id );
			$campaign_slug = get_post_field( 'post_name', $campaign_id );
			$amountDisp = !empty( get_post_meta ( $campaign_id, 'wc-donation-amount-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-amount-display-option', true  ) : 'both'; 
			$freeMinAmount = !empty( get_post_meta ( $campaign_id, 'free-min-amount', true  ) ) ? get_post_meta ( $campaign_id, 'free-min-amount', true  ) : ''; 
			$freeMaxAmount = !empty( get_post_meta ( $campaign_id, 'free-max-amount', true  ) ) ? get_post_meta ( $campaign_id, 'free-max-amount', true  ) : ''; 
			$predAmount = !empty( get_post_meta ( $campaign_id, 'pred-amount', false  ) ) ? get_post_meta ( $campaign_id, 'pred-amount', false  ) : array();
			$predLabel = !empty( get_post_meta ( $campaign_id, 'pred-label', false  ) ) ? get_post_meta ( $campaign_id, 'pred-label', false  ) : array();
			$DonationDispType = !empty( get_post_meta ( $campaign_id, 'wc-donation-display-donation-type', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-display-donation-type', true  ) : 'select'; 
			$currencyPos = !empty( get_post_meta ( $campaign_id, 'wc-donation-currency-position', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-currency-position', true  ) : 'before'; 
			$donationTitle = !empty( get_post_meta ( $campaign_id, 'wc-donation-title', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-title', true  ) : ''; 
			$donationBtnTxt = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-text', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-text', true  ) : 'Donate'; 
			$donationBtnTxtColor = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-text-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-text-color', true  ) : 'FFFFFF'; 
			$donationBtnBgColor = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-bg-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-bg-color', true  ) : '333333'; 
			$RecurringDisp = !empty( get_post_meta ( $campaign_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-recurring', true  ) : 'disabled';
			$recurring_text = !empty( get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) : '';
			$interval = !empty( get_post_meta ( $campaign_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_period_interval', true  ) : '1';
			$period = !empty( get_post_meta ( $campaign_id, '_subscription_period', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_period', true  ) : 'day';
			$length = !empty( get_post_meta ( $campaign_id, '_subscription_length', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_length', true  ) : '1';
			$dispSinglePage = !empty( get_post_meta ( $campaign_id, 'wc-donation-disp-single-page', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-disp-single-page', true ) : 'no';
			$dispShopPage = !empty( get_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', true ) : 'no'; 

			//donation goal
			$goalDisp = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-option', true  ) : 'disabled'; 
			$goalType = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-type', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-type', true  ) : 'fixed_amount';
			$fixedAmount = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) : ''; 
			$no_of_donation = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) : ''; 
			$no_of_days = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-days-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-days-field', true  ) : ''; 
			$total_days = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-total-days', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-total-days', true  ) : 0;
			$progressBarColor = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-progress-bar-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-progress-bar-color', true  ) : '333333'; 
			$dispDonorCount = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-donor-count', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-donor-count', true  ) : 'disabled'; 
			$closeForm = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-close-form', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-close-form', true  ) : ''; 
			$message = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-close-form-text', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-close-form-text', true  ) : ''; 
			//donation cause
			$causeDisp = !empty( get_post_meta ( $campaign_id, 'wc-donation-cause-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-cause-display-option', true  ) : 'hide';
			$causeNames = !empty( get_post_meta ( $campaign_id, 'donation-cause-names', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-names', false  ) : array();
			$causeDesc = !empty( get_post_meta ( $campaign_id, 'donation-cause-desc', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-desc', false  ) : array();
			$causeImg = !empty( get_post_meta ( $campaign_id, 'donation-cause-img', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-img', false  ) : array();
			$arr = apply_filters ( 'wc_donation_get_campaign', array (
				'campaign' => array (
					'campaign_id' => $campaign_id,
					'campaign_slug' => $campaign_slug,
					'campaign_name' => $campaign_name,
					'amount_display' => $amountDisp,
					'freeMinAmount' => $freeMinAmount,
					'freeMaxAmount' => $freeMaxAmount,
					'predAmount' => $predAmount,
					'predLabel' => $predLabel,
					'DonationDispType' => $DonationDispType,
					'currencyPos' => $currencyPos,
					'donationTitle' => $donationTitle,
					'donationBtnTxt' => $donationBtnTxt,
					'donationBtnTxtColor' => $donationBtnTxtColor,
					'donationBtnBgColor' => $donationBtnBgColor,
					'RecurringDisp' => $RecurringDisp,
					'recurringText' => $recurring_text,
					'causeDisp' => $causeDisp,
					'causeDesc' => $causeDesc,
					'causeImg' => $causeImg,
					'causeNames' => $causeNames,
					'interval' => $interval,
					'period' => $period,
					'length' => $length,
				),
				'goal' => array(
					'display' => $goalDisp,
					'type' => $goalType,
					'fixed_amount' => $fixedAmount,
					'no_of_donation' => $no_of_donation,
					'no_of_days' => $no_of_days,
					'total_days' => $total_days,
					'progress_bar_color' => $progressBarColor,
					'display_donor_count' => $dispDonorCount,
					'form_close' => $closeForm,
					'message' => $message,
				),
				'product' => array (
					'product_id' => $prod_id,
					'product_name' => $product_name,
					'product_type' => $product_type,
					'product_slug' => $product_slug,
					'product_permalink' => $product_permalink,
					'is_single' => $dispSinglePage,
					'is_shop' => $dispShopPage,
				)
				
				), $campaign_id );

			$object = (object) $arr;

			return $object;
		}
	}
}

new WcdonationCampaignSetting();
