<?php

add_action( 'init', 'rssub_api_handler' );
add_action( 'wp_head', 'rssub_token_js' );

function rssub_api_handler() {
	$result_data = ['message' => "Unknown error.", 'status' => 0];
	$output_mode = isset($_REQUEST['json'])?"json":"html";
	$_REQUEST = array_merge($_GET, $_POST);

	if (isset($_REQUEST['subscription_service'])) {
    switch ($_REQUEST['action']) {
      case 'activate':
        RSSub\activate($_REQUEST['token']);
        header("Location: " . get_site_url() . "?p=" . get_option(RSSub\OPTION_PAGE_VERIFICATION,'') . "&token=" . $_REQUEST['token']);
        break;
      case 'manage':
        header("Location: " . get_site_url() . "?p=" . get_option(RSSub\OPTION_PAGE_MANAGE,'') . "&token=" . $_REQUEST['token']);
        break;
      case 'unsubscribe':
        header("Location: " . get_site_url() . "?p=" . get_option(RSSub\OPTION_PAGE_UNSUBSCRIBE,'') . "&token=" . $_REQUEST['token']);
        break;
    }
		die();
	}
	
	if (isset($_REQUEST['rssubapi'])) {
		try {
			if (isset($_REQUEST['action'])) {
				switch($_REQUEST['action']) {
					case "email":
						if (isset($_REQUEST['to'])) {
							$mail = new RSSub\Mail(get_option(RSSub\OPTION_CONTENT,''), get_option(RSSub\OPTION_SUBJECT,''));
							if ($mail->send($_REQUEST['to'], isset($_REQUEST['post']))) {
								$result_data['message'] = "Message successfully sent.";
								$result_data['status'] = 1;								
							} else {								
								throw new Exception("Message failed to send.");							
							};
						} else {
							throw new Exception("No recipient specified.");							
						}
						break;
          case "subinfo":
            $result_data['data'] = RSSub\get_subscriber_info($_REQUEST['hash']);
						$result_data['message'] = "";
						$result_data['status'] = 1;		
            break;
          case "update_pages":
            $pages = $_REQUEST;
            unset($pages['rssubapi']);
            unset($pages['action']);
            $pages = array_values($pages);
            update_option(RSSub\OPTION_PAGE_VERIFICATION, $pages[0]);
            update_option(RSSub\OPTION_PAGE_MANAGE, $pages[1]);
            update_option(RSSub\OPTION_PAGE_UNSUBSCRIBE, $pages[2]);
            break;
					case "update_activation_template":
						update_option(RSSub\OPTION_ACTV_SUBJECT, $_REQUEST['subject']);
						update_option(RSSub\OPTION_ACTV_CONTENT, $_REQUEST['activation_message']);					
						$result_data['message'] = "Configuration successfully saved!";
						$result_data['status'] = 1;								
						break;
          case "update_subscription_template":
						update_option(RSSub\OPTION_SUBSCRIBE_SUBJECT, $_REQUEST['subject']);
						update_option(RSSub\OPTION_SUBSCRIBE_CONTENT, $_REQUEST['subscribe_message']);					
						$result_data['message'] = "Configuration successfully saved!";
						$result_data['status'] = 1;								
						break;
					case "update_template":
						update_option(RSSub\OPTION_SUBJECT, $_REQUEST['subject']);
						update_option(RSSub\OPTION_CONTENT, $_REQUEST['message']);
						$result_data['message'] = "Configuration successfully saved!";
						$result_data['status'] = 1;								
						break;
					case "update_post_types":
						update_option(RSSub\OPTION_POST_TYPES, $_REQUEST['types']);
						$result_data['message'] = "Post types successfully saved!";
						$result_data['status'] = 1;								
						break;
					case "request_subscription_list":
						$result_data['data'] = RSSub\get_subscriptions_for_user($_REQUEST['hash']);
						$result_data['message'] = $_REQUEST['hash'];
						$result_data['status'] = 1;								
						break;
					case "add_subscription":
						if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]+/i', $_REQUEST['email']) == 0) {
							throw new Exception("Invalid E-mail Specified.");
						}
          
            $active = 0;
            if (isset($_REQUEST['active'])) $active = $_REQUEST['active'];
						$id = RSSub\create_subscriber($_REQUEST['email']);

            if (!$id) {
              throw new Exception("There was an issue creating the subscriber.");          
            }
          
            $res = RSSub\add_subscription($id,$_REQUEST['post_type'],$_REQUEST['user_id']);
            if ($res != 1) {
              throw new Exception("There was an issue subscribing to the provided subscription set: " . $res);
            };
					
						$result_data['message'] = "Subscriber successfully added!";
						$result_data['status'] = 1;								
						break;
				  case "update_subscriber":
						foreach (split(",",$_REQUEST['active']) as $activeid) {
							$res = RSSub\change_subscriber_activation_state($activeid,true);
						}
						foreach (split(",",$_REQUEST['deactive']) as $activeid) {
							$res = RSSub\change_subscriber_activation_state($activeid,false);
						}
						foreach (split(",",$_REQUEST['delete']) as $activeid) {
							$res = RSSub\delete_subscriber($activeid);
						}
						$result_data['message'] = "Subscriber successfully updated!";
						$result_data['status'] = 1;								
						break;
          case "update_subscription":
            RSSub\sync_metadata($_REQUEST['meta'],$_REQUEST['hash']);
						$result_data['message'] = "Subscriber successfully updated!";
						$result_data['status'] = 1;								          
            break;
					default:
						throw new Exception("Invalid action specified.");
						break;
				}
			} else { throw new Exception("No action specified."); }
		} catch (Exception $e) {
			http_response_code(400);
			$result_data['message'] = $e->getMessage();
		}

		switch ($output_mode) {
			case "json":
				die(json_encode($result_data));
				break;
			default:
				die($result_data['message']);
				break;
		}
	}
  
  function rssub_token_js() {
    if (isset($_REQUEST['token'])) {
      echo "<script>__rssub_token = '{$_REQUEST['token']}';</script>";
    }
  }
}
								
/*
* action:
*		test_email:
*			to: Recipient of the e-mail
*/