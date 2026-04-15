<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'Loyalty Settings', 'phyto-loyalty' ); ?></h1>
<form method="post" action="options.php">
<?php settings_fields( 'phyto_loyalty_settings_group' ); ?>
<table class="form-table">
    <tr><th><?php esc_html_e( 'Earn Rate (₹ per 1 point)', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_settings[earn_rate]" value="<?php echo esc_attr( $settings['earn_rate'] ?? 1 ); ?>" step="0.01" min="0.01" class="small-text"></td></tr>
    <tr><th><?php esc_html_e( 'Redeem Rate (₹ per 1 point)', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_settings[redeem_rate]" value="<?php echo esc_attr( $settings['redeem_rate'] ?? 1 ); ?>" step="0.01" min="0.01" class="small-text"></td></tr>
    <tr><th><?php esc_html_e( 'Min points to redeem', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_settings[min_redeem]" value="<?php echo esc_attr( $settings['min_redeem'] ?? 100 ); ?>" min="1" class="small-text"></td></tr>
    <tr><th><?php esc_html_e( 'Max redeem % of cart', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_settings[max_redeem_pct]" value="<?php echo esc_attr( $settings['max_redeem_pct'] ?? 20 ); ?>" min="1" max="100" class="small-text"> %</td></tr>
    <tr><th><?php esc_html_e( 'Points expiry (days)', 'phyto-loyalty' ); ?></th>
        <td><input type="number" name="phyto_loyalty_settings[expiry_days]" value="<?php echo esc_attr( $settings['expiry_days'] ?? 365 ); ?>" min="30" class="small-text"></td></tr>
    <tr><th><?php esc_html_e( 'Programme enabled', 'phyto-loyalty' ); ?></th>
        <td><input type="checkbox" name="phyto_loyalty_settings[enabled]" value="1" <?php checked( $settings['enabled'] ?? 1 ); ?>></td></tr>
</table>
<h2><?php esc_html_e( 'Tiers', 'phyto-loyalty' ); ?></h2>
<p style="color:#666"><?php esc_html_e( 'Defined in code. Edit phyto_loyalty_settings[tiers] via wp-cli or DB to change tier thresholds and multipliers.', 'phyto-loyalty' ); ?></p>
<?php submit_button(); ?>
</form>
</div>
