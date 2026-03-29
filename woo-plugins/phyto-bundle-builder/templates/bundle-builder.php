<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="phyto-bundle-builder" data-bundle="<?php echo esc_attr( $bundle->id_bundle ); ?>">
    <h3><?php echo esc_html( $bundle->name ); ?></h3>
    <?php if ( $bundle->description ) echo '<p>' . wp_kses_post( $bundle->description ) . '</p>'; ?>

    <?php if ( $bundle->discount_type === 'percent' ) : ?>
    <p class="phyto-bundle-discount-note"><?php printf( esc_html__( 'Save %s%% when you complete this bundle!', 'phyto-bundle' ), number_format( (float) $bundle->discount_value ) ); ?></p>
    <?php else : ?>
    <p class="phyto-bundle-discount-note"><?php printf( esc_html__( 'Save %s on this bundle!', 'phyto-bundle' ), wc_price( $bundle->discount_value ) ); ?></p>
    <?php endif; ?>

    <?php foreach ( $slots as $slot ) : ?>
    <div class="phyto-bundle-slot" data-slot="<?php echo esc_attr( $slot->id_slot ); ?>" data-category="<?php echo esc_attr( $slot->category_id ); ?>">
        <h4><?php echo esc_html( $slot->slot_name ); ?><?php if ( ! $slot->required ) echo ' <em>(' . esc_html__( 'optional', 'phyto-bundle' ) . ')</em>'; ?></h4>
        <div class="phyto-bundle-slot-products">
            <p class="phyto-bundle-loading"><?php esc_html_e( 'Loading products…', 'phyto-bundle' ); ?></p>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="phyto-bundle-total"></div>
    <button class="button alt phyto-bundle-add-btn"><?php esc_html_e( 'Add Bundle to Cart', 'phyto-bundle' ); ?></button>
    <div class="phyto-bundle-message"></div>
</div>
