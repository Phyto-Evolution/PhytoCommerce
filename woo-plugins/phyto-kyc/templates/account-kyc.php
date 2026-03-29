<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2><?php esc_html_e( 'Identity Verification', 'phyto-kyc' ); ?></h2>
<?php $require_l1 = ! empty( $settings['require_l1'] ); $require_l2 = ! empty( $settings['require_l2'] ); ?>
<?php if ( $require_l1 ) : ?>
<div class="phyto-kyc-section">
    <h3><?php esc_html_e( 'Level 1 — PAN Verification (Retail)', 'phyto-kyc' ); ?>
        <span class="phyto-kyc-status phyto-kyc-status--<?php echo esc_attr( strtolower( $profile->level1_status ) ); ?>"><?php echo esc_html( $profile->level1_status ); ?></span>
    </h3>
    <?php if ( $profile->level1_status !== 'Verified' ) : ?>
    <form id="phyto-kyc-pan-form">
        <label><?php esc_html_e( 'PAN Number', 'phyto-kyc' ); ?><br>
        <input type="text" name="pan" maxlength="10" placeholder="ABCDE1234F" class="input-text" style="text-transform:uppercase">
        </label>
        <button type="submit" class="button alt"><?php esc_html_e( 'Verify PAN', 'phyto-kyc' ); ?></button>
        <div id="phyto-kyc-pan-message"></div>
    </form>
    <?php else : ?>
    <p class="phyto-kyc-verified"><?php printf( esc_html__( '✓ Verified as: %s', 'phyto-kyc' ), esc_html( $profile->pan_name ) ); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ( $require_l2 ) : ?>
<div class="phyto-kyc-section">
    <h3><?php esc_html_e( 'Level 2 — GST Verification (B2B)', 'phyto-kyc' ); ?>
        <span class="phyto-kyc-status phyto-kyc-status--<?php echo esc_attr( strtolower( $profile->level2_status ) ); ?>"><?php echo esc_html( $profile->level2_status ); ?></span>
    </h3>
    <?php if ( $profile->level2_status !== 'Verified' ) : ?>
    <form id="phyto-kyc-gst-form">
        <label><?php esc_html_e( 'GSTIN', 'phyto-kyc' ); ?><br>
        <input type="text" name="gst" maxlength="15" placeholder="22AAAAA0000A1Z5" class="input-text" style="text-transform:uppercase">
        </label>
        <button type="submit" class="button alt"><?php esc_html_e( 'Verify GST', 'phyto-kyc' ); ?></button>
        <div id="phyto-kyc-gst-message"></div>
    </form>
    <?php else : ?>
    <p class="phyto-kyc-verified"><?php printf( esc_html__( '✓ Verified as: %s', 'phyto-kyc' ), esc_html( $profile->business_name ) ); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>
