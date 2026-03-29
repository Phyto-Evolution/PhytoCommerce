<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Bundles', 'phyto-bundle' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-bundles-new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'phyto-bundle' ); ?>
    </a>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Bundle saved.', 'phyto-bundle' ); ?></p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Bundle deleted.', 'phyto-bundle' ); ?></p></div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'phyto-bundle' ); ?></th>
                <th><?php esc_html_e( 'Name', 'phyto-bundle' ); ?></th>
                <th><?php esc_html_e( 'Discount', 'phyto-bundle' ); ?></th>
                <th><?php esc_html_e( 'Active', 'phyto-bundle' ); ?></th>
                <th><?php esc_html_e( 'Date Created', 'phyto-bundle' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'phyto-bundle' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $bundles ) ) : ?>
            <tr><td colspan="6"><?php esc_html_e( 'No bundles yet.', 'phyto-bundle' ); ?></td></tr>
            <?php else : ?>
            <?php foreach ( $bundles as $b ) : ?>
            <tr>
                <td><?php echo esc_html( $b->id_bundle ); ?></td>
                <td><strong><?php echo esc_html( $b->name ); ?></strong></td>
                <td>
                    <?php if ( $b->discount_type === 'percent' ) : ?>
                        <?php echo esc_html( number_format( (float) $b->discount_value ) . '%' ); ?>
                    <?php else : ?>
                        <?php echo wc_price( $b->discount_value ); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ( $b->active ) : ?>
                        <span class="dashicons dashicons-yes-alt" style="color:#46b450;" title="<?php esc_attr_e( 'Active', 'phyto-bundle' ); ?>"></span>
                    <?php else : ?>
                        <span class="dashicons dashicons-dismiss" style="color:#dc3232;" title="<?php esc_attr_e( 'Inactive', 'phyto-bundle' ); ?>"></span>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html( $b->date_add ?: '—' ); ?></td>
                <td>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-bundles-new&id=' . (int) $b->id_bundle ) ); ?>">
                        <?php esc_html_e( 'Edit', 'phyto-bundle' ); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
