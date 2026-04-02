jQuery(function ($) {
	var nonce   = phytoBB.nonce;
	var ajaxurl = phytoBB.ajaxurl;

	// Per-builder state: selections[slot_index] = {product_id, name, price, price_html, img}
	$('.phyto-bb-builder').each(function () {
		var $builder    = $(this);
		var templateId  = $builder.data('template');
		var discountPct = parseInt($builder.data('discount'), 10) || 0;
		var selections  = {};
		var searchTimers = {};

		// ── Search ────────────────────────────────────────────────────
		$builder.on('input', '.phyto-bb-search-input', function () {
			var $input = $(this);
			var $slot  = $input.closest('.phyto-bb-slot');
			var idx    = $slot.data('slot');

			clearTimeout(searchTimers[idx]);
			searchTimers[idx] = setTimeout(function () {
				var q = $input.val().trim();
				if (q.length < 2) { $slot.find('.phyto-bb-results').hide(); return; }

				$.post(ajaxurl, {
					action:       'phyto_bb_get_products',
					nonce:        nonce,
					search:       q,
					product_ids:  $slot.data('products')  || '[]',
					category_ids: $slot.data('categories') || '[]',
				}, function (r) {
					var $results = $slot.find('.phyto-bb-results').empty().show();
					if (!r.success || !r.data.length) {
						$results.append('<div style="padding:8px 10px;color:#999">No products found.</div>');
						return;
					}
					r.data.forEach(function (p) {
						$results.append(
							$('<div class="phyto-bb-result-item">').data('product', p)
								.append($('<img>').attr('src', p.img))
								.append($('<span class="name">').text(p.name))
								.append($('<span class="price">').html(p.price_html))
						);
					});
				});
			}, 300);
		});

		// Close dropdown on outside click
		$(document).on('click', function (e) {
			if (!$(e.target).closest('.phyto-bb-slot').length) {
				$builder.find('.phyto-bb-results').hide();
			}
		});

		// Select a product from results
		$builder.on('click', '.phyto-bb-result-item', function () {
			var $item   = $(this);
			var p       = $item.data('product');
			var $slot   = $item.closest('.phyto-bb-slot');
			var idx     = $slot.data('slot');

			selections[idx] = p;
			updateSlotDisplay($slot, p);
			$slot.find('.phyto-bb-results').hide();
			$slot.find('.phyto-bb-search-input').val('');
			updateSummary();
		});

		// Clear a slot
		$builder.on('click', '.phyto-bb-slot-clear', function () {
			var $slot = $(this).closest('.phyto-bb-slot');
			var idx   = $slot.data('slot');
			delete selections[idx];
			clearSlotDisplay($slot);
			updateSummary();
		});

		function updateSlotDisplay($slot, p) {
			$slot.addClass('filled');
			$slot.find('.phyto-bb-slot-selected')
				.removeClass('phyto-bb-empty')
				.html(
					$('<img>').attr('src', p.img) +
					'<span class="sel-name">' + escHtml(p.name) + '</span>' +
					'<span class="sel-price">' + p.price_html + '</span>' +
					'<button class="phyto-bb-slot-clear" title="Remove">✕</button>'
				);
		}

		function clearSlotDisplay($slot) {
			$slot.removeClass('filled');
			$slot.find('.phyto-bb-slot-selected')
				.addClass('phyto-bb-empty')
				.text('No product selected');
		}

		function updateSummary() {
			var slotCount    = $builder.find('.phyto-bb-slot').length;
			var selectedKeys = Object.keys(selections);
			var total        = 0;

			selectedKeys.forEach(function (idx) { total += selections[idx].price; });

			var displayTotal = total;
			if (discountPct > 0 && selectedKeys.length === slotCount) {
				displayTotal = total * (1 - discountPct / 100);
			}

			$builder.find('.phyto-bb-total-price').text(
				selectedKeys.length === 0 ? '—' : formatPrice(displayTotal)
			);

			var allFilled = selectedKeys.length === slotCount;
			$builder.find('.phyto-bb-add-to-cart').prop('disabled', !allFilled);
		}

		// ── Add to cart ───────────────────────────────────────────────
		$builder.on('click', '.phyto-bb-add-to-cart', function () {
			var $btn    = $(this);
			var $status = $builder.find('.phyto-bb-cart-status');
			var sels    = Object.keys(selections).map(function (idx) {
				return { slot: idx, product_id: selections[idx].id };
			});

			$btn.prop('disabled', true).text('Adding…');
			$status.text('');

			$.post(ajaxurl, {
				action:      'phyto_bb_add_bundle',
				nonce:       nonce,
				template_id: templateId,
				selections:  JSON.stringify(sels),
			}, function (r) {
				$btn.prop('disabled', false).text('Add Bundle to Cart');
				if (r.success) {
					$status.html('✓ Added! <a href="' + r.data.cart_url + '">View cart</a>');
					$(document.body).trigger('wc_fragment_refresh');
				} else {
					$status.text('Error: ' + r.data);
				}
			}).fail(function () {
				$btn.prop('disabled', false).text('Add Bundle to Cart');
				$status.text('Failed. Please try again.');
			});
		});

		function formatPrice(n) {
			return '$' + n.toFixed(2);
		}
		function escHtml(str) { return $('<div>').text(str).html(); }
	});
});
