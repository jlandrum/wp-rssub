jQuery(function($) {
	$('form[action=rssub] button[type=submit]').on('click',function(e) {
		e.preventDefault();
    
		var $form = $(this).closest('form');
		var action = $form.data('method');
    var data = {'rssubapi': '', 'json': ''};
		
		switch (action) {
			case 'subscribe':
				data['action'] = "add_subscription";
				data['email'] = $('input[data-field=email]').val();	
				data['user_id'] = $('input[data-field=user_id]').val();
				data['post_type'] = $('input[data-field=post_type]').val();
				api_call(data, $form.data("callback"));
        break;
      case 'update':
				data['action'] = "add_subscription";
				data['email'] = $('input[data-field=email]').val();	
				data['user_id'] = $('input[data-field=user_id]').val();
				data['post_type'] = $('input[data-field=post_type]').val();
				api_call(data, $form.data("callback"));
        break;
      default:
        console.log("Error: A method was attempted that is not supported by rssub.");
		}
	}); 
	
  if (typeof __rssub_token != 'undefined') {
    window.rssubpop = function(data, code) {
      window.rssubpop = null;
      obj = JSON.parse(data);
      console.log(obj);
      $('input[data-field=email]').val(obj['data']['email']);
    }
        
    var data = {'rssubapi': '', 'json': '', 'action': 'subinfo', 'hash': __rssub_token};
    api_call(data, 'rssubpop');
  }
  
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
