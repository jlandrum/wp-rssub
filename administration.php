<?php
add_action( 'admin_menu', 'rssub_menu' );
add_action( 'admin_enqueue_scripts', 'rssub_scripts_loader' );

function rssub_menu() {
	add_options_page( 'Really Simple Subscriptions', 'Subscriptions', 'manage_options', 'rssub', 'rssub_options' );
}

function create_subscriptions($suber) {
	$subs = RSSub\get_subscription_count_for_user($suber->hash);
	return "<div class=\"col\"><a class=\"button button-small get-subs\" data-hash=\"{$suber->hash}\">Total: ".$subs."</a></div>";
}

function rssub_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$post_types = preg_split("/,/",get_option( RSSub\OPTION_POST_TYPES, 'post' ));
	
	?>
	<div class="wrap rssub-host">
		<h1>Really Simple Subscriptions</h1>
		<div class="rssub-left">
			<div class="rssub-actionbox collapsed">
			 <h1>New Post Template</h1>
       <p>This message will be sent when a new post, page or other compatible item has been added to the site.</p>
			 <input class="header" id="title" name="title" placeholder="Subject" data-key="subject" value="<?php echo stripslashes(get_option(RSSub\OPTION_SUBJECT,'')); ?>"/><br/>
			 <?php wp_editor(stripslashes(get_option(RSSub\OPTION_CONTENT,'')),'message',array(
						'wpautop'       => true,
						'media_buttons' => false,
						'textarea_name' => 'message',
						'editor_selector' => 'message',
						'textarea_rows' => 10,
						'teeny'         => true
				)); ?><br/>
			</div>
			<div class="rssub-actionbox collapsed">
			 <h1>Activation Template</h1>
       <p>This message will be sent when a user subscribes and has not yet confirmed their subscription.</p>
			 <input class="header" id="a_title" name="a_title" placeholder="Subject" data-key="subject" value="<?php echo stripslashes(get_option(RSSub\OPTION_ACTV_SUBJECT,'')); ?>"/><br/>
			 <?php wp_editor(stripslashes(get_option(RSSub\OPTION_ACTV_CONTENT,'')),'activation_message',array(
						'wpautop'       => true,
						'media_buttons' => false,
						'editor_selector' => 'activation_message',
						'textarea_name' => 'activation_message',
						'textarea_rows' => 10,
						'teeny'         => true
				)); ?><br/>
			</div>
      <div class="rssub-actionbox collapsed">
			 <h1>New Subscription Template</h1>
       <p>This message will be sent when a user subscribes but has already activated.</p>
			 <input class="header" id="b_title" name="b_title" placeholder="Subject" data-key="subject" value="<?php echo stripslashes(get_option(RSSub\OPTION_SUBSCRIBE_SUBJECT,'')); ?>"/><br/>
			 <?php wp_editor(stripslashes(get_option(RSSub\OPTION_SUBSCRIBE_CONTENT,'')),'subscribe_message',array(
						'wpautop'       => true,
						'media_buttons' => false,
						'editor_selector' => 'subscribe_message',
						'textarea_name' => 'subscribe_message',
						'textarea_rows' => 10,
						'teeny'         => true
				)); ?><br/>
			</div>
		</div>
		<div class="rssub-right">
			<div class="rssub-actionbox">
			  <h1>Template Codes</h1>
        <p><strong>All Templates</strong></p>        
        <p>
          <em>{UNSUBSCRIBE_LINK}</em> - The URL for the Unsubscribe link.<br/>
          <em>{MANAGE_LINK}</em> - The URL for the Manage Subscriptions link.<br/>
          <em>{SITE_NAME}</em> - The name of the site.<br/>
          <em>{SITE_URL}</em> - The URL of the site.<br/>
        </p>
        <p><strong>New Post Templates</strong></p>
        <p>
          <em>{POST_TYPE}</em> - The type of post.<br/>
          <em>{POST_TITLE}</em> - The title of the post.<br/>
          <em>{POST_EXERPT}</em> - The exerpt of the post.<br/>
          <em>{AUTHOR_NAME}</em> - The author of the post.<br/>
          <em>{POST_URL}</em> - The URL for viewing the post.<br/>
        </p>
        <p><strong>Activation Templates</strong></p>
        <p>
          <em>{ACTIVATE_LINK}</em> - The URL for activating a subscription.<br/>
        </p>
      </div>
      <div class="rssub-actionbox fixed">
        
			  <h1>Settings</h1>
        <p><strong>Successful Verification Page</strong></p>
        <p><?php wp_dropdown_pages(array('name'=>'pv_page', 'selected' => get_option(RSSub\OPTION_PAGE_VERIFICATION,'') )); ?></p>
        <p><strong>Manage Subscription Page</strong></p>
        <p><?php wp_dropdown_pages(array('name'=>'ms_page', 'selected' => get_option(RSSub\OPTION_PAGE_MANAGE,'') )); ?></p>
        <p><strong>Unsubscribe Page</strong></p>
        <p><?php wp_dropdown_pages(array('name'=>'un_page', 'selected' => get_option(RSSub\OPTION_PAGE_UNSUBSCRIBE,'') )); ?></p>
			  <api-graph id="saveall-graph">
				  <api-call data-targets="#title,#message" data-action="update_template"></api-call>
				  <api-call data-targets="#a_title,#activation_message" data-action="update_activation_template"></api-call>
				  <api-call data-targets="#b_title,#subscribe_message" data-action="update_subscription_template"></api-call>
				  <api-call data-targets="._delete,._active" data-action="update_subscriber"></api-call>
				  <api-call data-targets="#pv_page,#ms_page,#un_page" data-action="update_pages"></api-call>
			  </api-graph>
			  <button class="button button-large button-primary rssub-api-graph" data-api-graph="saveall-graph" href="#">Save All Changes</button>	
			</div>
			<div class="rssub-actionbox">
				<h1><?php echo (!isset($_GET['showall'])?"Active Subscriptions  <a href=\"?page=rssub&showall\">(Show All)</a>":"All Subscriptions"); ?></h1>
				<div class="subtable">					
					<div class="row">
						<div class="col"><strong>E-mail</strong></div>
						<div class="col"><strong>Activated</strong></div>
						<div class="col"><strong>Delete</strong></div>
						<div class="col"><strong>Subscriptions</strong></div>
					</div><div class="rssub-clearfix"></div>
					<?php
						foreach (RSSub\get_subscribers(false,!isset($_GET['showall'])) as $suber) {
							$checked = ($suber->active)?"checked":"";
							echo "<div class=\"row\">
											<div class=\"col\">{$suber->email}</div>
											<div class=\"col\"><input type=\"checkbox\" name=\"active\" data-inverse=\"deactive\" class=\"_active\" value=\"{$suber->id}\" $checked></div>
											<div class=\"col\"><input type=\"checkbox\" name=\"delete\" class=\"_delete\" value=\"{$suber->id}\"></div>
											".create_subscriptions($suber)."
										</div><div class=\"rssub-clearfix\"></div>";
						};
					?>
				</div>
			</div>
		</div>
	</div>
 <div class="rssub-clearfix"></div>
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