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
				data['action'] = "update_subscription";
				data['email'] = $('input[data-field=email]').val();	
				data['user_id'] = $('input[data-field=user_id]').val();
				data['post_type'] = $('input[data-field=post_type]').val();
				data['hash'] = $(document).data('hash');
        data['meta'] = new Object();
        $('[data-field=meta]').each(function() {
          data['meta'][$(this).attr('name')] = $(this).val();
        });
        console.log(data);
				api_call(data, $form.data("callback"));
        break;
		}
	}); 
	
  if (typeof __rssub_token != 'undefined') {
    window.rssubpop = function(data, code) {
      window.rssubpop = null;
      obj = JSON.parse(data);
      $(document).data('hash', obj['data']['hash']);
      $('input[data-field=email]').val(obj['data']['email']);
      for (entry in obj['data']['meta']) {
        src = obj['data']['meta'][entry];
        $("[name='"+src['_key']+"']").val(src['_value']);
      }
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
