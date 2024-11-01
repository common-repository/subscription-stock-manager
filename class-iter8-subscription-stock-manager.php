<?php
/**
 * Interlaw
 *
 * @author    Iter8
 * @copyright Copyright (c) 2020, Iter8, Inc.
 */

defined( 'ABSPATH' ) or exit;

/**
 * Iter8 Switch Subscriptions main plugin class.
 *
 * @since 1.0.0
 */
class Iter8_Subscription_Stock_Manager {

	/** plugin version number */
	const VERSION = '1.0.0';

	/** plugin id */
	const PLUGIN_ID = 'iter8_subscription_stock_manager';

	/** @var Iter8_Subscription_Stock_Manager single instance of this plugin */
	protected static $instance;

	/** @var Iter8_Subscription_Stock_Manager_Admin instance */
	protected $admin;

	/** @var Iter8_Subscription_Stock_Manager_Renewal_Manager */
	protected $renewal_manager;

	/** @var bool $logging_enabled Whether debug logging is enabled */
	private $logging_enabled;

	/** @var string $plugin_path the path of the plugin */
	private $plugin_path;

	/** @var string $plugin_url the url of the plugin */
	private $plugin_url;


	/**
	 * Plugin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->add_hooks();
	}


	/**
	 * Add hooks
	 *
	 * @since 1.0.0
	 */
	public function add_hooks() {

		// initialize the plugin
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 15 );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {

		$this->renewal_manager = $this->load_class( '/inc/class-iter8-subscription-stock-manager-renewal-manager.php', 'Iter8_Subscription_Stock_Manager_Renewal_Manager' );

		// Admin includes
		if ( is_admin() && ! is_ajax() ) {
			$this->admin         = $this->load_class( '/inc/class-iter8-subscription-stock-manager-admin.php', 'Iter8_Subscription_Stock_Manager_Admin' );
		}
	}


	/**
	 * Loads a class at a given path
	 *
	 * @since 1.0.0
	 *
	 * @return class
	 */
	public function load_class( $path, $clazz ) {
		require_once( $this->get_plugin_path() . $path );

		return new $clazz;
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Iter8_Switch_Subscriptions Instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @see wc_avatax()
	 * @return Iter8_Switch_Subscriptions
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin name
	 */
	public function get_plugin_path() {

		if ( null === $this->plugin_path ) {
			$this->plugin_path = untrailingslashit( plugin_dir_path( $this->get_file() ) );
		}

		return $this->plugin_path;
	}


	/**
	 * Gets the plugin's URL without a trailing slash.
	 *
	 * E.g. http://skyverge.com/wp-content/plugins/plugin-directory
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_plugin_url() {

		if ( null === $this->plugin_url ) {
			$this->plugin_url = untrailingslashit( plugins_url( '/', $this->get_file() ) );
		}

		return $this->plugin_url;
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 *
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}

	/**
	 * Determines if debug logging is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool $logging_enabled Whether debug logging is enabled.
	 */
	public function logging_enabled() {

		$this->logging_enabled = ( 'yes' === get_option( 'iter8_subscription_stock_manager_logging_enabled' ) );

		/**
		 * Filter whether debug logging is enabled.
		 *
		 * @since 1.0.0
		 * @param bool $logging_enabled Whether debug logging is enabled.
		 */
		return apply_filters( 'iter8_switch_subscriptions_logging_enabled', $this->logging_enabled );
	}

}


/**
 * Returns the One True Instance of Iter8_Switch_Subscriptions.
 *
 * @since 1.0.0
 *
 * @return Iter8_Switch_Subscriptions
 */
function iter8_subscription_stock_manager() {

	return Iter8_Subscription_Stock_Manager::instance();
}
