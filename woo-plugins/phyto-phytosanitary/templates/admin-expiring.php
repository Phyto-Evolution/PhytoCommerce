<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Expiring Documents', 'phyto-phytosanitary' ); ?></h1>

    <form method="get" style="margin-bottom:16px;">
        <input type="hidden" name="page" value="phyto-phytosanitary-expiring">
        <?php esc_html_e( 'Show documents expiring within', 'phyto-phytosanitary' ); ?>
        <input type="number" name="days" value="<?php echo esc_attr( $days ); ?>" min="1" max="365" style="width:60px;">
        <?php esc_html_e( 'days', 'phyto-phytosanitary' ); ?>
        <input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'phyto-phytosanitary' ); ?>">
    </form>

    <?php if ( empty( $docs ) ) : ?>
        <p><?php printf( esc_html__( 'No documents expiring within %d days.', 'phyto-phytosanitary' ), $days ); ?></p>
    <?php else : ?>
        <div class="notice notice-warning"><p>
            <?php printf(
                esc_html( _n( '%d document expiring within %d days.', '%d documents expiring within %d days.', count( $docs ), 'phyto-phytosanitary' ) ),
                count( $docs ), $days
            ); ?>
        </p></div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Type', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Expiry Date', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Days Left', 'phyto-phytosanitary' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'phyto-phytosanitary' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $docs as $doc ) :
                    $days_left = (int) ceil( ( strtotime( $doc->expiry_date ) - time() ) / 86400 );
                ?>
                <tr>
                    <td><a href="<?php echo esc_url( get_edit_post_link( $doc->product_id ) ); ?>"><?php echo esc_html( $doc->product_name ?: '#' . $doc->product_id ); ?></a></td>
                    <td><?php echo esc_html( Phyto_PS_Admin::doc_types()[ $doc->doc_type ] ?? $doc->doc_type ); ?></td>
                    <td><?php echo esc_html( $doc->doc_title ?: $doc->reference_number ?: '—' ); ?></td>
                    <td><?php echo esc_html( $doc->expiry_date ); ?></td>
                    <td>
                        <strong class="phyto-ps-expiry--<?php echo $days_left <= 7 ? 'expired' : 'expiring'; ?>">
                            <?php echo esc_html( $days_left ); ?>
                        </strong>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-phytosanitary-new&id=' . $doc->id_doc ) ); ?>"><?php esc_html_e( 'Edit', 'phyto-phytosanitary' ); ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
