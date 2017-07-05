<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Bank_Requests_Get_Checkout_Information extends Collector_Bank_Requests {

	public $path = '';

	public function __construct( $private_id ) {
		$collector_settings = get_option( 'woocommerce_collector_bank_settings' );
		$store_id = $collector_settings['collector_merchant_id'];
		$this->path = '/merchants/' . $store_id . '/checkouts/' . $private_id;
	}

	private function get_request_args() {
		$request_args = array(
			'headers' => $this->request_header( '', $this->path ),
			'method'  => 'GET',
		);
		$this->log( 'Collector checkout information request args: ' . var_export( $request_args, true ) );
		return $request_args;
	}

	public function request() {
		$request_url = 'https://checkout-api-uat.collector.se' . $this->path;
		$request = wp_remote_request( $request_url, $this->get_request_args() );
		$request = wp_remote_retrieve_body( $request );
		$this->log( 'Collector get checkout information request response: ' . var_export( $request, true ) );
		return $request;
	}
}
