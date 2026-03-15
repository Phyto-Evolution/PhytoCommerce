
{extends file='helpers/view/view.tpl'}

{block name="override_tpl"}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-leaf"></i> Phyto Quick Add Product
    </div>

    <div class="panel-body">
        <!-- OpenAI Key Config -->
        <div class="alert alert-info">
            <strong>OpenAI Key:</strong>
            <form method="post" class="form-inline" style="display:inline;">
                <input type="text" name="openai_key" class="form-control input-sm"
                    value="{$openai_key}" placeholder="sk-..." style="width:300px;">
                <button type="submit" name="saveOpenAIKey" class="btn btn-default btn-sm">Save Key</button>
            </form>
        </div>

        <!-- AI Description Generator -->
        <div class="well">
            <h4><i class="icon-magic"></i> Generate Description with AI</h4>
            <div class="input-group" style="max-width:500px;">
                <input type="text" id="plant_name_ai" class="form-control" placeholder="Enter plant name e.g. Nepenthes rajah">
                <span class="input-group-btn">
                    <button class="btn btn-primary" id="generateBtn" onclick="generateDescription()">
                        <i class="icon-magic"></i> Generate
                    </button>
                </span>
            </div>
            <div id="ai_loading" style="display:none;margin-top:10px;">
                <i class="icon-spinner icon-spin"></i> Generating description...
            </div>
        </div>

        <!-- Quick Add Form -->
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="submitQuickAdd" value="1">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="product_name" id="product_name" class="form-control" required
                            placeholder="e.g. Nepenthes rajah - Highland Pitcher Plant">
                    </div>

                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="product_short_description" id="product_short_description"
                            class="form-control" rows="2"
                            placeholder="2-3 sentence summary"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="product_description" id="product_description"
                            class="form-control" rows="6"
                            placeholder="Full product description"></textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="product_price" class="form-control"
                            step="0.01" min="0" required placeholder="999.00">
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="product_quantity" class="form-control"
                            min="0" required placeholder="10">
                    </div>

                    <div class="form-group">
                        <label>Category *</label>
                        <select name="product_category" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            {foreach $categories as $category}
                                <option value="{$category['id_category']}">{$category['name']}</option>
                            {/foreach}
                        </select>
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

<script>
function generateDescription() {
    var plantName = document.getElementById('plant_name_ai').value;
    if (!plantName) { alert('Please enter a plant name'); return; }

    document.getElementById('ai_loading').style.display = 'block';
    document.getElementById('generateBtn').disabled = true;

    fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'generateDescription=1&plant_name=' + encodeURIComponent(plantName)
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            document.getElementById('product_description').value = data.description || '';
            document.getElementById('product_short_description').value = data.short_description || '';
            document.getElementById('product_name').value = plantName;
        }
    })
    .catch(e => alert('Request failed: ' + e))
    .finally(() => {
        document.getElementById('ai_loading').style.display = 'none';
        document.getElementById('generateBtn').disabled = false;
    });
}
</script>
{/block}
