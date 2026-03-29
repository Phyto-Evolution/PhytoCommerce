<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'KYC Verification', 'phyto-kyc' ); ?></h1>
<?php if ( ! empty( $_GET['updated'] ) ) echo '<div class="updated"><p>' . esc_html__( 'Updated.', 'phyto-kyc' ) . '</p></div>'; ?>

<nav class="nav-tab-wrapper">
    <a href="?page=phyto-kyc&tab=list"     class="nav-tab <?php echo ( $_GET['tab'] ?? 'list' ) === 'list'     ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Applications', 'phyto-kyc' ); ?></a>
    <a href="?page=phyto-kyc&tab=settings" class="nav-tab <?php echo ( $_GET['tab'] ?? '' )    === 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'phyto-kyc' ); ?></a>
</nav>

<?php if ( ( $_GET['tab'] ?? 'list' ) === 'settings' ) : ?>
<form method="post" action="options.php">
<?php settings_fields( 'phyto_kyc_settings_group' ); ?>
<table class="form-table">
    <tr><th><?php esc_html_e( 'Enable KYC', 'phyto-kyc' ); ?></th>
        <td><input type="checkbox" name="phyto_kyc_settings[enabled]" value="1" <?php checked( $settings['enabled'] ?? 1 ); ?>></td></tr>
    <tr><th><?php esc_html_e( 'Require Level 1 (PAN)', 'phyto-kyc' ); ?></th>
        <td><input type="checkbox" name="phyto_kyc_settings[require_l1]" value="1" <?php checked( $settings['require_l1'] ?? 1 ); ?>></td></tr>
    <tr><th><?php esc_html_e( 'Require Level 2 (GST)', 'phyto-kyc' ); ?></th>
        <td><input type="checkbox" name="phyto_kyc_settings[require_l2]" value="1" <?php checked( $settings['require_l2'] ?? 0 ); ?>></td></tr>
    <tr><th><?php esc_html_e( 'sandbox.co.in API Key', 'phyto-kyc' ); ?></th>
        <td><input type="password" name="phyto_kyc_settings[api_key]" value="<?php echo esc_attr( $settings['api_key'] ?? '' ); ?>" class="regular-text"></td></tr>
    <tr><th><?php esc_html_e( 'Mode', 'phyto-kyc' ); ?></th>
        <td><select name="phyto_kyc_settings[mode]">
            <option value="sandbox" <?php selected( $settings['mode'] ?? 'sandbox', 'sandbox' ); ?>>Sandbox</option>
            <option value="live"    <?php selected( $settings['mode'] ?? '', 'live' ); ?>>Live</option>
        </select></td></tr>
</table>
<?php submit_button(); ?>
</form>

<?php else : ?>
<table class="widefat striped">
<thead><tr>
    <th><?php esc_html_e( 'Email', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'L1 Status', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'PAN', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'L2 Status', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'GST', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'Updated', 'phyto-kyc' ); ?></th>
    <th><?php esc_html_e( 'Actions', 'phyto-kyc' ); ?></th>
</tr></thead>
<tbody>
<?php foreach ( $profiles as $p ) : ?>
<tr>
    <td><?php echo esc_html( $p->user_email ); ?></td>
    <td><strong><?php echo esc_html( $p->level1_status ); ?></strong></td>
    <td><?php echo esc_html( $p->pan_number ?: '—' ); ?></td>
    <td><strong><?php echo esc_html( $p->level2_status ); ?></strong></td>
    <td><?php echo esc_html( $p->gst_number ?: '—' ); ?></td>
    <td><?php echo esc_html( $p->date_upd ); ?></td>
    <td>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:4px;flex-wrap:wrap">
            <?php wp_nonce_field( 'phyto_kyc_review' ); ?>
            <input type="hidden" name="action"         value="phyto_kyc_review">
            <input type="hidden" name="id_kyc_profile" value="<?php echo (int) $p->id_kyc_profile; ?>">
            <select name="kyc_level_target"><option value="1">L1</option><option value="2">L2</option></select>
            <select name="review_status"><option value="Verified">Verified</option><option value="Rejected">Rejected</option></select>
            <input type="text" name="admin_notes" placeholder="Notes" style="width:120px">
            <button class="button button-small"><?php esc_html_e( 'Save', 'phyto-kyc' ); ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
