/**
 * Publish Remote Select Box
 * 
 * @author: Deux Huit Huit
 * 
 */
(function ($) {
	
	'use strict';
	
	var initOne = function () {
		var t = $(this);
		
		$.get(t.attr('data-url'), function (data) {
			if (!!data && !!data.length) {
				var options = [];
				var selectedValue = t.attr('data-value');
				
				$.each(data, function (index, d) {
					var o = $('<option />')
						.attr('value', d.value)
						.text(d.text);
					
					if (selectedValue == d.value) {
						o.attr('selected', 'selected');
					}
					
					options.push(o);
				});
				t.append(options);
			}
		});
	};
	
	var init = function () {
		$('#contents div.field-remote_selectbox select').each(initOne);
	};
	
	$(init);
	
})(jQuery);
