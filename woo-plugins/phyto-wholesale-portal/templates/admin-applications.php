<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'Wholesale Applications', 'phyto-wholesale' ); ?></h1>
<?php if ( ! empty( $_GET['updated'] ) ) echo '<div class="updated"><p>' . esc_html__( 'Status updated.', 'phyto-wholesale' ) . '</p></div>'; ?>
<table class="widefat striped">
<thead><tr>
    <th>#</th><th><?php esc_html_e( 'Email', 'phyto-wholesale' ); ?></th>
    <th><?php esc_html_e( 'Business', 'phyto-wholesale' ); ?></th>
    <th><?php esc_html_e( 'GST', 'phyto-wholesale' ); ?></th>
    <th><?php esc_html_e( 'Status', 'phyto-wholesale' ); ?></th>
    <th><?php esc_html_e( 'Applied', 'phyto-wholesale' ); ?></th>
    <th><?php esc_html_e( 'Actions', 'phyto-wholesale' ); ?></th>
</tr></thead>
<tbody>
<?php foreach ( $rows as $row ) : ?>
<tr>
    <td><?php echo (int) $row->id_app; ?></td>
    <td><?php echo esc_html( $row->user_email ); ?></td>
    <td><?php echo esc_html( $row->business_name ); ?></td>
    <td><?php echo esc_html( $row->gst_number ); ?></td>
    <td><strong><?php echo esc_html( $row->status ); ?></strong></td>
    <td><?php echo esc_html( $row->date_add ); ?></td>
    <td>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
            <?php wp_nonce_field( 'phyto_ws_status' ); ?>
            <input type="hidden" name="action"  value="phyto_ws_update_status">
            <input type="hidden" name="id_app"  value="<?php echo (int) $row->id_app; ?>">
            <textarea name="admin_notes" placeholder="Notes" rows="1" style="width:160px"><?php echo esc_textarea( $row->admin_notes ); ?></textarea>
            <select name="status">
                <?php foreach ( [ 'Pending', 'Approved', 'Rejected' ] as $s ) : ?>
                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $row->status, $s ); ?>><?php echo esc_html( $s ); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small"><?php esc_html_e( 'Update', 'phyto-wholesale' ); ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
