<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'Phyto Loyalty — Overview', 'phyto-loyalty' ); ?></h1>
<div style="display:flex;gap:1.5em;margin:1em 0;flex-wrap:wrap">
    <?php foreach ( [
        [ __( 'Members',           'phyto-loyalty' ), number_format( (int) $stats->members ) ],
        [ __( 'Points Outstanding','phyto-loyalty' ), number_format( (int) $stats->total_outstanding ) ],
        [ __( 'Points Earned',     'phyto-loyalty' ), number_format( (int) $stats->total_earned ) ],
        [ __( 'Points Redeemed',   'phyto-loyalty' ), number_format( (int) $stats->total_redeemed ) ],
    ] as [$label, $value] ) : ?>
    <div style="background:#fff;border:1px solid #ccd;border-radius:6px;padding:1.2em 1.8em;min-width:140px;text-align:center">
        <div style="font-size:1.8em;font-weight:700;color:#2a7a2a"><?php echo esc_html( $value ); ?></div>
        <div style="color:#666;font-size:.85em;margin-top:.25em"><?php echo esc_html( $label ); ?></div>
    </div>
    <?php endforeach; ?>
</div>
<p><a href="?page=phyto-loyalty-customers" class="button"><?php esc_html_e( 'View all customers →', 'phyto-loyalty' ); ?></a>
   <a href="?page=phyto-loyalty-settings"  class="button"><?php esc_html_e( 'Settings →', 'phyto-loyalty' ); ?></a></p>
</div>
