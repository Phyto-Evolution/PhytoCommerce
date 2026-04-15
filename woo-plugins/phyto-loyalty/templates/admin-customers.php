<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'Loyalty Customers', 'phyto-loyalty' ); ?></h1>
<table class="widefat striped">
<thead><tr>
    <th><?php esc_html_e( 'Email', 'phyto-loyalty' ); ?></th>
    <th><?php esc_html_e( 'Tier', 'phyto-loyalty' ); ?></th>
    <th><?php esc_html_e( 'Balance', 'phyto-loyalty' ); ?></th>
    <th><?php esc_html_e( 'Lifetime', 'phyto-loyalty' ); ?></th>
    <th><?php esc_html_e( 'Redeemed', 'phyto-loyalty' ); ?></th>
    <th><?php esc_html_e( 'Since', 'phyto-loyalty' ); ?></th>
</tr></thead>
<tbody>
<?php foreach ( $rows as $r ) : ?>
<tr>
    <td><a href="<?php echo esc_url( get_edit_user_link( $r->user_id ) ); ?>"><?php echo esc_html( $r->user_email ); ?></a></td>
    <td><?php echo esc_html( ucfirst( $r->tier ) ); ?></td>
    <td><?php echo number_format( (int) $r->points_balance ); ?></td>
    <td><?php echo number_format( (int) $r->points_lifetime ); ?></td>
    <td><?php echo number_format( (int) $r->points_redeemed ); ?></td>
    <td><?php echo esc_html( substr( $r->date_add, 0, 10 ) ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
