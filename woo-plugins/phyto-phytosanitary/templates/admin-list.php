<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Compliance Documents', 'phyto-phytosanitary' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-phytosanitary-new' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add Document', 'phyto-phytosanitary' ); ?>
    </a>

    <?php if ( isset( $_GET['saved'] ) )   : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Document saved.', 'phyto-phytosanitary' ); ?></p></div><?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Document deleted.', 'phyto-phytosanitary' ); ?></p></div><?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="phyto-phytosanitary">
        <select name="doc_type" onchange="this.form.submit()">
            <option value=""><?php esc_html_e( 'All Types', 'phyto-phytosanitary' ); ?></option>
            <?php foreach ( Phyto_PS_Admin::doc_types() as $val => $label ) : ?>
                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $_GET['doc_type'] ?? '', $val ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <table class="wp-list-table widefat fixed striped" style="margin-top:12px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Product', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Type', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Title / Reference', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Authority', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Expiry', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Public', 'phyto-phytosanitary' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'phyto-phytosanitary' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $docs ) ) : ?>
            <tr><td colspan="7"><?php esc_html_e( 'No documents found.', 'phyto-phytosanitary' ); ?></td></tr>
            <?php else : ?>
            <?php foreach ( $docs as $doc ) :
                $status = Phyto_PS_Frontend::expiry_status( $doc->expiry_date );
            ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url( get_edit_post_link( $doc->product_id ) ); ?>">
                        <?php echo esc_html( $doc->product_name ?: '#' . $doc->product_id ); ?>
                    </a>
                </td>
                <td><?php echo esc_html( Phyto_PS_Admin::doc_types()[ $doc->doc_type ] ?? $doc->doc_type ); ?></td>
                <td>
                    <?php echo esc_html( $doc->doc_title ?: '—' ); ?>
                    <?php if ( $doc->reference_number ) echo '<br><small>' . esc_html( $doc->reference_number ) . '</small>'; ?>
                </td>
                <td><?php echo esc_html( $doc->issuing_authority ?: '—' ); ?></td>
                <td class="phyto-ps-expiry--<?php echo esc_attr( $status ); ?>">
                    <?php if ( $doc->expiry_date ) : ?>
                        <?php echo esc_html( $doc->expiry_date ); ?>
                        <?php if ( $status === 'expired' ) echo ' <strong>(' . esc_html__( 'Expired', 'phyto-phytosanitary' ) . ')</strong>'; ?>
                        <?php if ( $status === 'expiring' ) echo ' <strong>(' . esc_html__( 'Expiring soon', 'phyto-phytosanitary' ) . ')</strong>'; ?>
                    <?php else : ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?php echo $doc->public ? '&#10003;' : '—'; ?></td>
                <td>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-phytosanitary-new&id=' . $doc->id_doc ) ); ?>"><?php esc_html_e( 'Edit', 'phyto-phytosanitary' ); ?></a>
                    |
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=phyto_ps_delete&id=' . $doc->id_doc ), 'phyto_ps_delete' ) ); ?>"
                       onclick="return confirm('<?php esc_attr_e( 'Delete this document?', 'phyto-phytosanitary' ); ?>')"
                       style="color:#dc3232;"><?php esc_html_e( 'Delete', 'phyto-phytosanitary' ); ?></a>
                    <?php if ( $doc->attachment_id && wp_get_attachment_url( $doc->attachment_id ) ) : ?>
                        | <a href="<?php echo esc_url( wp_get_attachment_url( $doc->attachment_id ) ); ?>" target="_blank"><?php esc_html_e( 'Download', 'phyto-phytosanitary' ); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
