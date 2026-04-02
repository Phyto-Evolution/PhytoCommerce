/* global phytoQA, wp */
jQuery(function ($) {
	var nonce   = phytoQA.nonce;
	var ajaxurl = phytoQA.ajaxurl;

	// ── Tab: Add Product ──────────────────────────────────────────────

	// Media picker
	var mediaFrame;
	$('#qa-pick-images').on('click', function () {
		if ( ! mediaFrame ) {
			mediaFrame = wp.media({
				title:    'Select Product Images',
				multiple: true,
				library:  { type: 'image' },
				button:   { text: 'Use These Images' },
			});
			mediaFrame.on('select', function () {
				var attachments = mediaFrame.state().get('selection').toJSON();
				var ids = [];
				$('#qa-image-preview').empty();
				attachments.forEach(function (a) {
					ids.push(a.id);
					var src = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
					$('#qa-image-preview').append(
						$('<img>').attr('src', src).attr('title', 'Click to remove').data('id', a.id)
					);
				});
				$('#qa-image-ids').val(ids.join(','));
			});
		}
		mediaFrame.open();
	});

	$(document).on('click', '#qa-image-preview img', function () {
		var removeId = $(this).data('id');
		$(this).remove();
		var ids = $('#qa-image-ids').val().split(',').filter(function (i) { return i && parseInt(i, 10) !== removeId; });
		$('#qa-image-ids').val(ids.join(','));
	});

	// Generate description
	$('#qa-gen-desc').on('click', function () {
		var name = $('#qa-name').val().trim();
		if ( ! name ) { alert('Enter a product name first.'); return; }
		var context = [$('#qa-tags').val(), $('#qa-category option:selected').text()].filter(Boolean).join(', ');
		$('#qa-gen-status').text('Generating…');
		$.post(ajaxurl, {
			action:  'phyto_qa_generate_desc',
			nonce:   nonce,
			name:    name,
			context: context,
		}, function (r) {
			if (r.success) {
				$('#qa-desc').val(r.data.text);
				$('#qa-gen-status').text('Done!');
			} else {
				$('#qa-gen-status').text('Error: ' + r.data);
			}
		}).fail(function () { $('#qa-gen-status').text('Request failed.'); });
	});

	// Submit product
	$('#qa-submit').on('click', function () {
		var $btn = $(this);
		var status = $('#qa-submit-status');
		var name = $('#qa-name').val().trim();
		var price = $('#qa-price').val().trim();
		if ( ! name || ! price ) { status.text('Name and price are required.'); return; }

		$btn.prop('disabled', true).text('Adding…');
		status.text('');

		$.post(ajaxurl, {
			action:      'phyto_qa_add_product',
			nonce:       nonce,
			name:        name,
			price:       price,
			sale_price:  $('#qa-sale-price').val(),
			stock:       $('#qa-stock').val(),
			sku:         $('#qa-sku').val(),
			category:    $('#qa-category').val(),
			tags:        $('#qa-tags').val(),
			short_desc:  $('#qa-short-desc').val(),
			description: $('#qa-desc').val(),
			image_ids:   $('#qa-image-ids').val(),
		}, function (r) {
			$btn.prop('disabled', false).text('Add Product');
			if (r.success) {
				status.html('✓ Added! <a href="' + r.data.edit + '">Edit</a> | <a href="' + r.data.view + '" target="_blank">View</a>');
				// Reset form
				$('#qa-name,#qa-price,#qa-sale-price,#qa-sku,#qa-tags,#qa-short-desc,#qa-desc,#qa-image-ids').val('');
				$('#qa-stock').val('1');
				$('#qa-category').val('');
				$('#qa-image-preview').empty();
			} else {
				status.text('Error: ' + r.data);
			}
		}).fail(function () { $btn.prop('disabled', false).text('Add Product'); status.text('Request failed.'); });
	});

	// ── Tab: AI Settings ──────────────────────────────────────────────

	$('#qa-ai-provider').on('change', function () {
		var p = $(this).val();
		$('.qa-key-row').hide();
		$('.qa-key-row[data-provider="' + p + '"]').show();
	}).trigger('change');

	$('#qa-save-ai').on('click', function () {
		var $btn = $(this);
		var status = $('#qa-ai-status');
		var provider = $('#qa-ai-provider').val();
		var data = { action: 'phyto_qa_save_ai_settings', nonce: nonce, provider: provider };
		$('.qa-api-key').each(function () {
			data['key_' + $(this).data('provider')] = $(this).val();
		});
		$btn.prop('disabled', true);
		$.post(ajaxurl, data, function (r) {
			$btn.prop('disabled', false);
			status.text(r.success ? r.data : 'Error: ' + r.data);
		}).fail(function () { $btn.prop('disabled', false); status.text('Request failed.'); });
	});

	$('#qa-test-ai').on('click', function () {
		var $btn = $(this);
		var status = $('#qa-ai-status');
		var provider = $('#qa-ai-provider').val();
		var key = $('#qa-key-' + provider).val();
		$btn.prop('disabled', true);
		status.text('Testing…');
		$.post(ajaxurl, {
			action: 'phyto_qa_test_ai',
			nonce:  nonce,
			provider: provider,
			key:    key,
		}, function (r) {
			$btn.prop('disabled', false);
			status.text(r.success ? r.data : 'Error: ' + r.data);
		}).fail(function () { $btn.prop('disabled', false); status.text('Request failed.'); });
	});

	// ── Tab: Taxonomy Importer ────────────────────────────────────────

	$('#qa-fetch-taxonomy').on('click', function () {
		var $btn = $(this);
		var status = $('#qa-taxonomy-status');
		$btn.prop('disabled', true);
		status.text('Fetching taxonomy index from GitHub…');
		$.ajax({
			url:     ajaxurl,
			type:    'POST',
			timeout: 90000,
			data:    { action: 'phyto_qa_fetch_taxonomy', nonce: nonce },
			success: function (r) {
				$btn.prop('disabled', false);
				if ( ! r.success ) { status.text('Error: ' + r.data); return; }
				var total = r.data.total_packs || 0;
				var cats  = (r.data.categories || []).length;
				status.text('Loaded ' + total + ' packs across ' + cats + ' categories.');
				renderPackGrid(r.data);
			},
			error: function (xhr, type) {
				$btn.prop('disabled', false);
				status.text(type === 'timeout' ? 'Timed out — check your server can reach GitHub.' : 'Request failed.');
			},
		});
	});

	function renderPackGrid(index) {
		var $grid = $('#qa-taxonomy-packs').empty();
		if ( ! index.categories || ! index.categories.length ) {
			$grid.text('No categories found.');
			return;
		}
		if ( index.warnings && index.warnings.length ) {
			$grid.before('<p style="color:#c00">Warning: ' + escHtml(index.warnings.join('; ')) + '</p>');
		}
		index.categories.forEach(function (cat) {
			if ( ! cat.packs || ! cat.packs.length ) return;

			var $heading = $('<h4 style="width:100%;margin:12px 0 4px;color:#2d7a54">').text(cat.name);
			$grid.append($heading);

			cat.packs.forEach(function (pack) {
				var $card = $('<div class="phyto-qa-pack-card">');
				$card.append('<h4>' + escHtml(pack.name || pack.id) + '</h4>');
				var meta = cat.name;
				if (pack.genera_count) { meta += ' · ' + pack.genera_count + ' genera'; }
				$card.append('<div class="pack-meta">' + escHtml(meta) + '</div>');

				var $btn = $('<button class="button button-secondary">Import as WC Categories</button>');
				var $result = $('<div>');
				$btn.on('click', function () {
					$btn.prop('disabled', true).text('Importing…');
					$result.removeClass('pack-result pack-error').text('');
					$.post(ajaxurl, {
						action: 'phyto_qa_import_pack',
						nonce:  nonce,
						path:   pack.file || '',
					}, function (r) {
						$btn.prop('disabled', false).text('Import as WC Categories');
						if (r.success) {
							$result.addClass('pack-result').text(
								'✓ ' + r.data.imported + ' genera imported' +
								(r.data.skipped ? ', ' + r.data.skipped + ' skipped' : '') +
								(r.data.errors && r.data.errors.length ? ' (' + r.data.errors.length + ' errors)' : '')
							);
						} else {
							$result.addClass('pack-error').text('Error: ' + escHtml(r.data));
						}
					}).fail(function () {
						$btn.prop('disabled', false).text('Import as WC Categories');
						$result.addClass('pack-error').text('Request failed.');
					});
				});
				$card.append($btn).append($result);
				$grid.append($card);
			});
		});
	}

	function escHtml(str) {
		return $('<div>').text(String(str)).html();
	}
});
