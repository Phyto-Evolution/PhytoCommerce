jQuery(function ($) {
	$('#phyto-sb-run-audit').on('click', function () {
		var $btn = $(this), $status = $('#phyto-sb-status');
		$btn.prop('disabled', true);
		$status.text('Running audit…');
		$.post(phytoSb.ajax_url, { action: 'phyto_sb_run_audit', nonce: phytoSb.nonce }, function (res) {
			$btn.prop('disabled', false);
			if (res.success) {
				$status.text('Audited ' + res.data.count + ' products. Reload to see results.');
			} else {
				$status.text('Error: ' + res.data);
			}
		});
	});

	$('#phyto-sb-generate-meta').on('click', function () {
		var $btn = $(this), $status = $('#phyto-sb-status');
		$btn.prop('disabled', true);
		$status.text('Generating meta via AI (may take a moment)…');
		$.post(phytoSb.ajax_url, { action: 'phyto_sb_generate_meta', nonce: phytoSb.nonce }, function (res) {
			$btn.prop('disabled', false);
			if (res.success) {
				$status.text('Updated ' + res.data.updated + ' products.');
			} else {
				$status.text('Error: ' + res.data);
			}
		});
	});
});
