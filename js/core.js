jQuery(function($) {
	calls = 0;
	failmsg = "";
	
	var api_graphs = {};
	$('api-graph').each(function(e,t) {
		$(t).detach();
		api_graphs[$(t).attr('id')] = $(t);
	});
	
	$('body').on('click','.rssub-api-graph', function(event) {
		calls = 0;
		failmsg = "";
		$('button').prop("disabled", true);
		console.log(api_graphs[$(this).data('api-graph')]);
		api_graphs[$(this).data('api-graph')].children().each(function (e,t) {
			handle_data_target(t);
			calls++;
		});
		event.preventDefault();
	});
	
	$('body').on('click','.get-subs', function(event) {
		get_subscribers($(this).data('hash'));
	});
	
	$('body').on('click','.sub_list_box', function(e) { $(this).remove(); });

	
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
				calls--;
				if (calls == 0) finished();
			},
			error: function(xhr,b,msg){
				calls--;
				failmsg = xhr.responseText + "\n";
				if (calls == 0) finished();
			}
		});
	}
	
	String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
	}
	
	function get_subscribers(target) {
		$.ajax({
			type: 'POST',
			url: '/',
			data: {'rssubapi': '', 'json': '', 'action': 'request_subscription_list', 'hash': target},
			success: function(msg){
				$('body').append('<div class="sub_list_box"><ul class="sublist"></ul></div>');
				dat = JSON.parse(msg);
				target = $('.sublist');				
				target.append("<li><strong>User is subscribed to:</strong></li>");	
				console.log(dat);
				for (i in dat.data) {
					item = dat.data[i];
					if (item.post_type == null && item.account_id == null) {					
						target.append("<li>Everything.</li>");					
					} else if (item.account_id == null) {
						target.append("<li>All <strong>" + dat.data[i].post_type.capitalize() + "s.</strong></li>");					
					} else if (item.post_type == null) {
						target.append("<li>Submissions from <strong>" + dat.data[i].display_name + "</strong>.</li>");										
					} else {
						target.append("<li><strong>"+dat.data[i].post_type.capitalize()+"</strong>s from <strong>" + dat.data[i].display_name + "</strong>.</li>");					
					}
				}
			},
			error: function(xhr,b,msg){
				alert("Failed to fetch user subscriptions.");
			}
		});
	}
	
	function finished() {
		if (failmsg != "") {
			alert("The following errors have occured:\n" + failmsg);
			$('button').prop("disabled", false);
		} else {
			location.reload();
		}
	}
	
	window.write_to_div = function(ex, message) {
		$(ex[0]).html($(ex[0]).html() + "<br/>" + message);
		$(ex[0]).animate({ scrollTop: $(ex[0]).height() }, "slow");
	}
	
	window.show_dialog = function(ex, message) {
		alert(message);
	}
});