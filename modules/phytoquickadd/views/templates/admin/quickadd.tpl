{extends file='helpers/view/view.tpl'}

{block name="override_tpl"}

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="#tab-product" data-toggle="tab"><i class="icon-leaf"></i> Add Product</a></li>
    <li><a href="#tab-category" data-toggle="tab"><i class="icon-folder-open"></i> Add Category</a></li>
    <li><a href="#tab-settings" data-toggle="tab"><i class="icon-cog"></i> Settings</a></li>
</ul>

<div class="tab-content">

<div class="tab-pane active" id="tab-product">
<div class="panel">
    <div class="panel-heading"><i class="icon-leaf"></i> Quick Add Product</div>
    <div class="panel-body">

        <div class="well" style="padding:12px 15px;margin-bottom:20px;">
            <label class="checkbox-inline" style="font-size:14px;cursor:pointer;">
                <input type="checkbox" id="enable_ai" onchange="toggleAI(this.checked)">
                &nbsp;<i class="icon-magic"></i> <strong>Use AI to generate description</strong>
                <small class="text-muted"> (requires OpenAI API key in Settings tab)</small>
            </label>
        </div>

        <div id="ai_section" style="display:none;" class="well">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" id="plant_name_ai" class="form-control"
                               placeholder="e.g. Nepenthes rajah, Dionaea muscipula B52">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" id="btn_generate" onclick="generateDescription()">
                                <i class="icon-magic"></i> Generate
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div id="ai_status" style="margin-top:8px;display:none;">
                <i class="icon-spinner icon-spin"></i> Generating description...
            </div>
            <div id="ai_error" class="alert alert-danger" style="display:none;margin-top:8px;"></div>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="submitQuickAdd" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" id="product_name" class="form-control" required
                               placeholder="e.g. Nepenthes rajah - Highland Pitcher Plant">
                    </div>
                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="product_short_description" id="product_short_description"
                                  class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="product_description" id="product_description"
                                  class="form-control" rows="7"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="product_price" class="form-control"
                               step="0.01" min="0" required placeholder="999.00">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="product_quantity" class="form-control"
                               min="0" required value="1">
                    </div>
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="product_category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            {if isset($flat_categories)}
                                {foreach $flat_categories as $cat}
                                    <option value="{$cat.id}">{$cat.name}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <small class="text-muted">
                            Need a new category? <a href="#tab-category" data-toggle="tab">Add it here →</a>
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="product_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="icon-plus"></i> Add Product
            </button>
        </form>

    </div>
</div>
</div>

<div class="tab-pane" id="tab-category">
<div class="panel">
    <div class="panel-heading"><i class="icon-folder-open"></i> Add Category / Sub-category</div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="submitAddCategory" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required
                               placeholder="e.g. Nepenthes, Highland Species, Tissue Culture">
                    </div>
                    <div class="form-group">
                        <label>Parent Category <span class="text-danger">*</span></label>
                        <select name="parent_category" class="form-control" required>
                            <option value="">-- Select Parent --</option>
                            {if isset($flat_categories)}
                                {foreach $flat_categories as $cat}
                                    <option value="{$cat.id}">{$cat.name}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <small class="text-muted">Select "Home" to create a top-level category</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-folder-open"></i> Add Category
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">Current Category Tree</div>
                        <div class="panel-body" style="max-height:400px;overflow-y:auto;">
                            {if isset($category_tree)}
                                {include file='module:phytoquickadd/views/templates/admin/category_tree.tpl'
                                         categories=$category_tree}
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<div class="tab-pane" id="tab-settings">
<div class="panel">
    <div class="panel-heading"><i class="icon-cog"></i> Settings</div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="saveSettings" value="1">
            <div class="form-group col-md-6">
                <label>OpenAI API Key</label>
                <input type="text" name="openai_key" class="form-control"
                       value="{if isset($openai_key)}{$openai_key}{/if}"
                       placeholder="sk-...">
                <small class="text-muted">
                    Get your key at <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a>
                </small>
            </div>
            <div class="clearfix"></div><br>
            <button type="submit" class="btn btn-primary">
                <i class="icon-save"></i> Save Settings
            </button>
        </form>
    </div>
</div>
</div>

</div>

{literal}
<script>
var AJAX_URL = '{/literal}{$ajax_url}{literal}';

function toggleAI(enabled) {
    document.getElementById('ai_section').style.display = enabled ? 'block' : 'none';
}

function generateDescription() {
    var plantName = document.getElementById('plant_name_ai').value.trim();
    if (!plantName) { alert('Please enter a plant name first.'); return; }

    var btn    = document.getElementById('btn_generate');
    var status = document.getElementById('ai_status');
    var errBox = document.getElementById('ai_error');

    btn.disabled         = true;
    status.style.display = 'block';
    errBox.style.display = 'none';

    fetch(AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phyto_ajax=1&phyto_action=generate_description&plant_name=' + encodeURIComponent(plantName)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            errBox.textContent   = data.error;
            errBox.style.display = 'block';
        } else {
            document.getElementById('product_name').value                = plantName;
            document.getElementById('product_description').value         = data.description || '';
            document.getElementById('product_short_description').value   = data.short_description || '';
        }
    })
    .catch(function(e) {
        errBox.textContent   = 'Request failed: ' + e.message;
        errBox.style.display = 'block';
    })
    .finally(function() {
        btn.disabled         = false;
        status.style.display = 'none';
    });
}
</script>
{/literal}

{/block}
