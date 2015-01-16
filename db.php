<?php 
namespace {
	global $rssub_db_version;
	$rssub_db_version = '1.1';
}

namespace RSSub {
	function create_subscriber($email, $active) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rssub';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'hash' => md5(uniqid(rand(), true)), 
				'email' => $email, 
				'active' => $active, 
			) 
		);
		
		if (!$active) {
			$query = $wpdb->prepare("
				SELECT hash 
				FROM $table_name
				WHERE email = %s", $email);
			
			$mail = new Mail(stripslashes(get_option(OPTION_ACTV_CONTENT,'')), stripslashes(get_option(OPTION_ACTV_SUBJECT,'')));
			$mail->send_activation($email, $wpdb->get_var($query));
		}
	}
	
	function get_subscribers($active) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rssub';
		
		return $wpdb->get_results( "
			SELECT * 
			FROM $table_name
			" . ($active?"WHERE active = true":"") 
		);
	}
	
	function activate($hash) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rssub';

		$wpdb->update( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'active' => true, 
				'hash' => ''
			),
			array(
				'hash' => $hash
			)
		);
	}
	
	function change_subscriber_activation_state($id,$active) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rssub';

		$wpdb->update( 
			$table_name, 
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

		$table_name = $wpdb->prefix . 'rssub';

		$wpdb->delete( 
			$table_name, 
			array(
				'id' => $id
			)
		);
	}

	const OPTION_ACTV_SUBJECT = "rssub_actv_subject";
	const OPTION_ACTV_CONTENT = "rssub_actv_content";
	const OPTION_SUBJECT = "rssub_subject";
	const OPTION_CONTENT = "rssub_content";
	const OPTION_POST_TYPES = "rssub_post_types";
	
	const TABLE_USER_DETAILS 			 = "rssub_users";
	const TABLE_USER_SUBSCRIPTIONS = "rssub_subscriptions";
	
	function get_full_table_name($target) {
		global $wpdb;
		return $wpdb->prefix . $target;
	}
}


namespace RSSub\_Private {
	function setup_plugin() {
		global $wpdb;
		global $rssub_db_version;

		$charset_collate = $wpdb->get_charset_collate();

		$sqls = array(
			"CREATE TABLE " . \RSSub\get_full_table_name(TABLE_USER_DETAILS) . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				time   datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				email  tinytext NOT NULL,
				hash   text NOT NULL,
				active boolean NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;",
			"CREATE TABLE " . \RSSub\get_full_table_name(TABLE_USER_SUBSCRIPTIONS) . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				account mediumint(9) NOT NULL,
				key tinytext NOT NULL,
				value text NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;"
		);

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		dbDelta( $sql_sub );
		dbDelta( $sql_ex  );

		add_option( 'rssub_db_version', $rssub_db_version );
		add_option( \RSSub\OPTION_SUBJECT, '' );
		add_option( \RSSub\OPTION_CONTENT, '' );
		add_option( \RSSub\OPTION_ACTV_SUBJECT, '' );
		add_option( \RSSub\OPTION_ACTV_CONTENT, '' );
		add_option( \RSSub\OPTION_POST_TYPES, 'post' );
	}
}