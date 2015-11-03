<?php

/**
 * Notifies Admins by email
 * 
 */
class BBP_Notify_Admin_Email_Notifier {
	
	private static $instance;
	
	private function __construct() {
		
	}

	/**
	 * 
	 * @return BBP_Notify_Admin_Email_Notifier
	 */
	public static function get_instance() {
		
		if( ! isset( self:: $instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
		
	}
	
	
	/**
	 * Notify admins on new topic
	 * 
	 * @param type $topic_id
	 * @param type $forum_id
	 * @param type $anonymous_data
	 * @param type $topic_author
	 * @return boolean
	 */
	public function notify_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		
		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = bbp_get_forum_id( $forum_id );
		
		remove_all_filters( 'bbp_get_topic_content' );
		remove_all_filters( 'bbp_get_topic_title'   );
		
		$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
		$topic_content = strip_tags( bbp_get_topic_content( $topic_id ) );
		$topic_url     = bbp_get_topic_permalink( $topic_id );
		
		$topic_author_name = bbp_get_topic_author_display_name( $topic_id );

		
		
		$message = sprintf( __( '%1$s created new topic:

%2$s

Topic Link: %3$s

-----------

You are receiving this email because you askd for it.

Login and visit the settings to disable these emails.', 'bbp-notify-admin' ),

			$topic_author_name,
			$topic_content,
			$topic_url
		);

		$message = apply_filters( 'bbp_notify_admin_topic_mail_message', $message, $topic_id, $forum_id );

		if ( empty( $message ) ) {
			return;
		}
		
		
		$subject = apply_filters( 'bbp_notify_admin_reply_mail_title', $this->get_subject( __( 'New Topic: ', 'bbp-notify-admin' ) . $topic_title ), $topic_id, $forum_id );
		if ( empty( $subject ) ) {
			return;
		}
		
		$headers = $this->get_headers();
		
		//get the users to send an email
		$users = $this->get_users_to_notify( 'topic' );

		
		$users = apply_filters( 'bbp_notify_admin_topic_notifiable_users', $users );
		
		
		if ( empty( $users ) ) {
			return false;
		}
		//get all users emails
		$emails = $this->get_emails( $users, 'topic' );
		
		if( empty( $emails ) ) {
			return false;//no one to send to
		}
		
		$to_email = array_shift( $emails );
		
		// Loop through users
		foreach ( $emails as $email ) {
			//add all other users as bcc(only applies in case we have more than 1 admin )
			$headers[] = 'Bcc: ' . $email;
		}

		//send email
		//even if an admin posts, It will notify everyone including him
		$this->notify( array(
			'subject'		=> $subject,
			'message'		=> $message,
			'to'			=> $to_email,
			'header'		=> $headers
		) );
	}
	/**
	 * Notify admins on new reply on the forum
	 * 
	 * A modified/inspired version of  bbp_notify_topic_subscribers()
	 * @param type $reply_id
	 * @param type $topic_id
	 * @param type $forum_id
	 * @param type $anonymous_data
	 * @param type $reply_author
	 * @param type $unknown_boolean
	 * @param type $reply_to
	 * @return boolean
	 */
	public function notify_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author, $unknown_boolean , $reply_to ) {
		
		$reply_id = bbp_get_reply_id( $reply_id );
		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = bbp_get_forum_id( $forum_id );
		
		// Poster name
		$reply_author_name = bbp_get_reply_author_display_name( $reply_id );
		
		

		remove_all_filters( 'bbp_get_reply_content' );
		remove_all_filters( 'bbp_get_topic_title'   );
		
		// Strip tags from text and setup mail data
		$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
		$reply_content = strip_tags( bbp_get_reply_content( $reply_id ) );
		$reply_url     = bbp_get_reply_url( $reply_id );
		
		$message = sprintf( __( '%1$s wrote:

%2$s

Post Link: %3$s

-----------

You are receiving this email because you askd for it.

Login and visit the settings to disable these emails.', 'bbp-notify-admin' ),

			$reply_author_name,
			$reply_content,
			$reply_url
		);

		$message = apply_filters( 'bbp_notify_admin_reply_mail_message', $message, $reply_id, $topic_id );

		if ( empty( $message ) ) {
			return;
		}
		
		
		$subject = apply_filters( 'bbp_notify_admin_reply_mail_title', $this->get_subject( __( 'New Reply: ', 'bbp-notify-admin' ) . $topic_title ), $reply_id, $topic_id );
		
		if ( empty( $subject ) ) {
			return;
		}
		
		$headers = $this->get_headers();
		
		//get all admin users
		$users = $this->get_users_to_notify( 'reply' );

		$users = apply_filters( 'bbp_notify_admin_reply_notifiable_users', $users );
	
		if ( empty( $users ) ) {
			return false;
		}
		
		//get all users emails
		$emails = $this->get_emails( $users, 'reply' );
		
		if( empty( $emails ) ) {
			return false;//no one to send to
		}
		
		$to_email = array_shift( $emails );
		
		// Loop through users
		foreach ( $emails as $email ) {
			//add all other users as bcc(only applies in case we have more than 1 admin )
			$headers[] = 'Bcc: ' . $email;
		}

		

		$this->notify( array(
			'subject'		=> $subject,
			'message'		=> $message,
			'to'			=> $to_email,
			'header'		=> $headers
		) );
		
	}
	
	/**
	 * Send email
	 * 
	 * @param type $args
	 * @return type
	 */
	private function notify( $args = null ) {
		
		$defaults = array(
			'subject'	=> '',
			'message'	=> '',
			'to'		=> '',
			'headers'	=> '',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		if( ! $to || ! $subject || !$message  ) {
			return ;
		}
		
		
		wp_mail( $to, $subject, $message, $headers );
		
	}
	/*
	 * Get basic header(from etc)
	 */
	private function get_headers() {
		
		// Get the noreply@sitename.com email address
		$no_reply   = bbp_get_do_not_reply_address();

		
		$from_email = apply_filters( 'bbp_notify_admin_from_email', $no_reply );

		// Setup the From header
		$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>' );
		
		return $headers;
	}
	
	/**
	 * Get subject of the message
	 * 
	 * @param type $subject
	 * @return type
	 */
	private function get_subject( $subject ) {
		$blog_name     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		
		return '[' . $blog_name . '] ' . $subject;
		
	}
	/**
	 * Get the users whom we plan to notify
	 * 
	 * @param type $type
	 * @return type
	 */
	private function get_users_to_notify( $type = 'topic' ) {
		
		//get user by rle
		
		$users = get_users( array(
			'role'	=> 'administrator'
		) );
		
		return $users;
	}
	
	private function get_emails( $users, $context = null ) {
		
		$emails = wp_list_pluck( $users, 'user_email' );
		//get current user's email
		$current_user = wp_get_current_user();
		
		if( empty( $current_user ) ) {
			return false;
		}
		
		$emails = apply_filters( 'bbp_notify_admin_email_addresses', $emails, $context );
				
		//the default to email address is  admin_email 's	
		//$to_email = get_option( 'admin_email' );
		
		$current_user_email = $current_user->user_email;
		
		/*if( $to_email == $current_user_email ) {
			$to_email = '';//we will get back to it in a moment
		}*/
		//when an admin is posting, we exclude him from the list too, 
		
		$emails = array_diff( $emails, (array) $current_user_email );
		
		return $emails;
	}
}
