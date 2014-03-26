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
				var getSelected = selectedValue.split(',');
				var required = ~~t.attr('data-required');
				
				if (required) {
					t.empty();
				}
				
				$.each(data, function (index, d) {
					var o = $('<option />')
						.attr('value', d.value+'|'+d.text)
						.text(d.text);
					
					if (d.value in oc(getSelected)) {
						o.attr('selected', 'selected');
					}
					
					options.push(o);
				});
				t.append(options);
				if(t.attr('data-order') == 'alphabetical'){
					sortByText()
				}
				t.hasClass('autocomplete')? t.selectize({plugins: ['remove_button','restore_on_backspace']}):'';
				
			}
		});
	};	
function sortByText() {

// Loop for each select element on the page.
$('#sort').each(function() {
// Keep track of the selected option.
var selectedValue = $(this).val();
// Sort all the options by text. I could easily sort these by val.
$(this).html($('option', $(this)).sort(function(a, b) {
return a.text.toUpperCase() == b.text.toUpperCase() ? 0 : a.text.toUpperCase() < b.text.toUpperCase() ? -1 : 1
}));
// Select one option.
$(this).val(selectedValue);
});
}


	function oc(a){
		var o = {};
		for(var i=0;i<a.length;i++){
			o[a[i]]='';
		}
		return o;
	}

	var init = function () {
		$('#contents div.field-remote_selectbox select').each(initOne);
	};
	
	$(init);


	
})(jQuery);

