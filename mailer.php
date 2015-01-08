<?php namespace RSSub;

	class Mail {
		protected $message = "";
		protected $subject = "";
		
		function __construct($message, $subject) {
			$this->message = $message;
			$this->subject = $subject;
		}

		/** Sends the given message. **/
		function send($to, $post = null) {
			return wp_mail(
				$to, 
				$this->parseMessage($this->subject, $post, $to), 
				$this->parseMessage($this->message, $post, $to),
				array('Content-Type: text/html' . "\r\n"));
		}
		
		function parseMessage($message, $post, $target) {
			if ($post == null) {
				return $message;
			}				
			
			setup_postdata($post); 
			
			$post_type = get_post_type_object( $post->post_type );
			
			return str_replace(
				array('{POST_TYPE}',
							'{AUTHOR_NAME}',
						  '{POST_URL}',
							'{UNSUBSCRIBE_LINK}'
						 ),
				array($post_type->labels->singular_name, 
							get_the_author(),
							get_permalink(),
							"/"
						 ),
				$message
			);
		}
	}

	
/**
* {POST_TYPE} - Post's type
*/