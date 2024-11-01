<?php
/**
 * Iter8 Subscription Stock Manager
 *
 * @author    Iter8
 * @copyright Copyright (c) 2020, Iter8, Inc.
 */

defined( 'ABSPATH' ) or exit;

// require_once( plugin_dir_path( __FILE__ ) . 'interlawk-constants.php' );

/**
 * Set up the admin settings.
 *
 * @since 1.0.0
 */
class Iter8_Subscription_Stock_Manager_Settings extends WC_Settings_Page {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id    = 'iter8_subscription_stock_manager';
		$this->label = __( 'Subscription Stock Manager', 'iter8_subscription_stock_manager' );

		parent::__construct();
	}


	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'' => __( 'General', 'iter8_subscription_stock_manager' ),
		);

		return $sections;
	}


	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @param string $current_section Optional. Defaults to empty string.
	 * @return array Array of settings
	 */
	public function get_settings( $current_section = '' ) {

		return apply_filters(
			'iter8_subscription_stock_manager_settings',
			array(
				array(
					'title' => __( 'General Settings', 'iter8_subscription_stock_manager' ),
					'type'  => 'title',
					'desc'  => __( 'This is where you can configure general settings for Subscription Stock Manager.', 'iter8_subscription_stock_manager' ),
					'id'    => 'iter8_subscription_stock_manager_general_settings_section_start',
				),
				'iter8_subscription_stock_manager_global_renewal_setting' => array(
					'id'       => 'iter8_subscription_stock_manager_global_renewal_setting',
					'title'    => __( 'Out of Stock Action', 'iter8_subscription_stock_manager' ),
					'desc'     => __( 'When an item in a subscription is out of stock, take the following action.', 'iter8_subscription_stock_manager' ),
					'default'  => '',
					'default'  => 'base',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array(
						''                           => __( 'Do nothing.', 'iter8_subscription_stock_manager' ),
						'line_item_in_stock'         => __( 'Renew subscription, but only include and charge in-stock items to the renewal order.', 'iter8_subscription_stock_manager' ),
						'place_subscription_on_hold' => __( 'Place subscription on-hold, do not automatically renew.', 'iter8_subscription_stock_manager' ),
					),
					'desc_tip' => __( 'On subscription renewal, configure a setting to manage what should happen if the subscriptions underlying product(s) are out of stock.', 'iter8_subscription_stock_manager' ),
				),
				'iter8_subscription_stock_manager_logging_enabled' => array(
					'title'   => __( 'Enable logging', 'iter8_subscription_stock_manager' ),
					'id'      => 'iter8_subscription_stock_manager_logging_enabled',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'iter8_subscription_stock_manager_general_settings_section_end',
				),
			)
		);
	}

	/**
	 * Output the settings
	 *
	 * @since 1.0
	 */
	public function output() {

		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 *
	 * @since 1.0
	 */
	public function save() {
		global $current_section;

		WC_Admin_Settings::save_fields( $this->get_settings( $current_section ) );
	}
}
