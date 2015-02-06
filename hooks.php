<?php
	add_action( 'wp_loaded', 'add_publish_actions' );

	function add_publish_actions() {
		foreach (get_post_types(array(),'names') as $type) {
			add_action( 'publish_' . $type, 'rssub_new_post', 10, 2 );
		}
	}

	function rssub_new_post($id, $post) {
    if(($_POST['post_status'] == 'publish' ) && ($_POST['original_post_status'] != 'publish')) {
      $mail = new RSSub\Mail(stripslashes(get_option(RSSub\OPTION_CONTENT,'')), stripslashes(get_option(RSSub\OPTION_SUBJECT,'')));
      foreach (RSSub\get_subscribers_for_params($post->post_author,$post->post_type) as $suber) {
        $mail->send_post($suber->id, $post);
      }
    }
	}
	