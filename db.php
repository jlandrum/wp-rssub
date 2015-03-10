<?php 
namespace {
	global $rssub_db_version;
	$rssub_db_version = '2.1';
}

namespace RSSub {
	function create_subscriber($email) {
		global $wpdb;
		$users = get_user_table();
		$hash = md5(uniqid(rand(), true));
		
    $id = $wpdb->get_var($wpdb->prepare("
			SELECT id 
			FROM $users
			WHERE email = %s",$email));
    
    $new = !$wpdb->get_var($wpdb->prepare("
			SELECT active 
			FROM $users
			WHERE email = %s",$email));;
    
    if (!$id) {
      if ($wpdb->insert( 
        $users, 
        array( 
          'hash' => $hash, 
          'email' => $email,
          'active' => false
        ))) {
        
        $id = $wpdb->get_var($wpdb->prepare("
          SELECT id 
          FROM $users
          WHERE email = %s",$email));
      } else {
        return $wpdb->last_error;
      }
    } 
    
    if ($new) {
      $mail = new Mail(stripslashes(get_option(OPTION_ACTV_CONTENT,'')), stripslashes(get_option(OPTION_ACTV_SUBJECT,'')));
      $mailed = $mail->send_activation($id);
    } else {
      $mail = new Mail(stripslashes(get_option(OPTION_SUBSCRIBE_CONTENT,'')), stripslashes(get_option(OPTION_SUBSCRIBE_SUBJECT,'')));
      $mailed = $mail->send($id);    
    }

    return $id;
	}
	
  function add_pending_post($id, $pid) {
    global $wpdb;
    
		$staged = get_staged_table();
    
    $wpdb->query($wpdb->prepare("
			INSERT INTO $staged
			(uid, postid) VALUES (%s,%s)",
			$id,$pid));
  }
  
  function get_pending_posts() {
		global $wpdb;

    $entries = array();
    
		$users = get_user_table();
		$staged = get_staged_table();

		$items = $wpdb->get_results("
			SELECT $users.id,
        $staged.postid 
      FROM $users 
      JOIN $staged 
      ON ($staged.uid=$users.id)");
        
    foreach ($items as $item) {
      if (!isset($entries[$item->id])) {
        $entries[$item->id] = array();
      }
      $entries[$item->id][] = get_post($item->postid);
    }
    
    $wpdb->query("DELETE FROM $staged");

    return $entries;
	}
  
  function get_subscriber_by_id($id) {
		global $wpdb;

		$users = get_user_table();

		$res = $wpdb->get_results($wpdb->prepare("
			SELECT * 
			FROM $users
			WHERE id = %s", $id));
    return $res[0];
	}
  
	function get_subscribers($active, $today = false) {
		global $wpdb;

		$users = get_user_table();

		return ($wpdb->get_results( "
			SELECT * 
			FROM $users
			WHERE " . ($active?"active = true AND ":"") . ($today?"added >= CURDATE()":"TRUE = TRUE") 
		));
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
	
	function get_subscribers_for_params($userid, $posttype, $schedule = 0) {
		global $wpdb;
		 
		$users = get_user_table();
		$subs  = get_subscribe_table();
		
		return $wpdb->get_results("SELECT DISTINCT $users.id FROM $subs 
			JOIN $users ON ($users.id = uid)
			WHERE
      ($users.schedule = $schedule) AND
			(account_id = $userid OR account_id IS NULL) AND
			(post_type = '$posttype' OR post_type IS NULL);");											 
	}
	
  function get_subscriber_info($hash) {
    global $wpdb;
		 
		$users = get_user_table();
    $subs  = get_subscribe_table();
    $meta  = get_metadata_table();
		$wp_users = $wpdb->prefix . "users";

    $values = array();
		$temp = $wpdb->get_results($wpdb->prepare("SELECT email, schedule FROM $users
			WHERE hash = %s;",$hash));	
    $values['schedule'] = $temp[0]->schedule;    
    $values['email'] = $temp[0]->email;
    $values['subscriptions'] = $wpdb->get_results("SELECT $subs.id, $subs.post_type, $wp_users.display_name FROM $subs
			JOIN $users ON ($users.id = uid)
			LEFT JOIN $wp_users ON ($wp_users.id = $subs.account_id)
			WHERE $users.hash = \"$hash\";");
    $values['meta'] = $wpdb->get_results("SELECT $meta._key, $meta._value FROM $meta
			JOIN $users ON ($users.id = $meta.uid)
			WHERE $users.hash = \"$hash\";", OBJECT_K );
    $values['hash'] = $hash;
    return $values;
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
	
	function add_subscription($id, $posttype, $user) {
		global $wpdb;

		$users = get_user_table();
		$subs  = get_subscribe_table();

		if (!$wpdb->query($wpdb->prepare("
			INSERT INTO $subs
			(uid, account_id, post_type)
			SELECT
					(SELECT id FROM $users WHERE id = %s)," .
					($user==null?"(NULL),":"(%d),") .
					($posttype==null?"(NULL)":"(%s)") .
					";"
			,$id,$user==null?$posttype:$user,$posttype))) {
			return $wpdb->last_query;
		};		
		return true;
	}
  
  function sync_metadata($meta, $hash, $email, $digest) {
		global $wpdb;
    
    $users = get_user_table();
    $metatable  = get_metadata_table();
    
    $users = get_user_table();

    if (!empty($email)) {
      $wpdb->query(
        $wpdb->prepare("
          UPDATE $users
          SET email=%s 
          WHERE hash = %s
        ", $email, $hash));
    }

    if (!empty($digest)) {
      $wpdb->query(
        $wpdb->prepare("
          UPDATE $users
          SET schedule=%d 
          WHERE hash = %s
        ", $digest, $hash));
    }

    
    $wpdb->query(
      $wpdb->prepare("
        DELETE wp_rssub_metadata.*
        FROM  $metatable
        JOIN  $users ON ( $metatable.uid = $users.id ) 
        WHERE $users.hash =  %s", $hash));
    
    
    foreach ($meta as $key => $item) {
      $wpdb->query($wpdb->prepare("
      INSERT INTO `wp_rssub_metadata`
        (uid, _key, _value)
        SELECT
          (SELECT id FROM wp_rssub_users WHERE hash = %s),
          (%s),
          (%s)", $hash, $key, $item));
    }
  }

	const OPTION_ACTV_SUBJECT = "rssub_actv_subject";
	const OPTION_ACTV_CONTENT = "rssub_actv_content";
	const OPTION_SUBJECT = "rssub_subject";
	const OPTION_CONTENT = "rssub_content";
  const OPTION_SUBSCRIBE_SUBJECT = "rssub_sub_subject";
	const OPTION_SUBSCRIBE_CONTENT = "rssub_sub_content";
  const OPTION_DIGEST_SUBJECT = "rssub_dig_subject";
	const OPTION_DIGEST_CONTENT = "rssub_dig_content";
  const OPTION_POST_TYPES = "rssub_post_types";
	const OPTION_PAGE_VERIFICATION = "rssub_page_verification";
	const OPTION_PAGE_MANAGE = "rssub_page_manage";
	const OPTION_PAGE_UNSUBSCRIBE = "rssub_page_unsubscribe";
  
	const TABLE_USER_DETAILS 			 = "rssub_users";
	const TABLE_USER_SUBSCRIPTIONS = "rssub_subscriptions";
	const TABLE_USER_METADATA      = "rssub_metadata";
	const TABLE_USER_STAGED_POST   = "rssub_staged_posts";
	
	function get_user_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_DETAILS;
	}

	function get_subscribe_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_SUBSCRIPTIONS;
	}	
  
  function get_metadata_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_METADATA;
	}
  
  function get_staged_table() {
		global $wpdb;
		return $wpdb->prefix . TABLE_USER_STAGED_POST;
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
				schedule   tinyint DEFAULT 0 NOT NULL,
				active boolean NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;",
			"CREATE TABLE " . \RSSub\get_subscribe_table() . " (
				id          mediumint(9) NOT NULL AUTO_INCREMENT,
				uid         mediumint(9) NOT NULL,
				account_id  mediumint(9),
				post_type   text,
				UNIQUE KEY id (id)
			) $charset_collate;",
      "CREATE TABLE " . \RSSub\get_metadata_table() . " (
				id          mediumint(9) NOT NULL AUTO_INCREMENT,
				uid         mediumint(9) NOT NULL,
				_key        text,
				_value      text,
				UNIQUE KEY id (id)
			) $charset_collate;",
      "CREATE TABLE " . \RSSub\get_staged_table() . " (
				id          mediumint(9) NOT NULL AUTO_INCREMENT,
				uid         mediumint(9) NOT NULL,
				postid      mediumint(9),
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
		add_option( \RSSub\OPTION_SUBSCRIBE_SUBJECT, '' );
		add_option( \RSSub\OPTION_SUBSCRIBE_CONTENT, '' );
		add_option( \RSSub\OPTION_POST_TYPES, 'post' );
		add_option( \RSSub\OPTION_PAGE_VERIFICATION, '-1' );
		add_option( \RSSub\OPTION_PAGE_MANAGE, '-1' );
		add_option( \RSSub\OPTION_PAGE_UNSUBSCRIBE, '-1' );
	}
}