<?php
/**
 * Plugin Name: bbPress Notify Admins
 * Plugin URI: https://buddydev.com/plugins/bbp-notify-admin/
 * Description:  Notify admins on new topic/replies on their bbPress forums
 * Version: 1.0.3
 * Author: Brajesh Singh(BuddyDev.com)
 * Author URI: https://buddydev.com/
 * License: GPL
*/

class BBP_Notify_Admin_Helper {

	/**
	 * Singleton instance.
	 *
	 * @var BBP_Notify_Admin_Helper
	 */
	private static $instance;

	/**
	 * Plugin directory url.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	private $path;

	private function __construct() {
		
		$this->setup();
		
		add_action( 'bbp_loaded', array( $this, 'setup' ) );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return BBP_Notify_Admin_Helper
	 */
	public static function get_instance() {
		
		if( ! isset( self:: $instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Setup the path and url of the plugin
	 * 
	 */
	public function setup() {
		
		$this->path = plugin_dir_path( __FILE__ );
		
		$this->url = plugin_dir_url( __FILE__ );
		
		$this->load();
		
		$notifier = BBP_Notify_Admin_Email_Notifier::get_instance();
		
		add_action( 'bbp_new_topic', array( $notifier, 'notify_topic' ), 50, 5 );
		add_action( 'bbp_new_reply', array( $notifier, 'notify_reply' ), 50, 7 );
	}

	/**
	 * Load required files when bbPress is loaded
	 * 
	 */
	private function load() {
		
		$path = $this->get_path();
		
		require_once $path . 'class-email-notifier.php' ;
	}
	
	/**
	 * URL to the bbp-notify-admin plugin directory with trailing slash
	 * 
	 * @return string url
	 */
	public function get_url() {
		return $this->url;
	}
	/**
	 * File system absolute path to the bbp-notify-admin plugin directory with trailing slash
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}
}

/**
 * Singleton instance
 * 
 * @return BBP_Notify_Admin_Helper
 */
function bbp_notify_admin_helper() {
	return BBP_Notify_Admin_Helper::get_instance();
}

// initialize.
bbp_notify_admin_helper();
