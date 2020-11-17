<?php
/**
 * Delivery module class.
 *
 * @package Collector_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Collector_Delivery_Module class.
 */
class Collector_Delivery_Module {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'clear_shipping_and_recalculate' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_order_meta' ), 10, 1 );
	}

	/**
	 * Clears the shipping calculations to prevent errors.
	 *
	 * @return void
	 */
	public function clear_shipping_and_recalculate() {
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		reset( $available_gateways );

		if ( 'collector_checkout' === WC()->session->get( 'chosen_payment_method' ) ) {
			// If Collector Checkout is the chosen payment method.
			WC()->session->set( 'collector_delivery_module_enabled', true );
			WC()->session->__unset( 'shipping_for_package_0' );
			WC()->cart->calculate_shipping();
		} elseif ( ( null === WC()->session->get( 'chosen_payment_method' ) || '' === WC()->session->get( 'chosen_payment_method' ) ) && 'collector_checkout' === key( $available_gateways ) ) {
			// If no payment method is chosen but Collector Checkout is the first payment method.
			WC()->session->set( 'collector_delivery_module_enabled', true );
			WC()->session->__unset( 'shipping_for_package_0' );
			WC()->cart->calculate_shipping();
		} else {
			if ( null !== WC()->session->get( 'collector_delivery_module_enabled' ) ) {
				WC()->session->__unset( 'shipping_for_package_0' );
				WC()->session->__unset( 'collector_delivery_module_enabled' );
			}
		}
	}

	/**
	 * Display Shipping info in order if order contain a Collector Delivery Module shipping method.
	 *
	 * @param object $order WooCommerce order.
	 * @return mixed Prints the html displayed in order admin.
	 */
	public function admin_order_meta( $order ) {
		$order_id                = $order->get_id();
		$collector_delivery_data = json_decode( get_post_meta( $order_id, '_collector_delivery_module_data', true ), true );

		if ( ! empty( $collector_delivery_data['servicePointName'] ) ) {
			$pickup_service = $collector_delivery_data['carrierName'];
			$pickup_name    = $collector_delivery_data['servicePointName'];

			$shipment_id = $collector_delivery_data['pendingShipment']['id'];

			$pickup_service_text = sprintf( '<strong>%1$s</strong> %2$s<br>', __( 'Service:', 'krokedil-shipping-connector' ), wc_clean( $pickup_service ) );
			$pickup_name_text    = sprintf( '<strong>%1$s</strong> %2$s<br>', __( 'Pickup Point:', 'krokedil-shipping-connector' ), wc_clean( $pickup_name ) );
			$shipment_id_text    = sprintf( '<strong>%1$s</strong> %2$s<br>', __( 'Shipment ID:', 'krokedil-shipping-connector' ), wc_clean( $shipment_id ) );

			printf(
				'<h3>%1$s</h3><div class="unifaun"><p>%2$s%3$s%4$s</p></div>',
				esc_html__( 'Shipment information', 'krokedil-shipping-connector' ),
				wp_kses_post( $pickup_service_text ),
				wp_kses_post( $pickup_name_text ),
				wp_kses_post( $shipment_id_text )
			);
		}

	}

}

Collector_Delivery_Module::get_instance();