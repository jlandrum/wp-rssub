<?php 
namespace {
	global $rssub_db_version;
	$rssub_db_version = '2.0';
}

namespace RSSub {
	function create_subscriber($email, $active) {
		global $wpdb;
		$users = get_user_table();
		$hash = md5(uniqid(rand(), true));
		
		if (!$wpdb->insert( 
			$users, 
			array( 
				'hash' => $hash, 
				'email' => $email,
				'active' => false
			) 
		)) {
			return $wpdb->last_error;
		};
		
		if ($active == true) {
			return activate($hash);
		} else {
			$query = $wpdb->prepare("
				SELECT hash 
				FROM $users
				WHERE email = %s", $email);
			
			$mail = new Mail(stripslashes(get_option(OPTION_ACTV_CONTENT,'')), stripslashes(get_option(OPTION_ACTV_SUBJECT,'')));
			$mail->send_activation($email, $wpdb->get_var($query));
			
			return true;
		}
	}
	
	function get_subscribers($active, $today = false) {
		global $wpdb;

		$users = get_user_table();

		return $wpdb->get_results( "
			SELECT * 
			FROM $users
			WHERE " . ($active?"active = true AND ":"") . ($today?"added >= CURDATE()":"TRUE = TRUE") 
		);
	}
	
	function activate($hash) {
		global $wpdb;

		$users = get_user_table();

		return ($wpdb->update( 
			$users, 
			array( 
				'added' => current_time( 'mysql' ), 
				'active' => true, 
			),
			array(
				'hash' => $hash
			)
		))?true:$wpdb->last_error;
	}
	
	function change_subscriber_activation_state($id,$active) {
		global $wpdb;

		$users = get_user_table();

		$wpdb->update( 
			$users, 
			array( 
				'time' => current_time( 'mysql' ), 
				'active' => $active, 
			),
			array(
				'id' => $id
			)
		);
	}

	function delete_subscriber($id) {
		global $wpdb;

		$users = get_user_table();
		$subs  = get_subscribe_table();

		$wpdb->delete( 
			$users, 
			array(
				'id' => $id
			)
		);
		
		$wpdb->delete( 
			$subs, 
			array(
				'uid' => $id
			)
		);
	}
	
	function get_subscribers_for_params($userid, $posttype) {
		global $wpdb;
		 
		$users = get_user_table();
		$subs  = get_subscribe_table();
		
		return $wpdb->get_results("SELECT DISTINCT $users.email FROM $subs 
			JOIN $users ON ($users.id = uid)
			WHERE
			(account_id = $userid OR account_id IS NULL) AND
			(post_type = '$posttype' OR post_type IS NULL);");											 
	}
	
	function get_subscriptions_for_user($hash) {
		global $wpdb;
		 
		$users = get_user_table();
		$subs  = get_subscribe_table();
		$wp_users = $wpdb->prefix . "users";

		return $wpdb->get_results("SELECT $subs.*, $wp_users.display_name FROM $subs
			JOIN $users ON ($users.id = uid)
			LEFT JOIN $wp_users ON ($wp_users.id = $subs.account_id)
			WHERE $users.hash = \"$hash\";");											 
	}
	
	function get_subscription_count_for_user($hash) {
		global $wpdb;
		 
		$users = get_user_table();
		$subs  = get_subscribe_table();

		return $wpdb->get_var("SELECT COUNT(*) FROM $subs
			JOIN $users ON ($users.id = uid)
			WHERE $users.hash = \"$hash\";");											 
	}
	
	function add_subscriber_with_params($email, $posttype, $user) {
		global $wpdb;

		$users = get_user_table();
		$subs  = get_subscribe_table();

		if (!$wpdb->query($wpdb->prepare("
			INSERT INTO $subs
			(uid, account_id, post_type)
			SELECT
					(SELECT id FROM $users WHERE email = %s)," .
					($user==null?"(NULL),":"(%d),") .
					($posttype==null?"(NULL)":"(%s)") .
					";"
			,$email,$user==null?$posttype:$user,$posttype))) {
			return $wpdb->last_query;
		};		
		return true;
	}

	const OPTION_ACTV_SUBJECT = "rssub_actv_subject";
	const OPTION_ACTV_CONTENT = "rssub_actv_content";
	const OPTION_SUBJECT = "rssub_subject";
	const OPTION_CONTENT = "rssub_content";
	const OPTION_POST_TYPES = "rssub_post_types";
	
	const TABLE_USER_DETAILS 			 = "rssub_users";
	const TABLE_USER_SUBSCRIPTIONS = "rssub_subscriptions";
	
	function get_user_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_DETAILS;
	}

	function get_subscribe_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_SUBSCRIPTIONS;
	}
}


namespace RSSub\_Private {
	function setup_plugin() {
		global $wpdb;
		global $rssub_db_version;

		$charset_collate = $wpdb->get_charset_collate();

		$sqls = array(
			"CREATE TABLE " . \RSSub\get_user_table() . " (
				id     mediumint(9) NOT NULL AUTO_INCREMENT,
				added   datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				email  varchar(128) NOT NULL UNIQUE,
				hash   text NOT NULL,
				active boolean NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;",
			"CREATE TABLE " . \RSSub\get_subscribe_table() . " (
				id          mediumint(9) NOT NULL AUTO_INCREMENT,
				uid         mediumint(9) NOT NULL,
				account_id  mediumint(9),
				post_type   text,
				UNIQUE KEY id (id)
			) $charset_collate;"
		);

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		dbDelta( $sqls );

		add_option( 'rssub_db_version', $rssub_db_version );
		add_option( \RSSub\OPTION_SUBJECT, '' );
		add_option( \RSSub\OPTION_CONTENT, '' );
		add_option( \RSSub\OPTION_ACTV_SUBJECT, '' );
		add_option( \RSSub\OPTION_ACTV_CONTENT, '' );
		add_option( \RSSub\OPTION_POST_TYPES, 'post' );
	}
}