<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="phyto_wholesale_product_data" class="panel woocommerce_options_panel">
    <div class="options_group">
        <p class="form-field">
            <label><?php esc_html_e( 'Min. Order Qty (MOQ)', 'phyto-wholesale' ); ?></label>
            <input type="number" name="phyto_ws_moq" value="<?php echo esc_attr( $config->moq ?? 0 ); ?>" min="0" class="short">
        </p>
        <p class="form-field">
            <label>
                <input type="checkbox" name="phyto_ws_wholesale_only" value="1" <?php checked( $config->wholesale_only ?? false ); ?>>
                <?php esc_html_e( 'Wholesale-only product (hide from public)', 'phyto-wholesale' ); ?>
            </label>
        </p>
    </div>
    <div class="options_group">
        <h4 style="padding: 0 12px"><?php esc_html_e( 'Tiered Pricing', 'phyto-wholesale' ); ?></h4>
        <table id="phyto-ws-tiers" style="width:100%;padding:0 12px">
            <thead><tr><th><?php esc_html_e( 'Min Qty', 'phyto-wholesale' ); ?></th><th><?php esc_html_e( 'Price (₹)', 'phyto-wholesale' ); ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ( $tiers as $i => $t ) : ?>
            <tr>
                <td><input type="number" name="phyto_ws_tiers[<?php echo $i; ?>][min_qty]" value="<?php echo esc_attr( $t['min_qty'] ); ?>" class="short"></td>
                <td><input type="number" name="phyto_ws_tiers[<?php echo $i; ?>][price]"   value="<?php echo esc_attr( $t['price'] ); ?>"   class="short" step="0.01"></td>
                <td><button type="button" class="button phyto-ws-remove-tier">✕</button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><button type="button" id="phyto-ws-add-tier" class="button"><?php esc_html_e( '+ Add tier', 'phyto-wholesale' ); ?></button></p>
    </div>
</div>
<script>
var tierIdx = <?php echo count( $tiers ); ?>;
document.getElementById('phyto-ws-add-tier').addEventListener('click', function(){
    var tbody = document.querySelector('#phyto-ws-tiers tbody');
    var row = document.createElement('tr');
    row.innerHTML = '<td><input type="number" name="phyto_ws_tiers['+tierIdx+'][min_qty]" class="short"></td><td><input type="number" name="phyto_ws_tiers['+tierIdx+'][price]" class="short" step="0.01"></td><td><button type="button" class="button phyto-ws-remove-tier">✕</button></td>';
    tbody.appendChild(row); tierIdx++;
});
document.querySelector('#phyto-ws-tiers').addEventListener('click', function(e){
    if(e.target.classList.contains('phyto-ws-remove-tier')) e.target.closest('tr').remove();
});
</script>
