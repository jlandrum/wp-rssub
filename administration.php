<?php
add_action( 'admin_menu', 'rssub_menu' );
add_action( 'admin_enqueue_scripts', 'rssub_scripts_loader' );

function rssub_menu() {
	add_options_page( 'Really Simple Subscriptions', 'Subscriptions', 'manage_options', 'rssub', 'rssub_options' );
}

function rssub_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	?>
	<div class="wrap">
	 <h1>Really Simple Subscriptions</h1>
	 <div class="rssub-actionbox">
	 	 <h1>Debug Console</h1>
	   <label for="email">Send Test E-Mail:</label><input id="email" name="email" data-key="to"/>
	 	 <button class="button rssub-api-trigger" href="#" data-output="write_to_div" data-output-params="#ste_console" data-targets="email" data-action="email">Send Mail</button>	
	   <div class="console" id="ste_console">Console:</div>
	 </div>
	 <div class="rssub-actionbox">
	 	 <h1>New Post Template</h1>
	   <input class="header" id="title" name="title" placeholder="Subject" data-key="subject" value="<?php echo stripslashes(get_option(RSSub\OPTION_SUBJECT,'')); ?>"/><br/>
	   <?php wp_editor(stripslashes(get_option(RSSub\OPTION_CONTENT,'')),'message'); ?><br/>
	 	 <button class="button rssub-api-trigger" href="#" data-output="show_dialog" data-targets="title,message" data-action="update_template">Save Changes</button>	
	 </div>
	</div>
	<?php
}

function rssub_scripts_loader() {
	wp_enqueue_script('jquery');
	wp_enqueue_script(
		'rssub-js',
		plugins_url( '/js/core.js' , __FILE__ ),
		array( 'jquery' )
	);
	wp_enqueue_style(
		'rssub-css',
		plugins_url( '/css/style.css' , __FILE__ )
	);
}
?>