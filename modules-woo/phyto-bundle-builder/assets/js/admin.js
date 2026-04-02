// Admin JS — minimal, form is server-rendered.
jQuery(function ($) {
	// Slot count change: show/hide slot rows
	$('#bb-slots').on('change', function () {
		var count = parseInt($(this).val(), 10) || 3;
		$('.phyto-bb-slot-row').each(function () {
			var idx = parseInt($(this).data('index'), 10);
			$(this).toggle(idx < count);
		});
	}).trigger('change');
});
