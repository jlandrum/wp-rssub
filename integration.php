<?php
add_action( 'wp_enqueue_scripts', 'rssub_public_scripts_loader' );

function rssub_public_scripts_loader() {
	wp_enqueue_script(
		'rssub-js-pub',
		plugins_url( '/js/public.js' , __FILE__ ),
		array( 'jquery' )
	);
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-dialog');
  wp_enqueue_style('plugin_name-admin-ui-css',
                'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/start/jquery-ui.css',
                false,
                1,
                false);
}