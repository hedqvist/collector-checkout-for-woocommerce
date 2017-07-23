<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Bank_Requests_Calculate_Auth {
	public $username = '';
	public $shared_key = '';
	public function __construct() {
		$collector_settings = get_option( 'woocommerce_collector_bank_settings' );
		$this->username = $collector_settings['collector_username'];
		$this->shared_key = $collector_settings['collector_shared_key'];
	}

	public function calculate_auth( $body, $path ) {
		error_log( $this->username );
		return 'SharedKey ' . base64_encode( $this->username . ':' . hash( 'sha256', $body . $path . $this->shared_key ) );
	}
}
