<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap phyto-qa-wrap">
<h1><?php esc_html_e( 'Phyto Quick Add', 'phyto-quickadd' ); ?></h1>

<nav class="nav-tab-wrapper">
    <a href="?page=phyto-quickadd&tab=quickadd" class="nav-tab <?php echo $tab === 'quickadd' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Quick Add', 'phyto-quickadd' ); ?></a>
    <a href="?page=phyto-quickadd&tab=taxonomy" class="nav-tab <?php echo $tab === 'taxonomy' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Taxonomy', 'phyto-quickadd' ); ?></a>
    <a href="?page=phyto-quickadd&tab=settings" class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'phyto-quickadd' ); ?></a>
</nav>

<?php if ( $tab === 'quickadd' ) : ?>
<div id="phyto-qa-form-wrap">
<form id="phyto-qa-form" class="phyto-qa-form">
    <table class="form-table">
        <tr><th><?php esc_html_e( 'Product Name', 'phyto-quickadd' ); ?></th>
            <td><input type="text" name="name" id="phyto-qa-name" class="regular-text" required>
            <button type="button" id="phyto-qa-ai-btn" class="button"><?php esc_html_e( '✦ AI Description', 'phyto-quickadd' ); ?></button></td></tr>
        <tr><th><?php esc_html_e( 'SKU', 'phyto-quickadd' ); ?></th>
            <td><input type="text" name="sku" class="regular-text"></td></tr>
        <tr><th><?php esc_html_e( 'Regular Price (₹)', 'phyto-quickadd' ); ?></th>
            <td><input type="number" name="price" step="0.01" min="0" class="small-text"></td></tr>
        <tr><th><?php esc_html_e( 'Stock Qty', 'phyto-quickadd' ); ?></th>
            <td><input type="number" name="stock" min="0" class="small-text" value="0"></td></tr>
        <tr><th><?php esc_html_e( 'Description', 'phyto-quickadd' ); ?></th>
            <td><textarea name="description" id="phyto-qa-description" rows="4" class="large-text"></textarea></td></tr>
        <tr><th><?php esc_html_e( 'Categories', 'phyto-quickadd' ); ?></th>
            <td><select name="categories[]" multiple class="phyto-qa-cat-select">
            <?php foreach ( $categories as $cat ) : ?>
                <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
            <?php endforeach; ?>
            </select></td></tr>
        <tr><th><?php esc_html_e( 'AI Notes', 'phyto-quickadd' ); ?></th>
            <td><input type="text" id="phyto-qa-notes" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. rare cultivar, TC-grown, beginner-friendly', 'phyto-quickadd' ); ?>"></td></tr>
    </table>
    <p><button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Create Product', 'phyto-quickadd' ); ?></button></p>
    <div id="phyto-qa-result"></div>
</form>
</div>

<?php elseif ( $tab === 'taxonomy' ) : ?>
<h2><?php esc_html_e( 'Taxonomy Pack Import', 'phyto-quickadd' ); ?></h2>
<?php if ( $index ) : ?>
<table class="widefat striped">
<thead><tr><th><?php esc_html_e( 'Family', 'phyto-quickadd' ); ?></th><th><?php esc_html_e( 'Pack', 'phyto-quickadd' ); ?></th><th></th></tr></thead>
<tbody>
<?php foreach ( $index['categories'] ?? [] as $cat ) :
    $cat_index = Phyto_Taxonomy::fetch_category_index( $cat['id'] );
    foreach ( $cat_index['packs'] ?? [] as $pack ) : ?>
<tr>
    <td><?php echo esc_html( $cat['name'] ); ?></td>
    <td><?php echo esc_html( $pack['family'] ?? $pack['file'] ); ?></td>
    <td><button class="button phyto-qa-import-btn" data-pack="<?php echo esc_attr( $pack['file'] ); ?>"><?php esc_html_e( 'Import', 'phyto-quickadd' ); ?></button>
        <span class="phyto-qa-import-status"></span></td>
</tr>
<?php endforeach; endforeach; ?>
</tbody>
</table>
<?php else : ?>
<p><?php esc_html_e( 'Could not load taxonomy index. Check network access.', 'phyto-quickadd' ); ?></p>
<?php endif; ?>

<?php elseif ( $tab === 'settings' ) : ?>
<h2><?php esc_html_e( 'Settings', 'phyto-quickadd' ); ?></h2>
<form method="post" action="options.php">
<?php settings_fields( 'phyto_qa_settings_group' ); ?>
<table class="form-table">
    <tr><th><?php esc_html_e( 'AI Provider', 'phyto-quickadd' ); ?></th>
        <td><select name="phyto_qa_settings[ai_provider]">
            <option value="openai"    <?php selected( $settings['ai_provider'] ?? '', 'openai' ); ?>>OpenAI</option>
            <option value="anthropic" <?php selected( $settings['ai_provider'] ?? '', 'anthropic' ); ?>>Anthropic (Claude)</option>
        </select></td></tr>
    <tr><th><?php esc_html_e( 'AI API Key', 'phyto-quickadd' ); ?></th>
        <td><input type="password" name="phyto_qa_settings[ai_api_key]" value="<?php echo esc_attr( $settings['ai_api_key'] ?? '' ); ?>" class="regular-text"></td></tr>
</table>
<?php submit_button(); ?>
</form>
<?php endif; ?>
</div>
