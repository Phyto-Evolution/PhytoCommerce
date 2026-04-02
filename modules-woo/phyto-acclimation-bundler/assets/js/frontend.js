jQuery(function ($) {
	// Dismiss widget for session.
	if (sessionStorage.getItem('phytoAbDismissed')) {
		$('#phyto-ab-widget').hide();
	}

	$('#phyto-ab-dismiss').on('click', function () {
		sessionStorage.setItem('phytoAbDismissed', '1');
		$('#phyto-ab-widget').slideUp(200);
	});

	// Add single item.
	$(document).on('click', '.phyto-ab-add-btn', function () {
		var $btn = $(this);
		var pid  = $btn.data('product-id');
		$btn.prop('disabled', true).text('Adding…');
		$.post('/?wc-ajax=add_to_cart', { product_id: pid, quantity: 1 }, function () {
			$btn.text('✓ Added');
			$(document.body).trigger('wc_fragment_refresh');
		});
	});

	// Add all kit items.
	$(document).on('click', '.phyto-ab-add-all', function () {
		var $btn = $(this);
		var ids  = $btn.data('ids').toString().split(',');
		$btn.prop('disabled', true).text('Adding…');
		var done = 0;
		ids.forEach(function (pid) {
			$.post('/?wc-ajax=add_to_cart', { product_id: parseInt(pid, 10), quantity: 1 }, function () {
				done++;
				if (done === ids.length) {
					$btn.text('✓ All Added');
					$(document.body).trigger('wc_fragment_refresh');
				}
			});
		});
	});
});
