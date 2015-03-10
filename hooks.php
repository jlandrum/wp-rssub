<?php
	add_action( 'wp_loaded', 'add_publish_actions' );
  add_filter( 'cron_schedules', 'rssub_add_weekly_cron' ); 
  add_action( 'rssub_digest_hook', 'rssub_digest' );
//  define('WP_CRON_LOCK_TIMEOUT', 900);

	function add_publish_actions() {
		foreach (get_post_types(array(),'names') as $type) {
			add_action( 'publish_' . $type, 'rssub_new_post', 10, 2 );
		}
	}

	function rssub_new_post($id, $post) {
    if(($_POST['post_status'] == 'publish' ) && ($_POST['original_post_status'] != 'publish')) {
      $mail = new RSSub\Mail(stripslashes(get_option(RSSub\OPTION_CONTENT,'')), stripslashes(get_option(RSSub\OPTION_SUBJECT,'')));
      foreach (RSSub\get_subscribers_for_params($post->post_author,$post->post_type,0) as $suber) {
        $mail->send_post($suber->id, $post);
      }
      foreach (RSSub\get_subscribers_for_params($post->post_author,$post->post_type,1) as $suber) {
        RSSub\add_pending_post($suber->id, $post->ID);
      }
    }
	}

  function rssub_add_weekly_cron( $schedules ) {
    // add a 'weekly' schedule to the existing set
    $schedules['weekly'] = array(
      'interval' => 604800,
      'display' => __('Once Weekly')
    );
    return $schedules;
  }

  if( !wp_next_scheduled( 'rssub_digest_hook' ) ) {
    wp_schedule_event( time(), 'weekly', 'rssub_digest_hook' );
  }

  function rssub_digest() {
    echo "OK";
    $mail = new RSSub\Mail(stripslashes(get_option(RSSub\OPTION_DIGEST_CONTENT,'')), stripslashes(get_option(RSSub\OPTION_DIGEST_SUBJECT,'')));
    $posts = RSSub\get_pending_posts();
    foreach ($posts as $id => $post ) {
      $mail->send_pending($id, $post);
    }
  } 

