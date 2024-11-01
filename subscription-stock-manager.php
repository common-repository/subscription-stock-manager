<?php
/**
 * Plugin Name: Subscription Stock Manager
 * Description: <strong>Subscription Stock Manager</strong> makes managing subscription stock simple.
 * Version: 1.0.1
 * Author: Iter8
 * Author URI: https://www.iter8.io/
 * Developer: Iter8
 * Developer URI: https://www.iter8.io/
 * Requires at least: 4.4
 * Tested up to: 5.5.3
 * WC requires at least: 3.0.0
 * WC tested up to: 4.6.1
 * Text Domain: iter8-switch-subscriptions
 * Domain Path: /languages
 * Copyright (c) 2020 Iter8 All rights reserved.
 *
 * @package iter8-switch-subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class Iter8_Subscription_Stock_Manager_Loader {

	/** minimum PHP version required by this plugin */
	const MINIMUM_PHP_VERSION = '5.6.0';

	/** minimum WordPress version required by this plugin */
	const MINIMUM_WP_VERSION = '4.4';

	/** minimum WooCommerce version required by this plugin */
	const MINIMUM_WC_VERSION = '3.0.9';

	/** the plugin name, for displaying notices */
	const PLUGIN_NAME = 'Switch Subscriptions';

	/** @var Iter8_Subscription_Stock_Manager_Loader single instance of this class */
	protected static $instance;

	/** @var array the admin notices to add */
	protected $notices = array();


	/**
	 * Constructs the class.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {

		add_action( 'admin_init', array( $this, 'check_environment' ) );
		add_action( 'admin_init', array( $this, 'add_plugin_notices' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 5 );

		// if the environment check fails, initialize the plugin
		if ( $this->is_environment_compatible() ) {
			add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		}
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot clone instances of %s.', get_class( $this ) ), '1.0.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot unserialize instances of %s.', get_class( $this ) ), '1.0.0' );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {

		if ( ! $this->plugins_compatible() ) {
			return;
		}
		// load the main plugin class
		require_once( plugin_dir_path( __FILE__ ) . 'class-iter8-subscription-stock-manager.php' );

		// fire it up!
		iter8_subscription_stock_manager();
	}


	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
	 *
	 * @since 1.0.0
	 */
	public function activation_check() {

		if ( ! $this->is_environment_compatible() ) {

			$this->deactivate_plugin();

			wp_die( self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message() );
		}
	}

	/**
	 * Checks the environment on loading WordPress, just in case the environment changes after activation.
	 *
	 * @since 1.0.0
	 */
	public function check_environment() {

		if ( ! $this->is_environment_compatible() && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			$this->deactivate_plugin();

			$this->add_admin_notice( 'bad_environment', 'error', self::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message() );
		}
	}


	/**
	 * Adds notices for out-of-date WordPress and/or WooCommerce versions.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_notices() {

		if ( ! $this->is_wp_compatible() ) {

			$this->add_admin_notice(
				'update_wordpress',
				'error',
				sprintf(
					'%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
					'<strong>' . self::PLUGIN_NAME . '</strong>',
					self::MINIMUM_WP_VERSION,
					'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">',
					'</a>'
				)
			);
		}

		if ( ! $this->is_wc_compatible() ) {

			$this->add_admin_notice(
				'update_woocommerce',
				'error',
				sprintf(
					'%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
					'<strong>' . self::PLUGIN_NAME . '</strong>',
					self::MINIMUM_WC_VERSION,
					'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">',
					'</a>',
					'<a href="' . esc_url( 'https://downloads.wordpress.org/plugin/woocommerce.' . self::MINIMUM_WC_VERSION . '.zip' ) . '">',
					'</a>'
				)
			);
		}
	}


	/**
	 * Determines if the required plugins are compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function plugins_compatible() {

		return $this->is_wp_compatible() && $this->is_wc_compatible();
	}


	/**
	 * Determines if the WordPress compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_wp_compatible() {

		if ( ! self::MINIMUM_WP_VERSION ) {
			return true;
		}

		return version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' );
	}


	/**
	 * Determines if the WooCommerce compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_wc_compatible() {

		if ( ! self::MINIMUM_WC_VERSION ) {
			return true;
		}

		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MINIMUM_WC_VERSION, '>=' );
	}


	/**
	 * Deactivates the plugin.
	 *
	 * @since 1.0.0
	 */
	protected function deactivate_plugin() {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}


	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug the notice message slug
	 * @param string $class the notice message class
	 * @param string $message the notice message body
	 */
	public function add_admin_notice( $slug, $class, $message ) {

		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}


	/**
	 * Displays any admin notices added with \Iter8_Switch_Subscriptions_Admin::add_admin_notice()
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {

		foreach ( (array) $this->notices as $notice_key => $notice ) :

			?>
			<div class="<?php echo esc_attr( $notice['class'] ); ?>">
				<p><?php echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) ); ?></p>
			</div>
			<?php

		endforeach;
	}

	public function plugin_action_links( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( __FILE__ );
		}
		if ( $plugin == $plugin_file ) {

			$settings  = array( 'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=iter8_subscription_stock_manager' ) . '">' . __( 'Settings', 'iter8_subscription_stock_manager' ) . '</a>' );
			$site_link = array( 'support' => '<a href="http://iter8.io" target="_blank">Support</a>' );

			$actions = array_merge( $site_link, $actions );
			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}


	/**
	 * Determines if the server environment is compatible with this plugin.
	 *
	 * Override this method to add checks for more than just the PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_environment_compatible() {

		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}


	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_environment_message() {

		return sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', self::MINIMUM_PHP_VERSION, PHP_VERSION );
	}


	/**
	 * Gets the main \Iter8_Subscription_Stock_Manager_Loader instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return \Iter8_Subscription_Stock_Manager_Loader
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


}

// fire it up!
Iter8_Subscription_Stock_Manager_Loader::instance();


// function interlawk_activate() {

// 	if ( ! is_blog_installed() ) {
// 		return;
// 	}

// 	require_once( plugin_dir_path( __FILE__ ) . 'inc/interlawk-utilities.php' );

// 	/** Register our site settings */
// 	add_option( 'interlawk_auth_key', interlawk_generate_key() );
// 	add_option( 'interlawk_capture_form_prompt', __( 'Text us now', 'interlawk' ) );
// 	add_option( 'interlawk_capture_form_header', __( 'We\'ll text you a code you can use at checkout.', 'interlawk' ) );
// 	add_option( 'interlawk_capture_form_locations', array() );
// 	add_option( 'interlawk_coupon_template', Interlawk_Constants::DEFAULT_COUPON );
// }

// register_activation_hook( __FILE__, 'interlawk_activate' );
