{extends file='helpers/view/view.tpl'}

{block name="override_tpl"}

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="#tab-product"  data-toggle="tab"><i class="icon-leaf"></i> Add Product</a></li>
    <li><a href="#tab-category" data-toggle="tab"><i class="icon-folder-open"></i> Add Category</a></li>
    <li><a href="#tab-taxonomy" data-toggle="tab"><i class="icon-sitemap"></i> Taxonomy Packs</a></li>
    <li><a href="#tab-settings" data-toggle="tab"><i class="icon-cog"></i> Settings</a></li>
</ul>

<div class="tab-content">

<div class="tab-pane active" id="tab-product">
<div class="panel">
    <div class="panel-heading"><i class="icon-leaf"></i> Quick Add Product</div>
    <div class="panel-body">

        <div class="well" style="padding:12px 15px;margin-bottom:20px;background:#f8fff8;border-color:#c3e6cb;">
            <label class="checkbox-inline" style="font-size:14px;cursor:pointer;margin:0;">
                <input type="checkbox" id="enable_ai" onchange="toggleAI(this.checked)">
                &nbsp;<i class="icon-magic" style="color:#6f42c1;"></i>
                <strong>Use AI to generate description</strong>
                <small class="text-muted"> — requires OpenAI API key in Settings</small>
            </label>
        </div>

        <div id="ai_section" style="display:none;" class="well">
            <h5><i class="icon-magic"></i> AI Description Generator</h5>
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
                    <small class="text-muted">AI will fill name, short and full description below</small>
                </div>
            </div>
            <div id="ai_status" style="margin-top:10px;display:none;">
                <i class="icon-spinner icon-spin"></i> Generating — please wait...
            </div>
            <div id="ai_error" class="alert alert-danger" style="display:none;margin-top:10px;"></div>
        </div>

        <form method="post" enctype="multipart/form-data" id="product_form" onsubmit="sessionStorage.setItem('phyto_tab','tab-product');">
            <input type="hidden" name="submitQuickAdd" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" id="product_name"
                               class="form-control" required
                               placeholder="e.g. Nepenthes rajah — Highland Pitcher Plant">
                    </div>
                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="product_short_description" id="product_short_description"
                                  class="form-control" rows="3"
                                  placeholder="2-3 sentence summary shown in listings"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            Notes
                            <small class="text-muted">— internal notes; <code>#hashtags</code> are saved as product tags</small>
                        </label>
                        <textarea name="product_notes" id="product_notes"
                                  class="form-control" rows="3"
                                  placeholder="e.g. #tissue-culture #highland received from lab Mar 2024, good root system"
                                  oninput="previewHashtags(this.value)"></textarea>
                        <div id="notes_tags_preview" style="margin-top:6px;min-height:22px;line-height:1.8;"></div>
                    </div>
                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="product_description" id="product_description"
                                  class="form-control" rows="8"
                                  placeholder="Full description — care notes, origin, features"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Price (Rs.) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Rs.</span>
                            <input type="number" name="product_price" class="form-control"
                                   step="0.01" min="0" required placeholder="999.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="product_quantity" class="form-control"
                               min="0" required value="1">
                    </div>
                    <div class="form-group">
                        <label>
                            Categories <span class="text-danger">*</span>
                            <small class="text-muted">— hold <kbd>Ctrl</kbd> / <kbd>⌘</kbd> to select multiple; first selected = primary</small>
                        </label>
                        <input type="text" id="category_search" class="form-control"
                               placeholder="Type to filter categories..."
                               onkeyup="filterCategories(this.value)"
                               style="margin-bottom:5px;">
                        <select name="product_categories[]" id="category_select"
                                class="form-control" multiple size="7"
                                onchange="updateSelectedCats()">
                            {if isset($flat_categories)}
                                {foreach $flat_categories as $cat}
                                    <option value="{$cat.id}"
                                        data-name="{$cat.name|lower}">{$cat.name}</option>
                                {/foreach}
                            {/if}
                        </select>
                        <div id="selected_cats_display"
                             style="margin-top:6px;min-height:22px;line-height:1.8;">
                            <span class="text-muted" style="font-size:12px;">No categories selected</span>
                        </div>
                        <small class="text-muted" style="margin-top:4px;display:block;">
                            <a href="#tab-category" data-toggle="tab">Add category →</a>
                            &nbsp;|&nbsp;
                            <a href="#tab-taxonomy" data-toggle="tab">Import taxonomy packs →</a>
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="product_image" class="form-control"
                               accept="image/*" onchange="previewImage(this)">
                        <div id="img_preview" style="margin-top:8px;display:none;">
                            <img id="img_preview_src"
                                 style="max-height:120px;border-radius:4px;border:1px solid #ddd;">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="icon-plus"></i> Add Product
            </button>
            <button type="reset" class="btn btn-default btn-lg" style="margin-left:10px;">
                <i class="icon-refresh"></i> Clear
            </button>
        </form>

    </div>
