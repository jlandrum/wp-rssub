jQuery(function($) {
	$('form[action=rssub] button[type=submit]').on('click',function(e) {
		e.preventDefault();
		
		var action = $(this).data('action');
		var data = {'rssubapi': '', 'json': ''};
		var form = $($(this).parent());
		
		switch (action) {
			case 'subscribe':
				data['action'] = "add_subscriber";
				data['email'] = $('input[data-field=email]').val();	
				data['user_id'] = $('input[data-field=user_id]').val();
				data['post_type'] = $('input[data-field=post_type]').val();
				api_call(data, form.data("callback"));
		}
	}); 
	
	function api_call(data,callback) {
		$.ajax({
			type: 'POST',
			url: '/',
			data: data,
			success: function(msg){
				Function("data", callback + "(data,200)")(msg);
			},
			error: function(msg){
				Function("data", callback + "(data,status)")(msg.responseText,msg.status);
			}
		});
	}
});