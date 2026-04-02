jQuery(function ($) {
	function inr(v) { return '₹' + parseFloat(v).toFixed(2); }

	function getInputs() {
		return {
			substrate_cost:    parseFloat($('#phyto-tc-substrate_cost').val())    || 0,
			substrate_litres:  parseFloat($('#phyto-tc-substrate_litres').val())  || 0,
			overhead_monthly:  parseFloat($('#phyto-tc-overhead_monthly').val())  || 0,
			production_months: parseFloat($('#phyto-tc-production_months').val()) || 0,
			labour_hourly:     parseFloat($('#phyto-tc-labour_hourly').val())     || 0,
			labour_hours:      parseFloat($('#phyto-tc-labour_hours').val())      || 0,
			plants_produced:   parseFloat($('#phyto-tc-plants_produced').val())   || 1,
		};
	}

	function calc() {
		var i      = getInputs();
		var margin = parseInt($('#phyto-tc-margin').val()) / 100;

		var substrate  = i.substrate_cost * i.substrate_litres;
		var overhead   = i.overhead_monthly * i.production_months;
		var labour     = i.labour_hourly * i.labour_hours;
		var total      = substrate + overhead + labour;
		var per_plant  = i.plants_produced > 0 ? total / i.plants_produced : 0;

		var price = function(m) { return m < 1 ? per_plant / (1 - m) : per_plant; };

		$('#res-substrate').text(inr(substrate));
		$('#res-overhead').text(inr(overhead));
		$('#res-labour').text(inr(labour));
		$('#res-total').text(inr(total));
		$('#res-per-plant').text(inr(per_plant));
		$('#res-price-40').text(inr(price(0.40)));
		$('#res-price-50').text(inr(price(0.50)));
		$('#res-price-60').text(inr(price(0.60)));
		$('#res-price-target').text(inr(price(margin)));

		return {
			substrate, overhead, labour,
			total, cost_per_plant: per_plant,
			price_40: price(0.40), price_50: price(0.50), price_60: price(0.60),
			price_target: price(margin),
		};
	}

	// Live recalc on input change.
	$(document).on('input', '.phyto-tc-input, #phyto-tc-margin', function () {
		if (this.id === 'phyto-tc-margin') { $('#phyto-tc-margin-val').text($(this).val()); }
		calc();
	});

	calc(); // initial render

	// Save.
	$('#phyto-tc-save').on('click', function () {
		var results = calc();
		$.post(phytoTc.ajax_url, {
			action:   'phyto_tc_save',
			nonce:    phytoTc.nonce,
			id:       $('#phyto-tc-id').val(),
			batch_id: $('#phyto-tc-batch-id').val(),
			label:    $('#phyto-tc-label').val(),
			inputs:   JSON.stringify(getInputs()),
			results:  JSON.stringify(results),
		}, function (res) {
			if (res.success) {
				alert('Estimate saved (ID: ' + res.data.id + '). Reload to see in list.');
				$('#phyto-tc-id').val(res.data.id);
			}
		});
	});

	// Clear.
	$('#phyto-tc-new').on('click', function () {
		$('#phyto-tc-id').val('');
		$('#phyto-tc-batch-id, #phyto-tc-label').val('');
		$('.phyto-tc-input').each(function () { $(this).val($(this).attr('data-default') || 0); });
		calc();
	});

	// Load saved estimate.
	$(document).on('click', '.phyto-tc-load-btn', function () {
		var id = $(this).closest('tr').data('id');
		$.post(phytoTc.ajax_url, { action: 'phyto_tc_load', nonce: phytoTc.nonce, id: id }, function (res) {
			if (!res.success) return;
			var d = res.data;
			$('#phyto-tc-id').val(d.id);
			$('#phyto-tc-batch-id').val(d.batch);
			$('#phyto-tc-label').val(d.label);
			$.each(d.inputs, function (k, v) { $('#phyto-tc-' + k).val(v); });
			calc();
		});
	});

	// Delete.
	$(document).on('click', '.phyto-tc-del-btn', function () {
		if (!confirm('Delete this estimate?')) return;
		var $row = $(this).closest('tr');
		$.post(phytoTc.ajax_url, { action: 'phyto_tc_delete', nonce: phytoTc.nonce, id: $row.data('id') }, function (res) {
			if (res.success) $row.fadeOut(200, function () { $(this).remove(); });
		});
	});
});
