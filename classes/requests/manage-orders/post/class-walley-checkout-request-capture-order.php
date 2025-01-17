<?php
/**
 * Class for the request to capoture an order.
 *
 * @package Collector_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Walley_Checkout_Request_Capture_Order class.
 */
class Walley_Checkout_Request_Capture_Order extends Walley_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Capture order';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$walley_id = get_post_meta( $this->arguments['order_id'], '_collector_order_id', true );
		return $this->get_api_url_base() . "/manage/orders/{$walley_id}/capture";
	}

	/**
	 * Get the body for the request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order_id = $this->arguments['order_id'];
		$order    = wc_get_order( $order_id );
		$body     = array(
			'amount' => $order->get_total(),
		);

		return apply_filters( 'coc_order_capture_args', $body, $this->arguments['order_id'] );
	}
}
