<?php
	add_action( 'wp_loaded', 'add_publish_actions' );

	function add_publish_actions() {
		foreach (get_post_types(array(),'names') as $type) {
			add_action( 'publish_' . $type, 'rssub_new_post', 10, 2 );
		}
	}

	function rssub_new_post($id, $post) {
		$mail = new RSSub\Mail(stripslashes(get_option(RSSub\OPTION_CONTENT,'')), stripslashes(get_option(RSSub\OPTION_SUBJECT,'')));
		$mail->send("thyme.cypher@gmail.com", $post);
		echo "Mail Sent!";
	}
	