</div>
</div>

<div class="tab-pane" id="tab-category">
<div class="panel">
    <div class="panel-heading"><i class="icon-folder-open"></i> Add Category / Sub-category</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-5">
                <div id="cat_success" class="alert alert-success" style="display:none;"></div>
                <div id="cat_error" class="alert alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Category Name <span class="text-danger">*</span></label>
                    <input type="text" id="new_category_name" class="form-control"
                           placeholder="e.g. Nepenthes, Highland Species, Tissue Culture">
                </div>
                <div class="form-group">
                    <label>Parent Category <span class="text-danger">*</span></label>
                    <input type="text" id="parent_search" class="form-control"
                           placeholder="Type to filter..."
                           onkeyup="filterParents(this.value)"
                           style="margin-bottom:5px;">
                    <select id="parent_select" class="form-control" size="6">
                        {if isset($flat_categories)}
                            {foreach $flat_categories as $cat}
                                <option value="{$cat.id}"
                                    data-name="{$cat.name|lower}"
                                    {if $cat.id == 2}selected{/if}>{$cat.name}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <small class="text-muted">Home = top level category</small>
                </div>
                <button class="btn btn-primary btn-lg" onclick="addCategory()">
                    <i class="icon-folder-open"></i> Add Category
                </button>
            </div>
            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="icon-sitemap"></i> Current Category Tree</div>
                    <div id="category_tree_display" class="panel-body" style="max-height:450px;overflow-y:auto;font-size:13px;">
                        {if isset($flat_categories)}
                            {foreach $flat_categories as $cat}
                                <div style="padding:2px 4px;">{$cat.name}</div>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="tab-pane" id="tab-taxonomy">
<div class="panel">
    <div class="panel-heading">
        <i class="icon-sitemap"></i> Taxonomy Packs
        <small class="text-muted" style="margin-left:10px;">
            Import botanical families as ready-to-use shop categories
        </small>
        <button class="btn btn-default btn-sm pull-right" onclick="loadPacks()">
            <i class="icon-refresh"></i> Refresh from GitHub
        </button>
    </div>
    <div class="panel-body">

        <div id="packs_loading" style="text-align:center;padding:30px;">
            <i class="icon-spinner icon-spin icon-2x"></i>
            <p style="margin-top:10px;color:#888;">Loading taxonomy packs from GitHub...</p>
        </div>

        <div id="packs_error" class="alert alert-danger" style="display:none;"></div>
        <div id="packs_container" style="display:none;"></div>

        <div id="import_log" class="panel panel-default" style="display:none;margin-top:20px;">
            <div class="panel-heading"><i class="icon-list"></i> Import Log</div>
            <div class="panel-body">
                <pre id="import_log_content"
                     style="max-height:300px;overflow-y:auto;font-size:12px;"></pre>
            </div>
        </div>

    </div>
</div>
</div>

<div class="tab-pane" id="tab-settings">
<div class="panel">
    <div class="panel-heading"><i class="icon-cog"></i> Settings</div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="saveSettings" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="icon-magic"></i> Claude AI API Key</label>
                        <input type="password" name="ai_key" class="form-control"
                               value="{if isset($ai_key)}{$ai_key}{/if}"
                               placeholder="sk-ant-...">
                        <small class="text-muted">
                            Get your key at
                            <a href="https://console.anthropic.com/settings/keys" target="_blank">
                                console.anthropic.com
                            </a>
                            — stored as PHYTO_AI_KEY
                        </small>
                    </div>
                </div>
            </div>
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


