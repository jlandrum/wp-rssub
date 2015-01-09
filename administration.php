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
	
	$post_types = split(",",get_option( RSSub\OPTION_POST_TYPES, 'post' ));
	
	?>
	<div class="wrap rssub-host">
		<h1>Really Simple Subscriptions</h1>
		<div class="rssub-right">
			<div class="rssub-actionbox">
			 <h1>Allowed Post Types</h1><div class="table">
			 <?php
					foreach (get_post_types(array(),'names') as $type) {
						$post_type = get_post_type_object($type);
						$name = $post_type->labels->singular_name;
						$checked = in_array($type,$post_types)?"checked":"";
						echo "<div class=\"cell\"><input type=\"checkbox\" class=\"_post_types\" name=\"types\" value=\"$type\" $checked> $name</div>";
					}
			 ?></div>
			</div>
			<div class="rssub-actionbox">
			 <h1>Settings</h1>
			 <api-graph id="saveall-graph">
				 <api-call data-targets="#title,#message" data-action="update_template"></api-call>
				 <api-call data-targets="#a_title,#activation_message" data-action="update_activation_template"></api-call>
				 <api-call data-targets="._post_types" data-action="update_post_types"></api-call>
				 <api-call data-targets="._delete,._active" data-action="update_subscriber"></api-call>
			 </api-graph>
			 <button class="button button-large button-primary rssub-api-graph" data-api-graph="saveall-graph" href="#">Save All Changes</button>	
			</div>
			<div class="rssub-actionbox">
				<h1>Active Subscriptions</h1>
				<div class="subtable">					
					<div class="row">
						<div class="col"><strong>E-mail</strong></div>
						<div class="col"><strong>Activated</strong></div>
						<div class="col"><strong>Delete</strong></div>
					</div><div class="rssub-clearfix"></div>
					<?php
						foreach (RSSub\get_subscribers(false) as $suber) {
							$checked = ($suber->active)?"checked":"";
							echo "<div class=\"row\">
											<div class=\"col\">{$suber->email}</div>
											<div class=\"col\"><input type=\"checkbox\" name=\"active\" data-inverse=\"deactive\" class=\"_active\" value=\"{$suber->id}\" $checked></div>
											<div class=\"col\"><input type=\"checkbox\" name=\"delete\" class=\"_delete\" value=\"{$suber->id}\"></div>
										</div><div class=\"rssub-clearfix\"></div>";
						};
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="rssub-left">
		 <div class="rssub-actionbox">
			 <h1>Debug Console</h1>
			 <label for="email">Send Test E-Mail:</label><input id="email" name="email" data-key="to"/>
			 <api-graph id="test-graph"><api-call data-targets="#email" data-action="email"></api-call></api-graph>
			 <button class="button rssub-api-graph" href="#" data-output-action="write_to_div" data-api-graph="test-graph">Send Mail</button><br/>	
			 <label for="email">Add Subscriber:</label><input id="suber" name="suber" data-key="email"/>
			 <button class="button rssub-api-graph" href="#" data-output-action="write_to_div" data-api-graph="add-sub-graph">Add Subscriber</button> 
			 <input type="hidden" value="true" id="active" name="active" data-key="active"/><br/>
			 <api-graph id="add-sub-graph"><api-call data-targets="#suber,#active" data-action="add_subscriber"></api-call></api-graph>
			 <div class="console" id="ste_console">Console:</div>
		 </div>
		 <div class="rssub-actionbox">
			 <h1>New Post Template</h1>
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
			<div class="rssub-actionbox">
			 <h1>Activation Template</h1>
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