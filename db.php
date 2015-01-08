<?php 
namespace {
	global $jal_db_version;
	$jal_db_version = '1.0';

	register_activation_hook( __FILE__, 'RSSub\_Private\create_root_table' );
	register_activation_hook( __FILE__, 'RSSub\_Private\create_options' );
}

namespace RSSub {
	function create_subscriber($email, $active) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'rssub';

		$wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'email' => $email, 
				'active' => $active, 
			) 
		);
	}
	
	const OPTION_SUBJECT = "rssub_subject";
	const OPTION_CONTENT = "rssub_content";
}

namespace RSSub\_Private {
	function create_root_table() {
		global $wpdb;
		global $jal_db_version;

		$sub_table_name = $wpdb->prefix . 'rssub';
		$ex_table_name = $wpdb->prefix . 'rssub_ex';

		$charset_collate = $wpdb->get_charset_collate();

		$sql_sub = "CREATE TABLE $sub_table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			email tinytext NOT NULL,
			active boolean NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
		
		$sql_ex = "CREATE TABLE $ex_table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			account mediumint(9) NOT NULL,
			key tinytext NOT NULL,
			value text NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		dbDelta( $sql_sub );
		dbDelta( $sql_ex  );

		add_option( 'rssub_db_version', $jal_db_version );
	}
	
	function create_options() {
		add_option( RSSub\OPTION_SUBJECT, '' );
		add_option( RSSub\OPTION_CONTENT, '' );
	}
}
