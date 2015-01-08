<?php

	add_action( 'publish_post', 'rssub_new_post', 10, 2 );

	function rssub_new_post($id, $post) {
		$mail = new RSSub\Mail(stripslashes(get_option(RSSub\OPTION_CONTENT,'')), stripslashes(get_option(RSSub\OPTION_SUBJECT,'')));
		$mail->send("", $post);
		echo "Mail Sent!";
	}
	