function addCategory() {
    var name      = document.getElementById('new_category_name').value.trim();
    var id_parent = document.getElementById('parent_select').value;
    var success   = document.getElementById('cat_success');
    var error     = document.getElementById('cat_error');

    success.style.display = 'none';
    error.style.display   = 'none';

    if (!name)      { error.textContent = 'Category name is required.'; error.style.display = 'block'; return; }
    if (!id_parent) { error.textContent = 'Please select a parent.';    error.style.display = 'block'; return; }

    phytoAjax('add_category', { category_name: name, parent_category: id_parent })
    .then(function(data) {
        if (data.error) {
            error.textContent   = data.error;
            error.style.display = 'block';
        } else {
            success.textContent   = 'Category "' + name + '" added successfully!';
            success.style.display = 'block';
            document.getElementById('new_category_name').value = '';
            reloadCategories();
        }
    })
    .catch(function(e) {
        error.textContent   = 'Failed: ' + e.message;
        error.style.display = 'block';
    });
}

function reloadCategories() {
    phytoAjax('get_categories')
    .then(function(data) {
        if (!data || !data.length) return;
        // Preserve currently selected values in the multi-select
        var sel = document.getElementById('category_select');
        var prevSelected = [];
        if (sel) {
            for (var i = 0; i < sel.options.length; i++) {
                if (sel.options[i].selected) prevSelected.push(sel.options[i].value);
            }
        }
        updateSelect('category_select', data, null, prevSelected);
        updateSelect('parent_select',   data, 2);
        updateCategoryTree(data);
        updateSelectedCats();
    });
}

function updateSelect(selectId, categories, selectedId, selectedIds) {
    var sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '';
    categories.forEach(function(cat) {
        var opt = document.createElement('option');
        opt.value = cat.id;
        opt.text  = cat.name;
        opt.setAttribute('data-name', cat.name.toLowerCase());
        if (selectedId && cat.id == selectedId) opt.selected = true;
        if (selectedIds && selectedIds.indexOf(String(cat.id)) > -1) opt.selected = true;
        sel.appendChild(opt);
    });
}

function updateCategoryTree(categories) {
    var tree = document.getElementById('category_tree_display');
    if (!tree) return;
    tree.innerHTML = categories.map(function(c) {
        return '<div style="padding:2px 4px;">' + c.name + '</div>';
    }).join('');
}

function toggleAI(enabled) {
    document.getElementById('ai_section').style.display = enabled ? 'block' : 'none';
}

function generateDescription() {
    var plantName = document.getElementById('plant_name_ai').value.trim();
    if (!plantName) { alert('Please enter a plant name first.'); return; }

    var btn    = document.getElementById('btn_generate');
    var status = document.getElementById('ai_status');
    var errBox = document.getElementById('ai_error');

    btn.disabled = true;
    status.style.display = 'block';
    errBox.style.display = 'none';

    phytoAjax('generate_description', { plant_name: plantName })
    .then(function(data) {
        if (data.error) {
            errBox.textContent = data.error;
            errBox.style.display = 'block';
        } else {
            document.getElementById('product_name').value              = plantName;
            document.getElementById('product_description').value       = data.description || '';
            document.getElementById('product_short_description').value = data.short_description || '';
        }
    })
    .catch(function(e) {
        errBox.textContent = 'Request failed: ' + e.message;
        errBox.style.display = 'block';
    })
    .finally(function() {
        btn.disabled = false;
        status.style.display = 'none';
    });
}

