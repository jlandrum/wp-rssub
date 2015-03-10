jQuery.fn.setval = function(value) {
  if (this.length == 0) return;
  switch (this[0].tagName) {
    case "INPUT":
      switch (jQuery(this[0]).attr('type')) {
        case "radio":
          $all = jQuery('[name='+jQuery(this[0]).attr('name')+']');
          $all.attr('checked',false);
          jQuery('[name='+jQuery(this[0]).attr('name')+'][value='+value+']').attr('checked',true);
          return;
        default:
          return jQuery(this[0]).val(value);    
      }
    default:
      return jQuery(this[0]).val(value);    
  }
};

jQuery.fn.getval = function() {
  if (this.length == 0) return;
  switch (this[0].tagName) {
    case "INPUT":
      switch (jQuery(this[0]).attr('type')) {
        case "radio":
          return jQuery('[name='+jQuery(this[0]).attr('name')+']:checked').val();
        default:
          return jQuery(this[0]).val();    
      }
    default:
      return jQuery(this[0]).val();    
  }
};

jQuery(function($) {
	$('form[action=rssub] button[type=submit]').on('click',function(e) {
		e.preventDefault();
    
		var $form = $(this).closest('form');
		var action = $form.data('method');
    var data = {'rssubapi': '', 'json': ''};
		
		switch (action) {
			case 'subscribe':
				data['action'] = "add_subscription";
				data['email'] = $('input[data-field=email]').getval();	
				data['user_id'] = $('input[data-field=user_id]').getval();
				data['post_type'] = $('input[data-field=post_type]').getval();
				api_call(data, $form.data("callback"));
        break;
      case 'update':
				data['action'] = "update_subscription";
				data['email'] = $('[data-field=email]').getval();	
				data['user_id'] = $('[data-field=user_id]').getval();
				data['post_type'] = $('[data-field=post_type]').getval();
				data['digest'] = $('[data-field=digest]').getval();
				data['hash'] = $(document).data('hash');
        data['meta'] = new Object();
        $('[data-field=meta]').each(function() {
          data['meta'][$(this).attr('name')] = $(this).getval();
        });
        console.log(data);
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
      $(document).data('hash', obj['data']['hash']);
      $('form[action="rssub"] [data-field=email]').setval(obj['data']['email']);
      $('form[action="rssub"] [data-field=digest]').setval(obj['data']['schedule']);
      for (entry in obj['data']['meta']) {
        src = obj['data']['meta'][entry];
        $("form[action=\"rssub\"] [name='"+src['_key']+"']").setval(src['_value']);
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
