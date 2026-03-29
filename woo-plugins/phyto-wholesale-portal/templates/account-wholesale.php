<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2><?php esc_html_e( 'Wholesale Account', 'phyto-wholesale' ); ?></h2>
<?php if ( $app && $app->status === 'Approved' ) : ?>
    <p class="woocommerce-message"><?php esc_html_e( 'Your wholesale account is active. Wholesale pricing is applied automatically at checkout.', 'phyto-wholesale' ); ?></p>
<?php elseif ( $app && $app->status === 'Pending' ) : ?>
    <p><?php esc_html_e( 'Your application is under review. We will notify you within 2 business days.', 'phyto-wholesale' ); ?></p>
<?php elseif ( $app && $app->status === 'Rejected' ) : ?>
    <p class="woocommerce-error"><?php esc_html_e( 'Your application was not approved.', 'phyto-wholesale' ); ?></p>
    <?php if ( ! empty( $app->admin_notes ) ) : ?><p><?php echo esc_html( $app->admin_notes ); ?></p><?php endif; ?>
<?php else : ?>
<form id="phyto-ws-apply-form">
    <p><label><?php esc_html_e( 'Business Name', 'phyto-wholesale' ); ?><br>
    <input type="text" name="business_name" class="input-text" required></label></p>
    <p><label><?php esc_html_e( 'GST Number', 'phyto-wholesale' ); ?><br>
    <input type="text" name="gst_number" class="input-text"></label></p>
    <p><label><?php esc_html_e( 'Phone', 'phyto-wholesale' ); ?><br>
    <input type="text" name="phone" class="input-text"></label></p>
    <p><label><?php esc_html_e( 'Website', 'phyto-wholesale' ); ?><br>
    <input type="url" name="website" class="input-text"></label></p>
    <p><label><?php esc_html_e( 'Business Address', 'phyto-wholesale' ); ?><br>
    <textarea name="address" class="input-text" rows="3"></textarea></label></p>
    <p><label><?php esc_html_e( 'Message', 'phyto-wholesale' ); ?><br>
    <textarea name="message" class="input-text" rows="3"></textarea></label></p>
    <button type="submit" class="button alt"><?php esc_html_e( 'Submit Wholesale Application', 'phyto-wholesale' ); ?></button>
    <div id="phyto-ws-message"></div>
</form>
<?php endif; ?>
