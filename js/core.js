jQuery(function($) {
	$calls = 0;
	
	var api_graphs = {};
	$('api-graph').each(function(e,t) {
		$(t).detach();
		api_graphs[$(t).attr('id')] = $(t);
	});
	
	$('body').on('click','.rssub-api-graph', function(event) {
		$(this).prop("disabled", true);
		api_graphs[$(this).data('api-graph')].children().each(function (e,t) {
			handle_data_target(t);
			$calls++;
		});
		event.preventDefault();
	});
	
	function handle_data_target(target) {
		var data = {'rssubapi': '', 'action': $(target).data('action')};
		var targets = $(target).data('targets').split(',');
		for (i in targets) {
			var target = $(targets[i]);
			var key = target.data('key') || target.attr('name');
			data[key] = "";
			target.each(function(i,o) {
				var val = ($(o).attr('class')=='wp-editor-area') ? tinyMCE.get($(o).attr('id')).getContent() : $(o).val();	
				if (!($(o).prop("type")=="checkbox")||$(o).prop("checked")) {
					data[key] += val + ",";
				} else if ($(o).attr("data-inverse")) {
					if (typeof data[$(o).data("inverse")] == "undefined") { data[$(o).data("inverse")] = ""; }
					data[$(o).data("inverse")] += val + ",";			
				}
			});
			data[key] = data[key].replace(/,\s*$/, '');
		}		
		api_call(data);
	}
	
	function api_call(data) {
		$.ajax({
			type: 'POST',
			url: '/',
			data: data,
			success: function(msg){
				$calls--;
				if ($calls == 0) location.reload();
			}
		});
	}

	
	window.write_to_div = function(ex, message) {
		$(ex[0]).html($(ex[0]).html() + "<br/>" + message);
		$(ex[0]).animate({ scrollTop: $(ex[0]).height() }, "slow");
	}
	
	window.show_dialog = function(ex, message) {
		alert(message);
	}
});