function previewHashtags(text) {
    var preview = document.getElementById('notes_tags_preview');
    var tags = [];
    var matches = text.match(/#([a-zA-Z0-9_\-]+)/g);
    if (matches) {
        matches.forEach(function(t) { tags.push(t.slice(1)); });
    }
    if (tags.length === 0) {
        preview.innerHTML = '';
        return;
    }
    preview.innerHTML = '<small class="text-muted" style="margin-right:4px;">Tags:</small>'
        + tags.map(function(t) {
            return '<span class="label label-info" style="margin-right:3px;font-size:11px;">#' + t + '</span>';
        }).join('');
}

function updateSelectedCats() {
    var sel     = document.getElementById('category_select');
    var display = document.getElementById('selected_cats_display');
    if (!sel || !display) return;
    var selected = [];
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].selected) selected.push({ id: sel.options[i].value, name: sel.options[i].text.trim() });
    }
    if (selected.length === 0) {
        display.innerHTML = '<span class="text-muted" style="font-size:12px;">No categories selected</span>';
        return;
    }
    display.innerHTML = selected.map(function(c, idx) {
        var style = idx === 0
            ? 'label label-success'   // primary
            : 'label label-default';
        var primary = idx === 0 ? ' <small>(primary)</small>' : '';
        return '<span class="' + style + '" style="margin-right:4px;font-size:11px;">'
            + c.name + primary + '</span>';
    }).join('');
}

function filterCategories(term) { filterSelect('category_select', term); }
function filterParents(term)    { filterSelect('parent_select', term); }

