<?php
add_action( 'wp_enqueue_scripts', 'rssub_public_scripts_loader' );

function rssub_public_scripts_loader() {
	wp_enqueue_script(
		'rssub-js-pub',
		plugins_url( '/js/public.js' , __FILE__ ),
		array( 'jquery' )
	);
}