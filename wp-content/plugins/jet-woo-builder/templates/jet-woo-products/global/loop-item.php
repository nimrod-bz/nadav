<?php
/**
 * Products loop item template
 */

global $product;

$product_id    = $product->get_id();
$classes       = [ 'jet-woo-builder-product' ];
$overlay_class = '';
$data_url      = '';

if ( $enable_thumb_effect ) {
	array_push( $classes, 'jet-woo-thumb-with-effect' );
}

if ( $carousel_enabled ) {
	array_push( $classes, 'swiper-slide' );
}

if ( $clickable_item ) {
	$overlay_class = 'jet-woo-item-overlay-wrap';
	$data_url      = 'data-url="' . esc_url( get_permalink() ) . '"';
}
?>

<div class="jet-woo-products__item <?php echo implode( ' ', $classes ); ?>" data-product-id="<?php echo $product_id ?>">
	<div class="jet-woo-products__inner-box <?php echo $overlay_class; ?>" <?php echo $data_url; ?> <?php echo $data_target_attr; ?> >
		<?php include $this->get_product_preset_template(); ?>
	</div>
	<?php if ( $clickable_item ) : ?>
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="jet-woo--item-overlay-link" <?php echo $target_attr; ?>></a>
	<?php endif; ?>
</div>