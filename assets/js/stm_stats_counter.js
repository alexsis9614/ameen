"use strict";

(function ($) {
	$(document).ready(function () {
		let counters = [];
		$('.stats_counter').each(function () {
			let $this = $(this),
				id = $this.attr('data-id');

			counters[id] = {
				started: false,
				counter: new countUp(id, 0, $this.attr('data-value'), 0, $this.attr('data-duration'), {
					useEasing: true,
					useGrouping: true,
					separator: '',
					suffix: $this.attr('data-suffix'),
					prefix: $this.attr('data-prefix'),
				})
			};

			$(window).on('load', function () {
				if ($('#' + id).is_on_screen() && !counters[id]['started']) {
					counters[id]['counter'].start();
					counters[id]['started'] = true;
				}
			});
		});
	});
})(jQuery);