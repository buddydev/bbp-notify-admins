<?php
/**
 * Email notifier class
 *
 * @package bbp-notify-admins
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifies Admins by email
 */
class BBP_Notify_Admin_Email_Notifier {

	/**
	 * Class instance
	 *
	 * @var BBP_Notify_Admin_Email_Notifier
	 */
	private static $instance;

	/**
	 * Get the singleton.
	 *
	 * @return BBP_Notify_Admin_Email_Notifier
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Notify admins on new topic
	 * 
	 * @param int   $topic_id topic id.
	 * @param int   $forum_id forum id.
	 * @param array $anonymous_data anonymous user data.
	 * @param int   $topic_author topic author user id.
	 */
	public function notify_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {

		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = bbp_get_forum_id( $forum_id );

		remove_all_filters( 'bbp_get_topic_content' );
		remove_all_filters( 'bbp_get_topic_title' );

		$topic_title   = strip_tags( bbp_get_topic_title( $topic_id ) );
		$topic_content = strip_tags( bbp_get_topic_content( $topic_id ) );
		$topic_url     = bbp_get_topic_permalink( $topic_id );

		$topic_author_name = bbp_get_topic_author_display_name( $topic_id );

		$message = sprintf( __( '%1$s created new topic:

%2$s

Topic Link: %3$s

-----------

You are receiving this email because you asked for it.

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

		// Get all users emails.
		$emails = $this->get_emails( 'topic' );

		if ( empty( $emails ) ) {
			return;// no one to send to.
		}

		$to_email = array_shift( $emails );

		// Loop through users.
		foreach ( $emails as $email ) {
			// add all other users as bcc(only applies in case we have more than 1 admin ).
			$headers[] = 'Bcc: ' . $email;
		}

		// send email.
		// even if an admin posts, It will notify everyone including them.
		$this->notify(
			array(
				'subject' => $subject,
				'message' => $message,
				'to'      => $to_email,
				'headers' => $headers,
			)
		);
	}

	/**
	 * Notify admins on new reply on the forum
	 * 
	 * A modified/inspired version of  bbp_notify_topic_subscribers()
	 *
	 * @param int   $reply_id reply id.
	 * @param int   $topic_id topic id.
	 * @param int   $forum_id forum id.
	 * @param array $anonymous_data Anonymous data.
	 * @param int   $reply_author reply poster.
	 * @param bool  $unknown_boolean unknown.
	 * @param int   $reply_to post id which we are replying.
	 */
	public function notify_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author, $unknown_boolean, $reply_to ) {

		$reply_id = bbp_get_reply_id( $reply_id );
		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = bbp_get_forum_id( $forum_id );

		// Poster name.
		$reply_author_name = bbp_get_reply_author_display_name( $reply_id );

		remove_all_filters( 'bbp_get_reply_content' );
		remove_all_filters( 'bbp_get_topic_title' );
		
		// Strip tags from text and setup mail data.
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

		// Get all users emails.
		$emails = $this->get_emails( 'reply' );

		if ( empty( $emails ) ) {
			return; // No one to send to.
		}

		$to_email = array_shift( $emails );

		// Loop through users.
		foreach ( $emails as $email ) {
			// Add all other users as bcc(only applies in case we have more than 1 admin ).
			$headers[] = 'Bcc:' . $email;
		}

		$this->notify(
			array(
				'subject' => $subject,
				'message' => $message,
				'to'      => $to_email,
				'headers' => $headers,
			)
		);
	}
	
	/**
	 * Send email
	 *
	 * @param array $args args.
	 */
	private function notify( $args = null ) {

		$defaults = array(
			'subject' => '',
			'message' => '',
			'to'      => '',
			'headers' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		if ( ! $args['to'] || ! $args['subject'] || ! $args['message'] ) {
			return;
		}

		wp_mail( $args['to'], $args['subject'], $args['message'], $args['headers'] );
	}

	/**
	 * Get basic header(from etc)
	 *
	 * @return array
	 */
	private function get_headers() {
		
		// Get the noreply@sitename.com email address.
		$no_reply = bbp_get_do_not_reply_address();

		$from_email = apply_filters( 'bbp_notify_admin_from_email', $no_reply );

		// Setup the From header.
		$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <' . $from_email . '>' );
		
		return $headers;
	}

	/**
	 * Get subject of the message
	 *
	 * @param string $subject subject.
	 *
	 * @return string
	 */
	private function get_subject( $subject ) {
		$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		return '[' . $blog_name . '] ' . $subject;
	}

	/**
	 * Get the email addresses list.
	 *
	 * @param string $context context.
	 *
	 * @return array
	 */
	private function get_emails( $context = null ) {
		$emails = get_option( 'bbp_notify_admins_settings' );
		$emails = isset( $emails['bbp_notify_admins_emails'] ) ? $emails['bbp_notify_admins_emails'] : '';

		$emails = explode( ',', $emails );

		if ( empty( $emails ) ) {
			return array();
		}

		$emails = array_map( 'trim', $emails );
		$emails = apply_filters( 'bbp_notify_admin_email_addresses', $emails, $context );

		$current_user       = wp_get_current_user();
		$current_user_email = '';

		if ( ! empty( $current_user ) ) {
			$current_user_email = $current_user->user_email;
		}

		// when an admin is posting, we exclude him from the list too.
		$emails = array_diff( $emails, (array) $current_user_email );

		return $emails;
	}
}
