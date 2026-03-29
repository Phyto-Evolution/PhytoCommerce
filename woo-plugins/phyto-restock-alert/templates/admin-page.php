<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'Phyto Restock Alerts', 'phyto-restock-alert' ); ?></h1>

<form method="post" action="options.php">
<?php settings_fields( 'phyto_restock_settings' ); ?>
<table class="form-table">
    <tr><th><?php esc_html_e( 'Sender Name', 'phyto-restock-alert' ); ?></th>
        <td><input name="phyto_restock_settings[from_name]" type="text" value="<?php echo esc_attr( $settings['from_name'] ?? '' ); ?>" class="regular-text"></td></tr>
    <tr><th><?php esc_html_e( 'Max emails per stock-in event', 'phyto-restock-alert' ); ?></th>
        <td><input name="phyto_restock_settings[max_per_run]" type="number" min="1" max="500" value="<?php echo esc_attr( $settings['max_per_run'] ?? 50 ); ?>"></td></tr>
    <tr><th><?php esc_html_e( 'Show form on OOS products', 'phyto-restock-alert' ); ?></th>
        <td><input name="phyto_restock_settings[show_form]" type="checkbox" value="1" <?php checked( $settings['show_form'] ?? 1 ); ?>></td></tr>
</table>
<?php submit_button(); ?>
</form>

<h2><?php esc_html_e( 'Subscribers', 'phyto-restock-alert' ); ?></h2>
<table class="widefat striped">
<thead><tr>
    <th><?php esc_html_e( 'Email', 'phyto-restock-alert' ); ?></th>
    <th><?php esc_html_e( 'Name', 'phyto-restock-alert' ); ?></th>
    <th><?php esc_html_e( 'Product', 'phyto-restock-alert' ); ?></th>
    <th><?php esc_html_e( 'Subscribed', 'phyto-restock-alert' ); ?></th>
    <th><?php esc_html_e( 'Notified', 'phyto-restock-alert' ); ?></th>
</tr></thead>
<tbody>
<?php foreach ( $rows as $row ) :
    $pname = get_the_title( $row->product_id );
?>
<tr>
    <td><?php echo esc_html( $row->email ); ?></td>
    <td><?php echo esc_html( $row->firstname ); ?></td>
    <td><a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-restock-alert&product_id=' . $row->product_id ) ); ?>"><?php echo esc_html( $pname ); ?></a></td>
    <td><?php echo esc_html( $row->date_add ); ?></td>
    <td><?php echo $row->notified ? esc_html( $row->date_notified ) : '—'; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
