<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php echo $bundle ? esc_html__( 'Edit Bundle', 'phyto-bundle' ) : esc_html__( 'New Bundle', 'phyto-bundle' ); ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'phyto_bundle_save' ); ?>
        <input type="hidden" name="action"    value="phyto_bundle_save">
        <input type="hidden" name="id_bundle" value="<?php echo esc_attr( $bundle ? $bundle->id_bundle : 0 ); ?>">

        <table class="form-table">
            <tr>
                <th><label for="pbb-name"><?php esc_html_e( 'Bundle Name', 'phyto-bundle' ); ?></label></th>
                <td><input type="text" id="pbb-name" name="name" class="regular-text" required value="<?php echo esc_attr( $bundle->name ?? '' ); ?>"></td>
            </tr>
            <tr>
                <th><label for="pbb-desc"><?php esc_html_e( 'Description', 'phyto-bundle' ); ?></label></th>
                <td><textarea id="pbb-desc" name="description" rows="4" class="large-text"><?php echo esc_textarea( $bundle->description ?? '' ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="pbb-dtype"><?php esc_html_e( 'Discount Type', 'phyto-bundle' ); ?></label></th>
                <td>
                    <select id="pbb-dtype" name="discount_type">
                        <option value="percent" <?php selected( ( $bundle->discount_type ?? 'percent' ), 'percent' ); ?>><?php esc_html_e( 'Percentage (%)', 'phyto-bundle' ); ?></option>
                        <option value="amount"  <?php selected( ( $bundle->discount_type ?? '' ), 'amount' ); ?>><?php esc_html_e( 'Fixed Amount', 'phyto-bundle' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="pbb-dvalue"><?php esc_html_e( 'Discount Value', 'phyto-bundle' ); ?></label></th>
                <td><input type="number" id="pbb-dvalue" name="discount_value" step="0.01" min="0" class="small-text" value="<?php echo esc_attr( $bundle->discount_value ?? 0 ); ?>"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Active', 'phyto-bundle' ); ?></th>
                <td><label><input type="checkbox" name="active" value="1" <?php checked( isset( $bundle ) ? $bundle->active : true ); ?>> <?php esc_html_e( 'Enable this bundle on the front end', 'phyto-bundle' ); ?></label></td>
            </tr>
        </table>

        <hr>
        <h2><?php esc_html_e( 'Slots', 'phyto-bundle' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Each slot lets the customer pick one product from the selected category.', 'phyto-bundle' ); ?></p>

        <table class="widefat" id="phyto-slots-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Slot Name', 'phyto-bundle' ); ?></th>
                    <th><?php esc_html_e( 'Category', 'phyto-bundle' ); ?></th>
                    <th><?php esc_html_e( 'Required', 'phyto-bundle' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="phyto-slots-body">
                <?php if ( ! empty( $slots ) ) : ?>
                    <?php foreach ( $slots as $i => $slot ) : ?>
                    <tr class="phyto-slot-row">
                        <td><input type="text" name="slots[<?php echo $i; ?>][slot_name]" value="<?php echo esc_attr( $slot->slot_name ); ?>" class="regular-text" required></td>
                        <td>
                            <select name="slots[<?php echo $i; ?>][category_id]">
                                <option value=""><?php esc_html_e( '— Select category —', 'phyto-bundle' ); ?></option>
                                <?php foreach ( $cats as $cat ) : ?>
                                    <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( (int) $slot->category_id, $cat->term_id ); ?>>
                                        <?php echo esc_html( $cat->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="checkbox" name="slots[<?php echo $i; ?>][required]" value="1" <?php checked( $slot->required ); ?>></td>
                        <td><button type="button" class="button phyto-remove-slot"><?php esc_html_e( 'Remove', 'phyto-bundle' ); ?></button></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <p>
            <button type="button" class="button" id="phyto-add-slot"><?php esc_html_e( '+ Add Slot', 'phyto-bundle' ); ?></button>
        </p>

        <!-- Template row (hidden) for JS cloning -->
        <script type="text/template" id="phyto-slot-template">
            <tr class="phyto-slot-row">
                <td><input type="text" name="slots[__IDX__][slot_name]" value="" class="regular-text" required placeholder="<?php esc_attr_e( 'e.g. Main Plant', 'phyto-bundle' ); ?>"></td>
                <td>
                    <select name="slots[__IDX__][category_id]">
                        <option value=""><?php esc_html_e( '— Select category —', 'phyto-bundle' ); ?></option>
                        <?php foreach ( $cats as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="checkbox" name="slots[__IDX__][required]" value="1" checked></td>
                <td><button type="button" class="button phyto-remove-slot"><?php esc_html_e( 'Remove', 'phyto-bundle' ); ?></button></td>
            </tr>
        </script>

        <?php submit_button( $bundle ? __( 'Update Bundle', 'phyto-bundle' ) : __( 'Create Bundle', 'phyto-bundle' ) ); ?>
    </form>
</div>
