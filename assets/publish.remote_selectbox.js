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
				var required = ~~t.attr('data-required');
				
				if (required) {
					t.empty();
				}
				
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
				
				//append input type hidden with text
				var inputText =  $('<input>')
					.attr('type', 'hidden')
					.attr('name', t.attr('data-field-name') + '[text]')
					.addClass('field-remote_selectbox-text')
					.val(t.find('option:selected').text());
				inputText.insertAfter(t);
			}
		});
	};
	
	var onChange = function () {
		var t = $(this);
		var input = t.closest('div.field-remote_selectbox').find('.field-remote_selectbox-text');
		
		input.val(t.find('option:selected').text());
	};
	
	var init = function () {
		var sel = $('#contents div.field-remote_selectbox select');
		sel.each(initOne);
		sel.on('change', onChange);
	};
	
	$(init);
	
})(jQuery);
