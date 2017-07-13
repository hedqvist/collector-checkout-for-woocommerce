<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Collector_Bank_SOAP_Requests_Activate_Invoice {

	static $log = '';

	public $endpoint = 'https://ecommercetest.collector.se/v3.0/InvoiceServiceV33.svc?wsdl';

	public $username = '';
	public $password = '';
	public $store_id = '';

	public function __construct() {
		$collector_settings = get_option( 'woocommerce_collector_bank_settings' );
		$this->username = $collector_settings['collector_username'];
		$this->password = $collector_settings['collector_password'];
		$this->store_id = $collector_settings['collector_merchant_id'];
	}

	public function request( $order_id ) {
		$soap = new SoapClient( $this->endpoint );
		$args = $this->get_request_args( $order_id );

		$headers = array();
		$headers[] = new SoapHeader( 'http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Username', $this->username );
		$headers[] = new SoapHeader( 'http://schemas.ecommerce.collector.se/v30/InvoiceService', 'Password', $this->password );
		$soap->__setSoapHeaders( $headers );

		$request = $soap->ActivateInvoice( $args );
		$order = wc_get_order( $order_id );
		if ( isset( $request->PaymentReference ) ) {
			$order->add_order_note( sprintf( __( 'Order activated with Collector Bank', 'collector-bank-for-woocommerce' ) ) );
		} else {
			$order->update_status( 'processing' );
			$order->add_order_note( sprintf( __( 'Order failed to activate with Collector Bank', 'collector-bank-for-woocommerce' ) ) );
			$this->log( 'Activate order headers: ' . var_export( $headers, true ) );
			$this->log( 'Activate order args: ' . var_export( $args, true ) );
		}
	}

	public function get_request_args( $order_id ) {
		return array(
			'StoreId'     => $this->store_id,
			'CountryCode' => 'SE',
			'InvoiceNo'   => get_post_meta( $order_id, '_collector_payment_id' )[0],
		);
	}

	public static function log( $message ) {
		$collector_settings = get_option( 'woocommerce_collector_bank_settings' );
		if ( 'yes' === $collector_settings['debug_mode'] ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'collector_bank', $message );
		}
	}
}
