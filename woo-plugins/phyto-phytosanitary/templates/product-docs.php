<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( empty( $docs ) ) return; ?>
<div class="phyto-ps-docs">
    <h3 class="phyto-ps-docs-title"><?php esc_html_e( 'Compliance Documents', 'phyto-phytosanitary' ); ?></h3>
    <ul class="phyto-ps-doc-list">
        <?php foreach ( $docs as $doc ) :
            $status = Phyto_PS_Frontend::expiry_status( $doc->expiry_date );
            $file_url = $doc->attachment_id ? wp_get_attachment_url( $doc->attachment_id ) : '';
        ?>
        <li class="phyto-ps-doc-item phyto-ps-doc--<?php echo esc_attr( $status ); ?>">
            <span class="phyto-ps-doc-type"><?php echo esc_html( Phyto_PS_Frontend::doc_type_label( $doc->doc_type ) ); ?></span>
            <span class="phyto-ps-doc-title"><?php echo esc_html( $doc->doc_title ?: $doc->reference_number ); ?></span>

            <?php if ( $doc->issuing_authority ) : ?>
                <span class="phyto-ps-doc-authority"><?php echo esc_html( $doc->issuing_authority ); ?></span>
            <?php endif; ?>

            <?php if ( $doc->expiry_date ) : ?>
                <span class="phyto-ps-doc-expiry phyto-ps-expiry--<?php echo esc_attr( $status ); ?>">
                    <?php if ( $status === 'expired' ) : ?>
                        <?php esc_html_e( 'Expired', 'phyto-phytosanitary' ); ?>
                    <?php elseif ( $status === 'expiring' ) : ?>
                        <?php printf( esc_html__( 'Expires %s', 'phyto-phytosanitary' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $doc->expiry_date ) ) ) ); ?>
                    <?php else : ?>
                        <?php printf( esc_html__( 'Valid to %s', 'phyto-phytosanitary' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $doc->expiry_date ) ) ) ); ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <?php if ( $file_url ) : ?>
                <a class="phyto-ps-doc-download" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( 'Download', 'phyto-phytosanitary' ); ?>
                </a>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
