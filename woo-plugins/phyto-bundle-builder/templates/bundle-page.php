<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="woocommerce-MyAccount-content phyto-bundles-page">

    <h2><?php esc_html_e( 'Build a Bundle', 'phyto-bundle' ); ?></h2>

    <?php if ( empty( $bundles ) ) : ?>
        <p><?php esc_html_e( 'No bundles are available right now. Check back soon!', 'phyto-bundle' ); ?></p>
    <?php else : ?>

        <?php if ( ! $bundle ) : ?>
            <p><?php esc_html_e( 'Choose a bundle below to get started:', 'phyto-bundle' ); ?></p>
            <ul class="phyto-bundle-list">
                <?php foreach ( $bundles as $b ) : ?>
                <li>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'bundles' ) . '?bundle=' . $b->id_bundle ); ?>">
                        <strong><?php echo esc_html( $b->name ); ?></strong>
                    </a>
                    <?php if ( $b->description ) : ?>
                        <p><?php echo wp_kses_post( $b->description ); ?></p>
                    <?php endif; ?>
                    <?php if ( $b->discount_type === 'percent' ) : ?>
                        <span class="phyto-bundle-badge"><?php printf( esc_html__( 'Save %s%%', 'phyto-bundle' ), number_format( (float) $b->discount_value ) ); ?></span>
                    <?php else : ?>
                        <span class="phyto-bundle-badge"><?php printf( esc_html__( 'Save %s', 'phyto-bundle' ), wc_price( $b->discount_value ) ); ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

        <?php else : ?>

            <p><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'bundles' ) ); ?>">&larr; <?php esc_html_e( 'All Bundles', 'phyto-bundle' ); ?></a></p>
            <?php include PHYTO_BUNDLE_DIR . 'templates/bundle-builder.php'; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>
