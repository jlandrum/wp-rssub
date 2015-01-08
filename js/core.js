jQuery(function($) {
	$('body').on('click','.rssub-api-trigger',function(e) {
		var targets = $(this).data('targets').split(',');
		var data = {'rssubapi': '', 'action': $(this).data('action')};
		var func = window[$(this).data('output')];
		
		var params = "";
		if (typeof $(this).data('output-params') != 'undefined') {
			params = $(this).data('output-params');
		}
		params = params.split(",");
		
		for (i in targets) {
			var target = $("#"+targets[i]);
			var key = target.data('key') || target.attr("name");
			var val = (target.attr("class")=="wp-editor-area")?tinyMCE.activeEditor.getContent():target.val();			
			data[key] = val;
		}
		
		$.ajax({
			type: 'POST',
			url: '/',
			data: data,
			success: function(msg){
				func(params, msg);
			}
		});
		
		e.preventDefault();
	});
	
	window.write_to_div = function(ex, message) {
		$(ex[0]).html($(ex[0]).html() + "<br/>" + message);
		$(ex[0]).animate({ scrollTop: $(ex[0]).height() }, "slow");
	}
	
	window.show_dialog = function(ex, message) {
		alert(message);
	}
});