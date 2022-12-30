<?php //phpcs:ignore
/**
 * Class for issuing API request.
 *
 * @package Collector_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Walley_Checkout_API class.
 *
 * Class for handling Walley API requests.
 */
class Walley_Checkout_API {

	/**
	 * Returns a access token.
	 *
	 * @return array|WP_Error
	 */
	public function get_access_token() {
		$request  = new Walley_Checkout_Request_Access_Token( array() );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get Walley order.
	 *
	 * @param string $walley_id The Walley transaction id.
	 * @return array|WP_Error
	 */
	public function get_walley_order( $walley_id ) {
		$args     = array( 'walley_id' => $walley_id );
		$request  = new Walley_Checkout_Request_Get_Order( $args );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Capture Walley order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return array|WP_Error
	 */
	public function capture_walley_order( $order_id ) {
		$args     = array( 'order_id' => $order_id );
		$request  = new Walley_Checkout_Request_Capture_Order( $args );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Part capture Walley order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return array|WP_Error
	 */
	public function part_capture_walley_order( $order_id ) {
		$args     = array( 'order_id' => $order_id );
		$request  = new Walley_Checkout_Request_Part_Capture_Order( $args );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Cancel Walley order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return array|WP_Error
	 */
	public function cancel_walley_order( $order_id ) {
		$args     = array( 'order_id' => $order_id );
		$request  = new Walley_Checkout_Request_Cancel_Order( $args );
		$response = $request->request();
		return $this->check_for_api_error( $response );
	}

	/**
	 * Checks for WP Errors and returns either the response as array.
	 *
	 * @param array $response The response from the request.
	 * @return array|WP_Error
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			if ( ! is_admin() ) {
				walley_print_error_message( $response );
			}
		}
		return $response;
	}
}
