<?php

add_action( 'init', 'rssub_api_handler' );

function rssub_api_handler() {
	$result_data = ['message' => "Unknown error.", 'status' => 0];
	$output_mode = isset($_REQUEST['json'])?"json":"html";
	$_REQUEST = array_merge($_GET, $_POST);

	if (isset($_REQUEST['subscription_service'])) {
		RSSub\activate($_REQUEST['token']);
		header("Location: " . $_REQUEST['redirect']) ;
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
					case "update_activation_template":
						update_option(RSSub\OPTION_ACTV_SUBJECT, $_REQUEST['subject']);
						update_option(RSSub\OPTION_ACTV_CONTENT, $_REQUEST['activation_message']);					
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
					case "add_subscriber":
						if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]+/i', $_REQUEST['email']) == 0) {
							throw new Exception("Invalid E-mail Specified.");
						}
						if (!RSSub\create_subscriber($_REQUEST['email'],$_REQUEST['active']==true)) {
							throw new Exception("An error occured while trying to add the provided e-mail: " . $_REQUEST['email']);
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
						$result_data['message'] = "Subscriber successfully updated!" . print_r($_REQUEST);
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
}
								
/*
* action:
*		test_email:
*			to: Recipient of the e-mail
*/