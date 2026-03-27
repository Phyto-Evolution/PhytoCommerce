{**
 * PhytoImageSec — front_protection hook template.
 *
 * Outputs an inline <style> block that reinforces the CSS file for themes
 * that may override user-select. The JS protection is loaded via registerJavascript().
 *}
<style>
img { -webkit-user-drag: none !important; user-drag: none !important; }
</style>
