<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="phyto-ps-meta-box">
    <?php if ( empty( $docs ) ) : ?>
        <p><?php esc_html_e( 'No documents attached to this product yet.', 'phyto-phytosanitary' ); ?></p>
    <?php else : ?>
        <table class="widefat" style="margin-bottom:12px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Type', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Expiry', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Public', 'phyto-phytosanitary' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $docs as $doc ) :
                    $status = Phyto_PS_Frontend::expiry_status( $doc->expiry_date );
                ?>
                <tr>
                    <td><?php echo esc_html( Phyto_PS_Admin::doc_types()[ $doc->doc_type ] ?? $doc->doc_type ); ?></td>
                    <td><?php echo esc_html( $doc->doc_title ?: $doc->reference_number ?: '—' ); ?></td>
                    <td class="phyto-ps-expiry--<?php echo esc_attr( $status ); ?>">
                        <?php echo esc_html( $doc->expiry_date ?: '—' ); ?>
                        <?php if ( $status === 'expired' )  echo ' <strong>(' . esc_html__( 'Expired', 'phyto-phytosanitary' ) . ')</strong>'; ?>
                        <?php if ( $status === 'expiring' ) echo ' <strong>(' . esc_html__( 'Soon', 'phyto-phytosanitary' ) . ')</strong>'; ?>
                    </td>
                    <td><?php echo $doc->public ? '&#10003;' : '—'; ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-phytosanitary-new&id=' . $doc->id_doc ) ); ?>">
                            <?php esc_html_e( 'Edit', 'phyto-phytosanitary' ); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-phytosanitary-new&product_id=' . $product_id ) ); ?>" class="button">
        <?php esc_html_e( 'Attach New Document', 'phyto-phytosanitary' ); ?>
    </a>
</div>
