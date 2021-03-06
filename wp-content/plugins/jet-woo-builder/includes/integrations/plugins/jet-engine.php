<?php
/**
 * JetEngine compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Engine_Package' ) ) {

	// Define class
	class Jet_Woo_Builder_Engine_Package {

		// Class constructor.
		public function __construct() {
			add_filter( 'jet-engine/listing/item-classes', [ $this, 'thumbnail_effect_class' ] );

			add_action( 'elementor/element/jet-woo-products/section_carousel/before_section_start', [ $this, 'register_custom_query_controls' ], 10, 2 );
			add_action( 'elementor/element/jet-woo-products-list/section_general/after_section_end', [ $this, 'register_custom_query_controls' ], 10, 2 );

			add_action( 'jet-woo-builder/shortcodes/jet-woo-products/custom-query/on-query', [ $this, 'maybe_set_query_props' ], 10, 3 );
			add_action( 'jet-woo-builder/shortcodes/jet-woo-products-list/custom-query/on-query', [ $this, 'maybe_set_query_props' ], 10, 3 );

			add_action( 'jet-engine/listings/frontend/setup-data', [ $this, 'maybe_set_listings_frontend_data' ] );
		}

		/**
		 * Setup data for Products Archive widgets compatibility with listing grid that use WC_Products_Query
		 *
		 * @param $post
		 */
		public function maybe_set_listings_frontend_data( $post ) {
			if ( $post && is_subclass_of( $post, 'WC_Product' ) ) {
				global $product;
				$product = $post;
			}
		}

		/**
		 * Push thumbnail effect class to listing grid item wrapper.
		 *
		 * @param $classes
		 *
		 * @return mixed
		 */
		public function thumbnail_effect_class( $classes ) {
			if ( filter_var( jet_woo_builder_settings()->get( 'enable_product_thumb_effect' ), FILTER_VALIDATE_BOOLEAN ) ) {
				$classes[] = 'jet-woo-thumb-with-effect';
			}

			return $classes;
		}

		/**
		 * Register custom query controls.
		 *
		 * @param $obj
		 */
		public function register_custom_query_controls( $obj ) {
			$obj->start_controls_section(
				'section_custom_query',
				[
					'label' => esc_html__( 'Custom Query', 'jet-woo-builder' ),
				]
			);

			$obj->add_control(
				'enable_custom_query',
				[
					'label'       => esc_html__( 'Enable Custom Query', 'jet-woo-builder' ),
					'type'        => \Elementor\Controls_Manager::SWITCHER,
					'description' => esc_html__( 'Allow to use custom query from Query Builder as items source.', 'jet-woo-builder' ),
				]
			);

			$obj->add_control(
				'custom_query_id',
				[
					'label'     => esc_html__( 'Custom Query', 'jet-engine' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'default'   => '',
					'options'   => \Jet_Engine\Query_Builder\Manager::instance()->get_queries_for_options(),
					'condition' => [
						'enable_custom_query' => 'yes',
					],
				]
			);

			$obj->end_controls_section();
		}

		/**
		 * Setup query data if it was filters request
		 *
		 * @param $query
		 * @param $settings
		 * @param $provider
		 */
		public function maybe_set_query_props( $query, $settings, $provider ) {
			$query_id = ! empty( $settings['_element_id'] ) ? $settings['_element_id'] : false;

			// Setup props for the pager
			jet_smart_filters()->query->set_props(
				$provider,
				[ 'max_num_pages' => $query->get_items_pages_count(), ],
				$query_id
			);

			// Store settings to localize it by SmartFilters later
			jet_smart_filters()->providers->store_provider_settings( $provider, $settings, $query_id );
		}

	}

}

new Jet_Woo_Builder_Engine_Package();
