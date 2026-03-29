<?php
/**
 * Template: Restock subscription form — shown on OOS product pages.
 * Override by copying to your-theme/woocommerce/restock-form.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
global $product;
?>
<div class="phyto-restock-wrap">
    <h4><?php esc_html_e( 'Notify me when available', 'phyto-restock-alert' ); ?></h4>
    <form id="phyto-restock-form" class="phyto-restock-form">
        <?php wp_nonce_field( 'phyto_restock', 'phyto_restock_nonce' ); ?>
        <input type="hidden" name="product_id"   value="<?php echo esc_attr( $product->get_id() ); ?>">
        <input type="hidden" name="variation_id" value="0">
        <input type="text"  name="firstname" placeholder="<?php esc_attr_e( 'Your name (optional)', 'phyto-restock-alert' ); ?>" class="input-text">
        <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'phyto-restock-alert' ); ?>" required class="input-text">
        <button type="submit" class="button alt"><?php esc_html_e( 'Notify Me', 'phyto-restock-alert' ); ?></button>
        <div class="phyto-restock-message" style="display:none;"></div>
    </form>
</div>
