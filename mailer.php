<?php 
namespace RSSub;
  include_once('db.php');

  function rw_trim_excerpt( $text='' )
  {
      $text = strip_shortcodes( $text );
      $text = apply_filters('the_content', $text);
      $text = str_replace(']]>', ']]&gt;', $text);
      $excerpt_length = apply_filters('excerpt_length', 55);
      $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
      return wp_trim_words( $text, $excerpt_length, $excerpt_more );
  }


	class Mail {
		protected $message = "";
		protected $subject = "";
		
		function __construct($message, $subject) {
			$this->message = $message;
			$this->subject = $subject;
		}

		/** Sends the given message. **/
		function send($uid) {
      $user = get_subscriber_by_id($uid);
			return wp_mail(
				$user->email, 
				$this->parse_all($this->subject, $user), 
				$this->parse_all($this->message, $user),
				array('Content-Type: text/html' . "\r\n"));
		}
		
    /** Sends the given message using a post. **/
		function send_pending($uid, $posts = null) {
      $user = get_subscriber_by_id($uid);
			return \wp_mail(
				$user->email, 
				$this->parse_pending($this->subject, $posts, $user), 
				$this->parse_pending($this->message, $posts, $user),
				array('Content-Type: text/html' . "\r\n"));
		}
    
    /** Sends the given message using a post. **/
		function send_post($uid, $post = null) {
      $user = get_subscriber_by_id($uid);
			return wp_mail(
				$user->email, 
				$this->parse_message($this->subject, $post, $user), 
				$this->parse_message($this->message, $post, $user),
				array('Content-Type: text/html' . "\r\n"));
		}
    
    function send_activation($uid) {
      $user = get_subscriber_by_id($uid);
      return wp_mail(
				$user->email, 
				$this->parse_activation($this->subject, $user), 
				$this->parse_activation($this->message, $user),
				array('Content-Type: text/html' . "\r\n"));
    }
    
    function create_link($hash, $action) {
			return get_site_url() . "/?subscription_service&token=$hash&action=$action";
		}
    
    function parse_all($message, $user) {
      return str_replace(
				array(
          '{UNSUBSCRIBE_LINK}',
          '{MANAGE_LINK}',
          '{SITE_NAME}',
          '{SITE_URL}',
		    ),array(
          $this->create_link($user->hash, "unsubscribe"),
          $this->create_link($user->hash, "manage"),
          get_bloginfo('name'),
          get_site_url(),
				), $message
			);
    }
    
        
    function parse_message($message, $post, $user) {
      $post_type = get_post_type_object($post->post_type);
      $author = get_userdata($post->post_author);

      $post_type_label = $post_type->labels->singular_name;
      $post_title = $post->post_title;
      $user_name = $author->display_name;
      $content = rw_trim_excerpt($post->post_content);
      
      return str_replace(
				array(
          '{POST_TYPE}',
          '{AUTHOR_NAME}',
          '{POST_URL}',
          '{POST_TITLE}',
          '{POST_EXERPT}',
        ),array(
          $post_type_label, 
          $user_name,
          get_permalink(),
          $post_title,
          $content,
        ), $this->parse_all($message, $user)
			);
    }
    
        
    function parse_activation($message, $user) {
      return str_replace(
				array(
							'{ACTIVATE_LINK}',
						 ),
				array(
							$this->create_link($user->hash, "activate"),
						 ),
				$this->parse_all($message,$user)
			);
    }
    
    function parse_pending($message, $posts, $user) {
      return str_replace(
				array(
							'{POST_COUNT}',
              '{POST_LIST}'
						 ),
				array(
              count($posts),
							$this->create_list($posts),
						 ),
				$this->parse_all($message,$user)
			);
    }
    
    function create_list($posts) {
      $html = "<ul>";
      foreach ($posts as $post) {
        $post_title = $post->post_title;
        $content = rw_trim_excerpt($post->post_content);
        $html .= "<li><b>$post_title</b><br/>$content</li>";
      }
      return $html . "</ul>";
    }
	}

	
/**
* {POST_TYPE} - Post's type
*/