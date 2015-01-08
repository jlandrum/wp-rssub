<?php

add_action( 'init', 'rssub_api_handler' );

function rssub_api_handler() {
	$result_data = ['message' => "Unknown error.", 'status' => 0];
	$output_mode = isset($_REQUEST['json'])?"json":"html";
	$_REQUEST = array_merge($_GET, $_POST);
	
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
					case "update_template":
						update_option(RSSub\OPTION_SUBJECT, $_REQUEST['subject']);
						update_option(RSSub\OPTION_CONTENT, $_REQUEST['message']);
						$result_data['message'] = "Configuration successfully saved! ";
						$result_data['status'] = 1;								
						break;
					default:
						throw new Exception("Invalid action specified.");
						break;
				}
			} else { throw new Exception("No action specified."); }
		} catch (Exception $e) {
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