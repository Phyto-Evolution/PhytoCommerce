<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2><?php esc_html_e( 'Loyalty Points', 'phyto-loyalty' ); ?></h2>
<?php if ( $account ) : ?>
<table class="form-table">
    <tr><th><?php esc_html_e( 'Balance', 'phyto-loyalty' ); ?></th><td><?php echo number_format( (int) $account->points_balance ); ?></td></tr>
    <tr><th><?php esc_html_e( 'Tier', 'phyto-loyalty' ); ?></th><td><?php echo esc_html( ucfirst( $account->tier ) ); ?></td></tr>
    <tr><th><?php esc_html_e( 'Lifetime earned', 'phyto-loyalty' ); ?></th><td><?php echo number_format( (int) $account->points_lifetime ); ?></td></tr>
</table>
<?php endif; ?>
<h3><?php esc_html_e( 'Manual Adjustment', 'phyto-loyalty' ); ?></h3>
<table class="form-table">
    <tr><th><?php esc_html_e( 'Points (use negative to deduct)', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_adjust_points" value="0" class="small-text"></td></tr>
    <tr><th><?php esc_html_e( 'Reason / Note', 'phyto-loyalty' ); ?></th>
        <td><input type="text" name="phyto_loyalty_adjust_note" class="regular-text"></td></tr>
    <tr><th></th>
        <td><label><input type="checkbox" name="phyto_loyalty_adjust" value="1"> <?php esc_html_e( 'Apply adjustment on save', 'phyto-loyalty' ); ?></label></td></tr>
</table>
