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
				api_call(data, form.data("callback"));
		}
	}); 
	
	function api_call(data,callback) {
		$.ajax({
			type: 'POST',
			url: '/',
			data: data,
			success: function(msg){
				Function("data", callback + "(data)")(msg);
			}
		});
	}
});