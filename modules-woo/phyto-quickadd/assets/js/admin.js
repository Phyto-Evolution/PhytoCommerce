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
			url:      ajaxurl,
			type:     'POST',
			dataType: 'json',
			timeout:  90000,
			data:     { action: 'phyto_qa_fetch_taxonomy', nonce: nonce },
			success: function (r) {
				$btn.prop('disabled', false);
				if ( ! r || ! r.success ) {
					status.text('Error: ' + ( r && r.data ? r.data : 'Unexpected server response' ));
					return;
				}
				var index = r.data;
				if ( ! index || typeof index !== 'object' ) {
					status.text('Error: response data is not an object.');
					return;
				}
				var total = index.total_packs || 0;
				var cats  = Array.isArray(index.categories) ? index.categories.length : 0;
				status.text('Loaded ' + total + ' packs across ' + cats + ' categories.');
				try {
					renderPackGrid(index);
				} catch (e) {
					status.text('Render error: ' + e.message);
				}
			},
			error: function (xhr, type, err) {
				$btn.prop('disabled', false);
				if ( type === 'timeout' ) {
					status.text('Timed out — check your server can reach GitHub.');
				} else if ( type === 'parsererror' ) {
					status.text('JSON parse error — your server may be outputting PHP notices. Enable WP_DEBUG_LOG and disable WP_DEBUG_DISPLAY.');
				} else {
					status.text('Request failed: ' + ( err || type ));
				}
			},
		});
	});

	function renderPackGrid(index) {
		var $grid     = $('#qa-taxonomy-packs').empty();
		var $warnings = $grid.prev('.phyto-qa-warnings');
		if ( $warnings.length ) { $warnings.remove(); }

		var categories = Array.isArray(index.categories) ? index.categories : [];
		if ( ! categories.length ) {
			$grid.text('No categories found.');
			return;
		}

		if ( Array.isArray(index.warnings) && index.warnings.length ) {
			$('<p class="phyto-qa-warnings" style="color:#c00">Warning: ' + escHtml(index.warnings.join('; ')) + '</p>')
				.insertBefore($grid);
		}

		categories.forEach(function (cat) {
			var packs = Array.isArray(cat.packs) ? cat.packs : [];
			if ( ! packs.length ) return;

			$grid.append(
				$('<h4 style="width:100%;margin:12px 0 4px;color:#2d7a54">').text(cat.name || cat.id || 'Category')
			);

			packs.forEach(function (pack) {
				var $card   = $('<div class="phyto-qa-pack-card">');
				var $result = $('<div>');
				var packFile = pack.file || '';
				var packName = pack.name || pack.id || 'Unknown pack';
				var meta     = (cat.name || '') + (pack.genera_count ? ' · ' + pack.genera_count + ' genera' : '');

				$card.append('<h4>' + escHtml(packName) + '</h4>');
				$card.append('<div class="pack-meta">' + escHtml(meta) + '</div>');

				var $importBtn = $('<button class="button button-secondary">Import as WC Categories</button>');
				$importBtn.on('click', function () {
					$importBtn.prop('disabled', true).text('Importing…');
					$result.removeClass('pack-result pack-error').text('');
					$.ajax({
						url:      ajaxurl,
						type:     'POST',
						dataType: 'json',
						data: {
							action: 'phyto_qa_import_pack',
							nonce:  nonce,
							path:   packFile,
						},
						success: function (r) {
							$importBtn.prop('disabled', false).text('Import as WC Categories');
							if ( r && r.success ) {
								var d       = r.data || {};
								var errs    = Array.isArray(d.errors) ? d.errors.length : 0;
								$result.addClass('pack-result').text(
									'✓ ' + (d.imported || 0) + ' genera imported' +
									(d.skipped ? ', ' + d.skipped + ' skipped' : '') +
									(errs ? ' (' + errs + ' errors)' : '')
								);
							} else {
								$result.addClass('pack-error').text('Error: ' + escHtml( r && r.data ? String(r.data) : 'Unknown error' ));
							}
						},
						error: function (xhr, type, err) {
							$importBtn.prop('disabled', false).text('Import as WC Categories');
							$result.addClass('pack-error').text('Request failed: ' + (err || type));
						},
					});
				});
				$card.append($importBtn).append($result);
				$grid.append($card);
			});
		});
	}

	function escHtml(str) {
		return $('<div>').text(String(str)).html();
	}
});
