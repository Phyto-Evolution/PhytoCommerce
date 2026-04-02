jQuery(function ($) {
	var nonce   = phytoWPAdmin.nonce;
	var ajaxurl = phytoWPAdmin.ajaxurl;

	function handleDecision(action, $btn) {
		var id     = $btn.data('id');
		var userId = $btn.data('user');
		$btn.prop('disabled', true);

		$.post(ajaxurl, {
			action:  'phyto_wp_' + action,
			nonce:   nonce,
			id:      id,
			user_id: userId,
		}, function (r) {
			if (r.success) {
				location.reload();
			} else {
				$btn.prop('disabled', false);
				alert('Action failed.');
			}
		}).fail(function () { $btn.prop('disabled', false); });
	}

	$(document).on('click', '.phyto-ws-approve', function () { handleDecision('approve', $(this)); });
	$(document).on('click', '.phyto-ws-reject',  function () {
		if ( confirm('Reject this application?') ) { handleDecision('reject', $(this)); }
	});
});
