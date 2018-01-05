<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Checkout_Requests_Update_Cart extends Collector_Checkout_Requests {
	public $path = '';

	public function __construct( $private_id, $customer_type ) {
		parent::__construct();
		$collector_settings = get_option( 'woocommerce_collector_checkout_settings' );
		switch ( get_woocommerce_currency() ) {
			case 'SEK' :
				$store_id = $collector_settings['collector_merchant_id_se_' . $customer_type];
				break;
			case 'NOK' :
				$store_id = $collector_settings['collector_merchant_id_no_' . $customer_type];
				break;
			default :
				$store_id = $collector_settings['collector_merchant_id_se_' . $customer_type];
				break;
		}
		$this->path = '/merchants/' . $store_id . '/checkouts/' . $private_id . '/cart';
	}

	private function get_request_args() {
		$request_args = array(
			'headers' => $this->request_header( $this->request_body(), $this->path ),
			'timeout' => 10,
			'body'    => $this->request_body(),
			'method'  => 'PUT',
		);
		$this->log( 'Collector update cart request args: ' . var_export( $request_args, true ) );
		return $request_args;
	}

	public function request() {
		$request_url = $this->base_url . $this->path;
		$request = wp_remote_request( $request_url, $this->get_request_args() );
		return $request;
	}

	protected function request_body() {
		$formatted_request_body = $this->cart();
		$this->log( 'Collector update cart request body: ' . var_export( $formatted_request_body, true ) );
		return wp_json_encode( $formatted_request_body );
	}
}
