<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<h1><?php esc_html_e( 'TC Batch Tracker', 'phyto-tc-batch' ); ?></h1>
<nav class="nav-tab-wrapper">
    <a href="?page=phyto-tc-batch&tab=list"   class="nav-tab <?php echo $tab==='list'   ? 'nav-tab-active':''; ?>"><?php esc_html_e('Batches','phyto-tc-batch'); ?></a>
    <a href="?page=phyto-tc-batch&tab=create" class="nav-tab <?php echo $tab==='create' ? 'nav-tab-active':''; ?>"><?php esc_html_e('New Batch','phyto-tc-batch'); ?></a>
</nav>

<?php if ( $tab === 'create' ) : ?>
<h2><?php esc_html_e( 'New TC Batch', 'phyto-tc-batch' ); ?></h2>
<form id="phyto-tcb-create-form">
<table class="form-table">
    <tr><th><?php esc_html_e('Species Name','phyto-tc-batch'); ?></th>
        <td><input type="text" name="species_name" id="phyto-tcb-species" class="regular-text" required>
        <button type="button" id="phyto-tcb-suggest" class="button"><?php esc_html_e('Suggest Code','phyto-tc-batch'); ?></button></td></tr>
    <tr><th><?php esc_html_e('Batch Code','phyto-tc-batch'); ?></th>
        <td><input type="text" name="batch_code" id="phyto-tcb-code" class="regular-text" required></td></tr>
    <tr><th><?php esc_html_e('Generation','phyto-tc-batch'); ?></th>
        <td><select name="generation">
            <?php foreach ( Phyto_TCB_DB::GENERATIONS as $g ) echo '<option>' . esc_html($g) . '</option>'; ?>
        </select></td></tr>
    <tr><th><?php esc_html_e('Initiation Date','phyto-tc-batch'); ?></th>
        <td><input type="date" name="date_initiation" class="regular-text"></td></tr>
    <tr><th><?php esc_html_e('Units Produced','phyto-tc-batch'); ?></th>
        <td><input type="number" name="units_produced" min="0" class="small-text" value="0"></td></tr>
    <tr><th><?php esc_html_e('Sterility Protocol','phyto-tc-batch'); ?></th>
        <td><textarea name="sterility_protocol" class="large-text" rows="3"></textarea></td></tr>
    <tr><th><?php esc_html_e('Notes','phyto-tc-batch'); ?></th>
        <td><textarea name="notes" class="large-text" rows="3"></textarea></td></tr>
</table>
<button type="submit" class="button button-primary"><?php esc_html_e('Create Batch','phyto-tc-batch'); ?></button>
<div id="phyto-tcb-result"></div>
</form>

<?php else : ?>
<table class="widefat striped">
<thead><tr>
    <th><?php esc_html_e('Code','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Species','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Gen','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Status','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Produced','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Remaining','phyto-tc-batch'); ?></th>
    <th><?php esc_html_e('Initiated','phyto-tc-batch'); ?></th>
</tr></thead>
<tbody>
<?php foreach ( $batches as $b ) : ?>
<tr>
    <td><strong><?php echo esc_html( $b->batch_code ); ?></strong></td>
    <td><?php echo esc_html( $b->species_name ); ?></td>
    <td><?php echo esc_html( $b->generation ); ?></td>
    <td><?php echo esc_html( $b->batch_status ); ?></td>
    <td><?php echo (int) $b->units_produced; ?></td>
    <td><?php echo (int) $b->units_remaining; ?></td>
    <td><?php echo esc_html( $b->date_initiation ?: '—' ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
