<?php
/**
 * Iter8 Subscription Stock Manager

 * @author    Iter8
 * @copyright Copyright (c) 2016-2020, Iter8, Inc.
 */


defined( 'ABSPATH' ) or exit;

/**
 * Set up the Iter8 Subscription Stock Manager renewal manager.
 *
 * @since 1.0.0
 */
class Iter8_Subscription_Stock_Manager_Renewal_Manager {

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$renewal_setting = get_option( 'iter8_subscription_stock_manager_global_renewal_setting', false );

		if ( empty( $renewal_setting ) ) {
			return;
		}

		if ( $renewal_setting === 'line_item_in_stock' ) {
			add_filter( 'wcs_renewal_order_items', array( $this, 'maybe_remove_some_order_items' ), 10, 3 );
		} elseif ( $renewal_setting === 'place_subscription_on_hold' ) {
			add_filter( 'wcs_get_subscription', array( $this, 'maybe_prevent_renewal' ), 10, 1 );
		}
	}

	public function maybe_prevent_renewal( $subscription ) {
		if ( ! doing_action( 'woocommerce_scheduled_subscription_payment' ) ) {
			return $subscription;
		}

		remove_action( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment', 10 );

		$items = apply_filters( 'subscription_stock_manager_renewal_items', $subscription->get_items( array( 'line_item', 'fee', 'shipping', 'tax', 'coupon' ) ) );

		$out_of_stock_product = false;
		foreach ( $items as $item ) {
			$product = $item->get_product();

			if ( ! $product->managing_stock() ) {
				continue;
			}

			if ( ! $product->is_in_stock() ) {
				$out_of_stock_product = $product;
				break;
			}
		}

		if ( false === $out_of_stock_product ) {
			return $subscription;
		}

		$product_name = $out_of_stock_product->get_formatted_name();
		$subscription->update_status( 'on-hold', "Subscription placed on hold due to out of stock product: $product_name" );

		return false;
	}


	public function maybe_remove_some_order_items( $items, $new_order, $subscription ) {
		$new_items = array();
		$removed   = array();
		foreach ( $items as $item ) {
			$product = $item->get_product();

			// If stock management doesn't matter, the item should always be in the renewal
			if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
				$new_items[] = $item;
				continue;
			}

			// If the product isn't in stock, don't add it it as an item
			if ( ! $product->is_in_stock() ) {
				$removed[] = $product->get_formatted_name();
				continue;
			}

			$new_items[] = $item;
		}

		if ( count( $removed ) > 0 ) {
			$removed_text = implode( ', ', $removed );
			$new_order->add_order_note( "The following products did not have sufficient stock and were removed from the order: $removed_text" );
		}

		return $new_items;
	}

}
