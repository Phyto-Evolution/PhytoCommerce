<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php echo $doc ? esc_html__( 'Edit Document', 'phyto-phytosanitary' ) : esc_html__( 'Add Document', 'phyto-phytosanitary' ); ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'phyto_ps_save' ); ?>
        <input type="hidden" name="action"  value="phyto_ps_save">
        <input type="hidden" name="id_doc"  value="<?php echo esc_attr( $doc->id_doc ?? 0 ); ?>">

        <table class="form-table">
            <tr>
                <th><label for="pps-product"><?php esc_html_e( 'Product', 'phyto-phytosanitary' ); ?></label></th>
                <td>
                    <input type="number" id="pps-product" name="product_id" value="<?php echo esc_attr( $doc->product_id ?? $product_id ); ?>" class="small-text" required>
                    <p class="description"><?php esc_html_e( 'Enter the WooCommerce Product ID.', 'phyto-phytosanitary' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="pps-type"><?php esc_html_e( 'Document Type', 'phyto-phytosanitary' ); ?></label></th>
                <td>
                    <select id="pps-type" name="doc_type">
                        <?php foreach ( Phyto_PS_Admin::doc_types() as $val => $label ) : ?>
                            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $doc->doc_type ?? 'phytosanitary', $val ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="pps-title"><?php esc_html_e( 'Document Title', 'phyto-phytosanitary' ); ?></label></th>
                <td><input type="text" id="pps-title" name="doc_title" value="<?php echo esc_attr( $doc->doc_title ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="pps-ref"><?php esc_html_e( 'Reference Number', 'phyto-phytosanitary' ); ?></label></th>
                <td><input type="text" id="pps-ref" name="reference_number" value="<?php echo esc_attr( $doc->reference_number ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="pps-authority"><?php esc_html_e( 'Issuing Authority', 'phyto-phytosanitary' ); ?></label></th>
                <td><input type="text" id="pps-authority" name="issuing_authority" value="<?php echo esc_attr( $doc->issuing_authority ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. NPPO India, USDA APHIS', 'phyto-phytosanitary' ); ?>"></td>
            </tr>
            <tr>
                <th><label for="pps-issue"><?php esc_html_e( 'Issue Date', 'phyto-phytosanitary' ); ?></label></th>
                <td><input type="date" id="pps-issue" name="issue_date" value="<?php echo esc_attr( $doc->issue_date ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th><label for="pps-expiry"><?php esc_html_e( 'Expiry Date', 'phyto-phytosanitary' ); ?></label></th>
                <td><input type="date" id="pps-expiry" name="expiry_date" value="<?php echo esc_attr( $doc->expiry_date ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th><label for="pps-attachment"><?php esc_html_e( 'Attachment (PDF/Image)', 'phyto-phytosanitary' ); ?></label></th>
                <td>
                    <input type="hidden" id="pps-attachment-id" name="attachment_id" value="<?php echo esc_attr( $doc->attachment_id ?? 0 ); ?>">
                    <button type="button" class="button" id="pps-upload-btn"><?php esc_html_e( 'Upload / Select File', 'phyto-phytosanitary' ); ?></button>
                    <span id="pps-attachment-label" style="margin-left:8px;">
                        <?php if ( ! empty( $doc->attachment_id ) && wp_get_attachment_url( $doc->attachment_id ) ) :
                            echo esc_html( basename( get_attached_file( $doc->attachment_id ) ) );
                        endif; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Visible to Customers', 'phyto-phytosanitary' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="public" value="1" <?php checked( isset( $doc ) ? $doc->public : true ); ?>>
                        <?php esc_html_e( 'Show this document on the product page', 'phyto-phytosanitary' ); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button( $doc ? __( 'Update Document', 'phyto-phytosanitary' ) : __( 'Add Document', 'phyto-phytosanitary' ) ); ?>
    </form>
</div>
