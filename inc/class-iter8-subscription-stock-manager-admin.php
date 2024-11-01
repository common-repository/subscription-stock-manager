<?php
/**
 * Iter8 Subscription Stock Manager

 * @author    Iter8
 * @copyright Copyright (c) 2016-2020, Iter8, Inc.
 */


defined( 'ABSPATH' ) or exit;

/**
 * Set up the Iter8 Subscription Stock Manager admin.
 *
 * @since 1.0.0
 */
class Iter8_Subscription_Stock_Manager_Admin {

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ), 10, 1 );
	}


	public function add_settings_page( $settings ) {

		$settings[] = iter8_subscription_stock_manager()->load_class( '/inc/class-iter8-subscription-stock-manager-settings.php', 'Iter8_Subscription_Stock_Manager_Settings' );

		return $settings;
	}

}
