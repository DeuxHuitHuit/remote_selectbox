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
						.attr('value', d.value)
						.text(d.text);
					
					if (d.value in oc(getSelected)) {
						o.attr('selected', 'selected');
					}
					
					options.push(o);
				});
				t.append(options);
				t.chosen({allow_single_deselect: true});
				t.trigger('chosen:updated');
				//var clear = t.next('.chosen-container-multi')? '<a class="clearoptions">Clear Selected Options</a>':'';
				//t.before(clear);
			}
		});
	};

	function oc(a){
		var o = {};
		for(var i=0;i<a.length;i++){
			o[a[i]]='';
		}
		return o;
	}

	var init = function () {
		$('#contents div.field-remote_selectbox select').each(initOne);
		/*$('.clearoptions').on('click',function(){
			return false;
			var b = $(this);
			console.log('clicked');
		});*/
	};
	
	$(init);


	
})(jQuery);