function filterSelect(selectId, term) {
    var opts = document.getElementById(selectId).options;
    term = term.toLowerCase();
    for (var i = 0; i < opts.length; i++) {
        var name = opts[i].getAttribute('data-name') || opts[i].text.toLowerCase();
        opts[i].style.display = (!term || name.indexOf(term) > -1) ? '' : 'none';
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('img_preview_src').src = e.target.result;
            document.getElementById('img_preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function loadPacks() {
    document.getElementById('packs_loading').style.display = 'block';
    document.getElementById('packs_container').style.display = 'none';
    document.getElementById('packs_error').style.display = 'none';

    phytoAjax('fetch_packs')
    .then(function(data) {
        if (data.error) {
            document.getElementById('packs_error').textContent = data.error;
            document.getElementById('packs_error').style.display = 'block';
        } else {
            renderPacks(data);
        }
    })
    .catch(function(e) {
        document.getElementById('packs_error').textContent = 'Failed: ' + e.message;
        document.getElementById('packs_error').style.display = 'block';
    })
    .finally(function() {
        document.getElementById('packs_loading').style.display = 'none';
    });
}

function renderPacks(data) {
    var container = document.getElementById('packs_container');
    var html = '';
    var byCategory = {};
    data.packs.forEach(function(pack) {
        if (!byCategory[pack.category]) byCategory[pack.category] = [];
        byCategory[pack.category].push(pack);
    });
    var catNames = {};
    if (data.categories) {
        data.categories.forEach(function(c) { catNames[c.id] = c.name; });
    }
    Object.keys(byCategory).forEach(function(catId) {
        html += '<h4 style="margin-top:20px;border-bottom:2px solid #eee;padding-bottom:8px;">';
        html += '<i class="icon-leaf"></i> ' + (catNames[catId] || catId) + '</h4>';
        html += '<div class="row">';
        byCategory[catId].forEach(function(pack) {
            var isImported = pack.imported;
            var badge = isImported
                ? '<span class="label label-success"><i class="icon-check"></i> Imported</span>'
                : '<span class="label label-default">Not imported</span>';
            var importedInfo = isImported
                ? '<br><small class="text-muted">Imported ' + pack.imported_at + ' &middot; ' + pack.count + ' categories</small>'
                : '';
            html += '<div class="col-md-4" style="margin-bottom:15px;">';
            html += '<div class="panel panel-default">';
            html += '<div class="panel-body">';
            html += '<h5 style="margin-top:0;">' + pack.display_name + ' ' + badge + '</h5>';
            html += '<p style="font-size:12px;color:#666;">' + pack.description + '</p>';
            html += '<p style="font-size:11px;"><strong>Genera:</strong> ' + pack.genera.join(', ') + '</p>';
            html += '<p style="font-size:11px;"><strong>Difficulty:</strong> ' + pack.difficulty_range + '</p>';
            html += importedInfo;
            html += '<div style="margin-top:10px;">';
            if (!isImported) {
                html += '<button class="btn btn-primary btn-sm" onclick="importPack(\'' + pack.file + '\',\'' + pack.display_name + '\')">';
                html += '<i class="icon-download"></i> Import Categories</button>';
            } else {
                html += '<button class="btn btn-warning btn-sm" onclick="syncPack(\'' + pack.file + '\',\'' + pack.display_name + '\')">';
                html += '<i class="icon-refresh"></i> Sync</button>';
            }
            html += '</div></div></div></div>';
        });
        html += '</div>';
    });
    if (data.coming_soon && data.coming_soon.length) {
        html += '<h4 style="margin-top:20px;border-bottom:2px solid #eee;padding-bottom:8px;color:#aaa;">';
        html += '<i class="icon-time"></i> Coming Soon</h4><div class="row">';
        data.coming_soon.forEach(function(pack) {
            html += '<div class="col-md-4" style="margin-bottom:15px;">';
            html += '<div class="panel panel-default" style="opacity:0.5;">';
            html += '<div class="panel-body">';
            html += '<h5 style="margin-top:0;">' + pack.display_name + ' <span class="label label-warning">Planned</span></h5>';
            html += '<p style="font-size:12px;color:#666;">' + (pack.description || '') + '</p>';
            html += '</div></div></div>';
        });
        html += '</div>';
    }
    container.innerHTML = html;
    container.style.display = 'block';
}

function importPack(packFile, packName) {
    if (!confirm('Import "' + packName + '" as categories? This will create the full family/genus/species hierarchy.')) return;
    showImportLog('Importing ' + packName + '...\n');
    phytoAjax('import_pack', { pack_file: packFile })
    .then(function(data) {
        if (data.error) {
            appendLog('ERROR: ' + data.error);
        } else {
            appendLog('Import complete! ' + data.imported + ' categories created.\n');
            if (data.log) data.log.forEach(function(l) { appendLog(l); });
            appendLog('\nDone! Reloading page to refresh category dropdowns...');
        setTimeout(function() { sessionStorage.setItem('phyto_tab','tab-taxonomy'); window.location.reload(); }, 2000);
            loadPacks();
        }
    })
    .catch(function(e) { appendLog('Failed: ' + e.message); });
}

function syncPack(packFile, packName) {
    if (!confirm('Sync "' + packName + '"? This will add any new species/cultivars.')) return;
    showImportLog('Syncing ' + packName + '...\n');
    phytoAjax('sync_pack', { pack_file: packFile })
    .then(function(data) {
        if (data.error) {
            appendLog('ERROR: ' + data.error);
        } else {
            appendLog('Sync complete! ' + data.imported + ' categories updated.\n');
            if (data.log) data.log.forEach(function(l) { appendLog(l); });
            loadPacks();
        }
    })
    .catch(function(e) { appendLog('Failed: ' + e.message); });
}

function showImportLog(text) {
    document.getElementById('import_log').style.display = 'block';
    document.getElementById('import_log_content').textContent = text;
}

function appendLog(text) {
    var el = document.getElementById('import_log_content');
    el.textContent += text + '\n';
    el.scrollTop = el.scrollHeight;
}

function phytoAjax(action, params) {
    var body = 'phyto_ajax=1&phyto_action=' + encodeURIComponent(action);
    if (params) {
        Object.keys(params).forEach(function(k) {
            body += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        });
    }
    return fetch(AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    }).then(function(r) { return r.json(); });
}

// Restore active tab after page reload
document.addEventListener('DOMContentLoaded', function() {
    var savedTab = sessionStorage.getItem('phyto_tab');
    if (savedTab) {
        sessionStorage.removeItem('phyto_tab');
        var tabLink = document.querySelector('a[href="#' + savedTab + '"]');
        if (tabLink) tabLink.click();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var taxTab = document.querySelector('a[href="#tab-taxonomy"]');
    if (taxTab) {
        taxTab.addEventListener('click', function() {
            if (document.getElementById('packs_container').innerHTML === '') loadPacks();
        });
    }
});
</script>
{/literal}

{/block}
