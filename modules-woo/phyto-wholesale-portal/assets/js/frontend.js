jQuery(function ($) {
	$('#phyto-ws-form').on('submit', function (e) {
		e.preventDefault();
		var $form   = $(this);
		var $btn    = $form.find('button[type="submit"]');
		var $status = $('#phyto-ws-status');
		var data    = $form.serializeArray().reduce(function (acc, f) { acc[f.name] = f.value; return acc; }, {});

		data.action = 'phyto_wp_apply';
		data.nonce  = phytoWP.nonce;

		$btn.prop('disabled', true).text('Submitting…');
		$status.text('');

		$.post(phytoWP.ajaxurl, data, function (r) {
			$btn.prop('disabled', false).text('Submit Application');
			if (r.success) {
				$form.slideUp(200);
				$status.text(r.data);
				$('.phyto-ws-apply-form').prepend('<p class="phyto-ws-notice phyto-ws-pending">' + r.data + '</p>');
			} else {
				$status.text('Error: ' + r.data);
			}
		}).fail(function () {
			$btn.prop('disabled', false).text('Submit Application');
			$status.text('Request failed. Please try again.');
		});
	});
});
