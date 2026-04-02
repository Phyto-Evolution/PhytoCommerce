jQuery(function ($) {
	// Save note.
	$(document).on('click', '.phyto-cw-save-note', function () {
		var $row  = $(this).closest('tr');
		var id    = $row.data('id');
		var note  = $row.find('.phyto-cw-note').val();
		var $btn  = $(this);

		$btn.prop('disabled', true).text('Saving…');

		$.post(phytoCw.ajax_url, {
			action:   'phyto_cw_update_note',
			nonce:    phytoCw.nonce,
			item_id:  id,
			note:     note,
		}, function (res) {
			$btn.prop('disabled', false).text('Save');
			if (!res.success) { alert('Error saving note.'); }
		});
	});

	// Remove item.
	$(document).on('click', '.phyto-cw-remove', function () {
		if (!confirm('Remove this plant from your collection?')) { return; }
		var $row = $(this).closest('tr');
		var id   = $row.data('id');

		$.post(phytoCw.ajax_url, {
			action:  'phyto_cw_remove_item',
			nonce:   phytoCw.nonce,
			item_id: id,
		}, function (res) {
			if (res.success) {
				$row.fadeOut(300, function () { $(this).remove(); });
			} else {
				alert('Error removing item.');
			}
		});
	});
});
