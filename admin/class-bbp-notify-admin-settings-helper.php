<?php
/**
 * Admin settings helper class
 *
 * @package bbp-notify-admins
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Press_Themes\PT_Settings\Page;

/**
 * Class BBP_Notify_Admin_Settings_Helper
 */
class BBP_Notify_Admin_Settings_Helper {

	/**
	 * Admin Menu slug
	 *
	 * @var string
	 */
	private $menu_slug;

	/**
	 * Used to keep a reference of the Page, It will be used in rendering the view.
	 *
	 * @var \Press_Themes\PT_Settings\Page
	 */
	private $page;

	/**
	 * Boot settings
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup settings
	 */
	public function setup() {

		$this->menu_slug = 'bbp_notify_admins_settings';

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * Show/render the setting page
	 */
	public function render() {
		$this->page->render();
	}

	/**
	 * Is it the setting page?
	 *
	 * @return bool
	 */
	private function needs_loading() {

		global $pagenow;

		// We need to load on options.php otherwise settings won't be reistered.
		if ( 'options.php' === $pagenow ) {
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->menu_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Initialize the admin settings panel and fields
	 */
	public function init() {

		if ( ! $this->needs_loading() ) {
			return;
		}

		$page = new Page( 'bbp_notify_admins_settings', __( 'BBP Notify Admins Settings', 'bbp-notify-admins' ) );

		// General settings tab.
		$general = $page->add_panel( 'general', _x( 'General', 'Admin settings panel title', 'bbp-notify-admins' ) );

		$section_general = $general->add_section( 'settings', _x( 'Notifying emails', 'Admin settings section title', 'bbp-notify-admins' ) );

		$section_general->add_field(
			array(
				'name'  => 'bbp_notify_admins_emails',
				'label' => _x( 'Email list', 'Admin settings', 'bbp-notify-admins' ),
				'type'  => 'rawtext',
				'desc'  => __( 'use comma(,) to separate emails', 'bbp-notify-admins' ),
			)
		);

		do_action( 'bbp_notify_admins_settings_page', $page );

		$this->page = $page;

		// allow enabling options.
		$page->init();
	}

	/**
	 * Add Menu
	 */
	public function add_menu() {

		add_options_page(
			_x( 'BBP Notify Admins', 'Admin settings page title', 'bbp-notify-admins' ),
			_x( 'BBP Notify Admins', 'Admin settings menu label', 'bbp-notify-admins' ),
			'manage_options',
			$this->menu_slug,
			array( $this, 'render' )
		);
	}
}